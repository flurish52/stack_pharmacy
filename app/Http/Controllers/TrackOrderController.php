<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrackOrderController extends Controller
{
    /**
     * Public lookup by payment reference — no order id, no sequential
     * guessing surface. GET so the reference can live in the URL/be
     * bookmarked, but throttled in routes/web.php since it's unauthenticated.
     */
    public function index(Request $request)
    {
        $reference = trim((string) $request->query('reference', ''));

        if ($reference === '') {
            return Inertia::render('Track/Index');
        }

        $payment = Payment::where('reference', $reference)->first();

        if (! $payment) {
            // Same generic response whether the reference never existed or
            // just doesn't match anything — never hint at which.
            return Inertia::render('Track/Index', [
                'reference' => $reference,
                'notFound' => true,
            ]);
        }

        $order = $payment->order()
            ->with(['items.variant.product', 'pickupPoint', 'payments' => fn ($q) => $q->latest()])
            ->firstOrFail();

        $user = $request->user();

        // Already theirs — send them to the real thing instead of a
        // stripped-down public view of a page they already have.
        if ($user && $order->user_id === $user->id) {
            return redirect()->route('account.orders.show', $order);
        }

        $claimable = $user
            && is_null($order->user_id)
            && $order->guest_email
            && strcasecmp($order->guest_email, $user->email) === 0;

        return Inertia::render('Track/Index', [
            'reference' => $reference,
            'order' => [
                'reference' => $reference,
                'status' => $order->status,
                'fulfillment_type' => $order->fulfillment_type,
                'pickup_point' => $order->pickupPoint,
                'delivery_address' => $order->fulfillment_type === 'delivery' ? $order->delivery_address : null,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at,
                'items' => $order->items->map(fn ($item) => [
                    'product_name' => $item->variant->product->name,
                    'variant_name' => $item->variant->variant_name,
                    'quantity' => $item->quantity,
                ]),
                'latest_payment_status' => $order->payments->first()?->status,
                'is_claimed' => ! is_null($order->user_id),
                'claimable' => $claimable,
            ],
        ]);
    }

    /**
     * Claiming attaches the order to the logged-in user's account. Gated
     * server-side on the order being unclaimed AND the account's email
     * matching guest_email — never trust the "claimable" flag the client
     * already saw, re-check it here.
     */
    public function claim(Request $request, string $reference)
    {
        $user = $request->user();

        $payment = Payment::where('reference', $reference)->firstOrFail();
        $order = $payment->order;

        abort_unless(
            is_null($order->user_id) && $order->guest_email && strcasecmp($order->guest_email, $user->email) === 0,
            403,
            'This order can\'t be claimed with your account.'
        );

        $order->update(['user_id' => $user->id]);

        return redirect()->route('account.orders.show', $order)->with('success', 'Order added to your account.');
    }
}
