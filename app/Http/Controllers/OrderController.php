<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $filters = $request->only(['status', 'fulfillment_type', 'search']);

        $orders = Order::with(['user:id,name,email', 'pickupPoint:id,name'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['fulfillment_type'] ?? null, fn ($q, $type) => $q->where('fulfillment_type', $type))
            ->when($filters['search'] ?? null, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('full_name', 'like', "%{$term}%")
                        ->orWhere('guest_email', 'like', "%{$term}%")
                        ->orWhere('guest_phone', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));


                    $id = ltrim($term, '#');
                    if (ctype_digit($id)) {
                        $q->orWhere('id', $id);
                    }
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $filters['status'] ?? '',
                'fulfillment_type' => $filters['fulfillment_type'] ?? '',
                'search' => $filters['search'] ?? '',
            ],
            'statuses' => [
                'pending', 'paid', 'processing', 'ready_for_pickup',
                'out_for_delivery', 'received', 'completed', 'cancelled',
            ],
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $user = $request->user();

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order->load([
                'items.variant.product',
                'statusHistory' => fn ($q) => $q->latest('id'),
                'statusHistory.changedBy:id,name',
                'pickupPoint',
                'user:id,name,email',
                'receivedBy:id,name',
                'successfulPayment',
            ]),
            // Policy results, so the UI never re-implements the rules.
            'can' => [
                'updateStatus' => $user->can('updateStatus', $order),
                'markReceived' => $user->can('markReceived', $order),
                'cancel' => $user->can('cancel', $order),
            ],
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $this->authorize('updateStatus', $order);

        $changed = DB::transaction(function () use ($request, $order) {
            $order = $this->lock($order);
            $next = $request->validated('status');

            if (! in_array($next, $order->allowedNextStatuses(), true)) {
                return false;
            }

            $order->update(['status' => $next]);

            $order->statusHistory()->create([
                'status' => $next,
                'changed_by' => $request->user()->id,
            ]);

            return true;
        });

        if ($changed) {
            $this->notifyCustomer($order, $request->validated('status'));
        }

        return $changed
            ? back()->with('success', 'Order status updated.')
            : back()->with('error', 'That status change is no longer valid for this order. Refresh and try again.');
    }

    public function markReceived(Request $request, Order $order)
    {
        $this->authorize('markReceived', $order);

        $changed = DB::transaction(function () use ($request, $order) {
            $order = $this->lock($order);

            if ($order->received_at || ! $order->isReceivable()) {
                return false;
            }

            $order->update([
                'status' => 'received',
                'received_at' => now(),
                'received_by' => $request->user()->id,
            ]);

            $order->statusHistory()->create([
                'status' => 'received',
                'changed_by' => $request->user()->id,
            ]);

            return true;
        });

        return $changed
            ? back()->with('success', 'Order marked as received.')
            : back()->with('error', 'This order can no longer be marked as received.');
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorize('cancel', $order);

        $changed = DB::transaction(function () use ($request, $order) {
            $order = $this->lock($order);

            if (! $order->isCancellable()) {
                return false;
            }

            $order->update(['status' => 'cancelled']);

            $order->statusHistory()->create([
                'status' => 'cancelled',
                'changed_by' => $request->user()->id,
            ]);

            return true;
        });

        if ($changed) {
            $this->notifyCustomer($order, 'cancelled');
        }
        return $changed
            ? back()->with('success', 'Order cancelled.')
            : back()->with('error', 'This order can no longer be cancelled.');
    }

    /**
     * Re-read the order with a row lock so two staff members acting on the
     * same order at once can't both pass the state check.
     */
    private function lock(Order $order): Order
    {
        return Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
    }

    private function notifyCustomer(Order $order, string $status): void
    {
        $email = $order->user?->email ?? $order->guest_email;

        if ($email && in_array($status, OrderStatusUpdated::NOTIFY_ON, true)) {
            Mail::to($email)->queue(new OrderStatusUpdated($order, $status));
        }
    }
}
