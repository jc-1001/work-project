<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now         = now();
        $last6       = collect(range(5, 0))->map(fn($i) => $now->copy()->subMonths($i));
        $trendLabels = $last6->map(fn($d) => $d->format('n') . '月')->values();
        $trendStart  = $last6->first()->copy()->startOfMonth();

        $userStats = User::whereHas('roles', fn($q) => $q->where('name', 'user'))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN YEAR(created_at) = ? AND MONTH(created_at) = ? THEN 1 ELSE 0 END) as monthly_growth
            ', [$now->year, $now->month])
            ->first();

        $userTrendRaw = User::whereHas('roles', fn($q) => $q->where('name', 'user'))
            ->selectRaw('YEAR(created_at) as yr, MONTH(created_at) as mo, COUNT(*) as cnt')
            ->where('created_at', '>=', $trendStart)
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn($r) => "{$r->yr}-{$r->mo}");

        $userTrend = $last6->map(
            fn($d) => (int) ($userTrendRaw->get("{$d->year}-{$d->month}")?->cnt ?? 0)
        )->values();

        $productStats = Product::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
            SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END) as low_stock
        ')->first();

        $top3 = OrderItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_sold')
            ->limit(3)
            ->get();

        $lowStockList = Product::where('stock', '<', 10)
            ->orderBy('stock')
            ->get(['id', 'name', 'stock', 'is_active']);

        $orderStats = Order::selectRaw('
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as shipping,
            SUM(CASE WHEN status = ? AND YEAR(updated_at) = ? AND MONTH(updated_at) = ? THEN 1 ELSE 0 END) as monthly_completed
        ', ['pending', 'shipping', 'completed', $now->year, $now->month])
            ->first();

        $orderTrendRaw = Order::where('status', 'completed')
            ->where('updated_at', '>=', $trendStart)
            ->selectRaw('YEAR(updated_at) as yr, MONTH(updated_at) as mo, COUNT(*) as cnt')
            ->groupByRaw('YEAR(updated_at), MONTH(updated_at)')
            ->get()
            ->keyBy(fn($r) => "{$r->yr}-{$r->mo}");

        $orderTrend = $last6->map(
            fn($d) => (int) ($orderTrendRaw->get("{$d->year}-{$d->month}")?->cnt ?? 0)
        )->values();

        $coupons = Coupon::orderBy('id')
            ->get(['id', 'code', 'expires_at', 'is_active']);

        $ads = Advertisement::orderBy('display_start_at')
            ->get(['id', 'title', 'display_start_at', 'display_end_at', 'is_active']);

        return response()->json([
            'users' => [
                'total'          => (int) $userStats->total,
                'monthly_growth' => (int) $userStats->monthly_growth,
                'inactive'       => (int) $userStats->inactive,
                'trend'          => ['labels' => $trendLabels, 'data' => $userTrend],
            ],
            'products' => [
                'total'          => (int) $productStats->total,
                'active'         => (int) $productStats->active,
                'inactive'       => (int) $productStats->inactive,
                'low_stock'      => (int) $productStats->low_stock,
                'top3'           => $top3,
                'low_stock_list' => $lowStockList,
            ],
            'orders' => [
                'pending'           => (int) $orderStats->pending,
                'shipping'          => (int) $orderStats->shipping,
                'monthly_completed' => (int) $orderStats->monthly_completed,
                'trend'             => ['labels' => $trendLabels, 'data' => $orderTrend],
            ],
            'coupons' => $coupons,
            'ads'     => $ads,
        ]);
    }
}
