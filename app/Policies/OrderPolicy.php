<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-orders');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can('view-orders') || $order->user_id === $user->id;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->can('update-order-status');
    }

    public function cancel(User $user, Order $order): bool
    {
        // Staff with cancel-order: allowed until the order leaves the pharmacy.
        if ($user->can('cancel-order')) {
            return $order->isCancellable();
        }

        // Customers: own order only, and only before processing starts.
        return $order->user_id === $user->id && $order->isCustomerCancellable();
    }

    public function markReceived(User $user, Order $order): bool
    {
        if (! $order->isReceivable()) {
            return false;
        }

        // Staff via update-order-status, OR the customer confirming their own order.
        return $user->can('update-order-status') || $order->user_id === $user->id;
    }

    public function viewActivityLog(User $user): bool
    {
        return $user->can('view-activity-log');
    }
}
