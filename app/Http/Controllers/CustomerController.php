<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Orders — list
     */

    public function dashboard(Request $request)
    {
        $user = $request->user();
        return Inertia::render('Account/Dashboard', [
            'user' => $user->only('id', 'name', 'email'),
            'recentOrder' => $user->orders()->latest()->first(),
            'defaultAddress' => $user->addresses()->where('is_default', true)->first(),
            'ordersCount' => $user->orders()->count(),
        ]);
    }

    public function ordersIndex(Request $request)
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->withCount('items')
            ->with('payments')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Account/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Orders — single order detail
     */
    public function ordersShow(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['items.variant.product', 'pickupPoint', 'payments']);

        return Inertia::render('Account/Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Addresses — list
     */
    public function addressesIndex(Request $request)
    {
        $addresses = Address::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return Inertia::render('Account/Addresses/Index', [
            'addresses' => $addresses,
        ]);
    }

    /**
     * Addresses — create
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:20'],
            'is_default' => ['boolean'],
        ]);

        $user = $request->user();

        if (! empty($data['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        // If this is the user's first address, make it default regardless.
        if ($user->addresses()->count() === 0) {
            $data['is_default'] = true;
        }

        $user->addresses()->create($data);

        return back()->with('success', 'Address added.');
    }

    /**
     * Addresses — update
     */
    public function update(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:20'],
            'is_default' => ['boolean'],
        ]);

        if (! empty($data['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('success', 'Address updated.');
    }

    /**
     * Addresses — delete
     */
    public function destroy(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        // Promote another address to default if the deleted one was it.
        if ($wasDefault) {
            $next = $request->user()->addresses()->latest()->first();
            $next?->update(['is_default' => true]);
        }

        return back()->with('success', 'Address removed.');
    }

    public function cancelOrder(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->isCancellable(), 422, 'This order can no longer be cancelled.');

        $order->update(['status' => 'cancelled']);

        $order->statusHistory()->create([
            'status' => 'cancelled',
            'changed_by' => $request->user()->id,
        ]);

        // If there's a successful payment on this order, this is the point
        // you'd kick off a refund — left out here since it depends on your
        // Paystack refund setup, but flagging it so it isn't forgotten.

        return back()->with('success', 'Order cancelled.');
    }


    public function markReceived(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->isReceivable(), 422, 'This order isn\'t out for delivery or ready for pickup yet.');

        $order->update([
            'status' => 'completed',
            'received_at' => now(),
            'received_by' => $request->user()->id, // self-reported by the customer
        ]);

        $order->statusHistory()->create([
            'status' => 'completed',
            'changed_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Marked as received — thanks!');
    }
}
