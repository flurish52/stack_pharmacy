<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::with(['user', 'pickupPoint'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Orders/Index', ['orders' => $orders]);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order->load(['items.variant.product', 'statusHistory.changedBy', 'pickupPoint']),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $this->authorize('updateStatus', $order);

        DB::transaction(function () use ($request, $order) {
            $order->update(['status' => $request->validated('status')]);

            $order->statusHistory()->create([
                'status' => $request->validated('status'),
                'changed_by' => $request->user()->id,
            ]);
        });

        return back()->with('success', 'Order status updated.');
    }

    public function markReceived(Order $order)
    {
        $this->authorize('markReceived', $order);

        if ($order->received_at) {
            return back()->with('error', 'Order already marked as received.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'received',
                'received_at' => now(),
                'received_by' => request()->user()->id,
            ]);

            $order->statusHistory()->create([
                'status' => 'received',
                'changed_by' => request()->user()->id,
            ]);
        });

        return back()->with('success', 'Order marked as received.');
    }

    public function cancel(Order $order)
    {
        $this->authorize('cancel', $order);

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            $order->statusHistory()->create([
                'status' => 'cancelled',
                'changed_by' => request()->user()->id,
            ]);
        });

        return back()->with('success', 'Order cancelled.');
    }
}
