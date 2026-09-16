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
        if (! $order->isCancellable()) {
            return false;
        }

        return $user->can('cancel-order') || $order->user_id === $user->id;
    }

    public function markReceived(User $user, Order $order): bool
    {
        // Staff/Admin/Owner via update-order-status, OR the customer self-reporting per §3.5
        return $user->can('update-order-status') || $order->user_id === $user->id;
    }

    public function viewActivityLog(User $user): bool
    {
        return $user->can('view-activity-log');
    }
}
