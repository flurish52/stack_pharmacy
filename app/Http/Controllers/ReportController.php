<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

// Access is enforced by the can:view-reports route middleware.
class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $period] = $this->range($request);

        // The period right before this one, same length, for "vs previous" figures.
        $length = max(1, (int) round($from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay())) + 1);
        $previousTo = $from->copy()->subSecond();
        $previousFrom = $from->copy()->subDays($length)->startOfDay();

        $current = $this->totals($from, $to);
        $previous = $this->totals($previousFrom, $previousTo);

        return Inertia::render('Admin/Reports/Index', [
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => [
                'revenue' => $current['revenue'],
                'orders' => $current['orders'],
                'average' => $current['orders'] > 0 ? round($current['revenue'] / $current['orders'], 2) : 0,
                'cancelled' => Order::where('status', 'cancelled')->whereBetween('created_at', [$from, $to])->count(),
                'revenue_change' => $this->change($current['revenue'], $previous['revenue']),
                'orders_change' => $this->change($current['orders'], $previous['orders']),
            ],
            'series' => $this->dailySeries($from, $to),
            'topProducts' => $this->topProducts($from, $to),
            'fulfillment' => Order::counted()->whereBetween('created_at', [$from, $to])
                ->selectRaw('fulfillment_type, COUNT(*) as orders, COALESCE(SUM(total_amount), 0) as revenue')
                ->groupBy('fulfillment_type')->get(),
            'statusBreakdown' => Order::whereBetween('created_at', [$from, $to])
                ->selectRaw('status, COUNT(*) as orders')
                ->groupBy('status')->orderByDesc('orders')->get(),
        ]);
    }

    /** @return array{0: Carbon, 1: Carbon, 2: string} */
    private function range(Request $request): array
    {
        $now = now();
        $period = $request->input('period', '30d');

        $range = match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            '7d' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
            'last_month' => [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            'custom' => $this->customRange($request),
            default => null,
        };

        if (! $range) {
            return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay(), '30d'];
        }

        return [$range[0], $range[1], $period];
    }

    private function customRange(Request $request): ?array
    {
        try {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $to = Carbon::parse($request->input('to'))->endOfDay();
        } catch (\Throwable) {
            return null;
        }

        if ($from->gt($to) || $from->diffInDays($to) > 366) {
            return null; // fall back to the default period
        }

        return [$from, $to];
    }

    /** Orders that were actually paid for and not cancelled. */
    private function totals(Carbon $from, Carbon $to): array
    {
        $row = Order::counted()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total_amount), 0) as revenue')
            ->first();

        return ['orders' => (int) $row->orders, 'revenue' => (float) $row->revenue];
    }

    private function change(float|int $current, float|int $previous): ?float
    {
        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : null;
    }

    /** One row per day in the range, zero-filled so the chart has no gaps. */
    private function dailySeries(Carbon $from, Carbon $to): array
    {
        $byDay = Order::counted()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders, COALESCE(SUM(total_amount), 0) as revenue')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $series = [];

        for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
            $key = $day->toDateString();

            $series[] = [
                'date' => $key,
                'orders' => (int) ($byDay[$key]->orders ?? 0),
                'revenue' => (float) ($byDay[$key]->revenue ?? 0),
            ];
        }

        return $series;
    }

    /** Best sellers. Raw joins on purpose: removed variants and products still count. */
    private function topProducts(Carbon $from, Carbon $to)
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as v', 'v.id', '=', 'oi.product_variant_id')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->whereIn('o.status', Order::COUNTED_STATUSES)
            ->whereBetween('o.created_at', [$from, $to])
            ->groupBy('p.id', 'p.name')
            ->selectRaw('p.id, p.name, SUM(oi.quantity) as units, SUM(oi.quantity * oi.price) as revenue')
            ->orderByDesc('units')
            ->limit(10)
            ->get();
    }
}
