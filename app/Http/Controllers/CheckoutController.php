<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PickupPoint;
use App\Models\ProductVariant;
use App\Services\PaystackService;
use App\Support\CartSession;
use Illuminate\Validation\Rule;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartSession $cart,
        protected PaystackService $paystack,
    ) {}

    public function index(): Response|RedirectResponse
    {
        if (empty($this->cart->all())) {
            return redirect()->route('cart.index');
        }

        $user = auth()->user();

        return Inertia::render('Checkout/Index', [
            'total' => $this->cart->summary()['total'],
            'user' => $user ? [
                'name' => $user->name,
                'email' => $user->email,
            ] : null,
            'addresses' => $user
                ? $user->addresses()->orderByDesc('is_default')->get(['id', 'label', 'address', 'phone', 'is_default'])
                : [],
            'pickupPoints' => PickupPoint::query()
                ->where('is_active', true)
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $cart = $this->cart->all(); // [variant_id => quantity]
        abort_if(empty($cart), 422, 'Cart is empty.');

        $user = $request->user();

        $validated = $request->validate([
            'fulfillment_type' => ['required', Rule::in(['delivery', 'pickup'])],
            'delivery_address' => ['required_if:fulfillment_type,delivery', 'nullable', 'string', 'max:500'],
            'pickup_point_id' => ['required_if:fulfillment_type,pickup', 'nullable', 'exists:pickup_points,id'],
            'full_name' => [$user ? 'nullable' : 'required', 'string', 'max:150'],
            'phone' => [$user ? 'nullable' : 'required', 'string', 'max:30'],
            'email' => [$user ? 'nullable' : 'required', 'email'],
        ]);

        $fullName = $user->name ?? $validated['full_name'];
        $email = $user->email ?? $validated['email'];

        // Phone: from the request if given; otherwise (saved-address delivery,
        // where we hid the field) fall back to that address's own phone.
        $phone = $validated['phone']
            ?? $user?->addresses()->where('address', $validated['delivery_address'] ?? null)->value('phone');

        abort_if(!$phone, 422, 'A phone number is required.');

        // Re-fetch variants fresh rather than trust anything the client sent
        // — prices and stock may have moved since the cart page was loaded.
        $variants = ProductVariant::whereIn('id', array_keys($cart))->get()->keyBy('id');

        foreach ($cart as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (! $variant) {
                return back()->with('error', 'One of the items in your cart is no longer available.');
            }

            if ($quantity > $variant->stock_quantity) {
                return back()->with('error', "Only {$variant->stock_quantity} left of one of your items — please update your cart.");
            }
        }

        [$order, $payment] = DB::transaction(function () use ($validated, $cart, $variants, $user, $fullName, $email, $phone) {
            $total = collect($cart)->sum(fn ($quantity, $variantId) => $variants[$variantId]->price * $quantity);

            $order = Order::create([
                'user_id' => $user?->id,
                'guest_email' => $user ? null : $email,
                'guest_phone' => $phone,
                'full_name' => $fullName,
                'fulfillment_type' => $validated['fulfillment_type'],
                'pickup_point_id' => $validated['pickup_point_id'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'status' => 'pending',
                'total_amount' => $total,
            ]);

            foreach ($cart as $variantId => $quantity) {
                $order->items()->create([
                    'product_variant_id' => $variantId,
                    'quantity' => $quantity,
                    'price' => $variants[$variantId]->price,
                ]);
            }

            $order->statusHistory()->create([
                'status' => 'pending',
                'changed_by' => $user?->id,
            ]);

            // One payment attempt per checkout submission. If they retry after a
            // failure, store() runs again and a second Payment row is created for
            // the same order — that's the point of a separate table.
            $payment = $order->payments()->create([
                'gateway' => 'paystack',
                'reference' => 'STACK_ORD-' . strtoupper(Str::random(12)),
                'status' => 'pending',
                'amount' => $total,
            ]);

            return [$order, $payment];
        });

        $response = $this->paystack->initializeTransaction(
            email: $email,
            amountInKobo: (int) ($payment->amount * 100),
            reference: $payment->reference,
            callbackUrl: route('checkout.callback'),
        );

        if (! ($response['status'] ?? false)) {
            $payment->update(['status' => 'failed']);
            $order->update(['status' => 'payment_failed']);

            return back()->with('error', 'Could not start payment. Please try again.');
        }

        $this->cart->clear();

        return Inertia::location($response['data']['authorization_url']);
    }

    public function callback(Request $request)
    {
        $payment = Payment::where('reference', $request->query('reference'))->firstOrFail();
        $order = $payment->order;

        if ($payment->status === 'pending') {
            $verification = $this->paystack->verifyTransaction($payment->reference);

            if (($verification['status'] ?? false) && $verification['data']['status'] === 'success') {
                $this->paystack->markSuccessful($payment, $verification['data']);
                $payment->refresh();
                $order->refresh();
            }
        }

        return Inertia::render('Checkout/Callback', [
            'order' => $order->only(['id', 'status']),
            'payment' => $payment->only(['reference', 'status', 'amount', 'channel']),
        ]);
    }

    /**
     * Resume payment on a pending order — this reads only from the order
     * and creates a fresh Payment row for the attempt. It never touches
     * the cart, so an order can be paid for even if the session cart is
     * empty (expired, cleared, different device, etc).
     */
    public function pay(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        // Nothing to pay for if it's already settled or dead.
        abort_unless($order->status === 'pending', 404);

        $email = $order->user?->email ?? $order->guest_email;
        abort_if(!$email, 422, 'No email on file for this order.');

        $payment = $order->payments()->create([
            'gateway' => 'paystack',
            'reference' => 'STACK_ORD-' . strtoupper(Str::random(12)),
            'status' => 'pending',
            'amount' => $order->total_amount,
        ]);

        $response = $this->paystack->initializeTransaction(
            email: $email,
            amountInKobo: (int) ($payment->amount * 100),
            reference: $payment->reference,
            callbackUrl: route('checkout.callback'),
        );

        if (! ($response['status'] ?? false)) {
            $payment->update(['status' => 'failed']);

            return back()->with('error', 'Could not start payment. Please try again.');
        }

        return Inertia::location($response['data']['authorization_url']);
    }
}
