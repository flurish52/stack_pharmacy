<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(protected PaystackService $paystack) {}

    public function store(Request $request)
    {
        $cart = session('cart', []);

        abort_if(empty($cart), 422, 'Cart is empty.');

        $order = DB::transaction(function () use ($request, $cart) {
            $order = Order::create([
                'user_id' => $request->user()?->id,
                'guest_email' => $request->user() ? null : $request->input('email'),
                'guest_phone' => $request->user() ? null : $request->input('phone'),
                'fulfillment_type' => $request->input('fulfillment_type'),
                'pickup_point_id' => $request->input('pickup_point_id'),
                'delivery_address' => $request->input('delivery_address'),
                'status' => 'pending',
                'paystack_reference' => 'ORD-' . strtoupper(Str::random(12)),
                'total_amount' => collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']),
            ]);

            foreach ($cart as $item) {
                $order->items()->create([
                    'product_variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            $order->statusHistory()->create(['status' => 'pending', 'changed_by' => $request->user()?->id]);

            return $order;
        });

        $response = $this->paystack->initializeTransaction(
            email: $request->user()?->email ?? $request->input('email'),
            amountInKobo: (int) ($order->total_amount * 100),
            reference: $order->paystack_reference,
            callbackUrl: route('checkout.callback'),
        );

        abort_unless($response['status'] ?? false, 500, 'Could not initialize payment.');

        return Inertia::location($response['data']['authorization_url']);
    }

    public function callback(Request $request)
    {
        $order = Order::where('paystack_reference', $request->query('reference'))->firstOrFail();

        return Inertia::render('Checkout/Callback', [
            'order' => $order->only(['id', 'status', 'paystack_reference']),
        ]);
    }
}
