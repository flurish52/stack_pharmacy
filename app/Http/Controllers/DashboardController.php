<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    // Adjust to match the status values you actually use on orders.status
    private const STATUSES = [
        'paid',
        'processing',
        'out_for_delivery',
        'ready_for_pickup',
        'completed',
        'received',
        'cancelled',
    ];

    // Statuses that should NOT count towards revenue
    private const NON_REVENUE = ['pending', 'cancelled'];

    public function index(Request $request): Response
    {
        $user = $request->user();

        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = collect(self::STATUSES)
            ->mapWithKeys(fn ($status) => [$status => (int) ($counts[$status] ?? 0)]);

        $recentOrders = Order::query()
            ->with('user:id,name')
            ->latest()
            ->limit(8)
            ->get(['id', 'user_id', 'guest_email', 'fulfillment_type', 'status', 'total_amount', 'created_at'])
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'customer' => $order->user?->name ?? $order->guest_email ?? 'Guest',
                'fulfillment_type' => $order->fulfillment_type,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->toIso8601String(),
            ]);

        // Assumes product_variants has a `stock` column
        $lowStock = ProductVariant::query()
            ->where('stock_quantity', '<=', config('pharmacy.low_stock_threshold', 5))
            ->count();

        // Revenue is only computed (and sent to the browser) for people who may see it
        $revenue = $user->can('view-reports')
            ? (float) Order::query()
                ->whereNotIn('status', self::NON_REVENUE)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_amount')
            : null;

        return Inertia::render('Admin/Dashboard', [
            'statusCounts' => $statusCounts,
            'recentOrders' => $recentOrders,
            'lowStock' => $lowStock,
            'revenue' => $revenue,
        ]);
    }
}
