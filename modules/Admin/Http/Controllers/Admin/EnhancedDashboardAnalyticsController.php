<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Order\Entities\Order;

class EnhancedDashboardAnalyticsController extends DashboardAnalyticsController
{
    public function index(Request $request)
    {
        $rangeParam = $request->query('range', '30');
        $preset = is_string($rangeParam) ? strtolower(trim($rangeParam)) : '30';

        $limitParam = $request->query('top_products_limit', 10);
        $limit = is_numeric($limitParam) ? (int) $limitParam : 10;
        $allowedLimits = [5, 10, 15, 20];
        if (!in_array($limit, $allowedLimits, true)) {
            $limit = 10;
        }

        $cacheKey = "dashboard_analytics_{$preset}_{$limit}_" . now()->format('Y-m-d_H');

        // Only use cache for non-today presets and non-recent data
        $shouldCache = !in_array($preset, ['today', 'yesterday']);
        
        if ($shouldCache && Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $group = 'day';

        if ($preset === 'today') {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
        } elseif ($preset === 'yesterday') {
            $start = now()->subDay()->startOfDay();
            $end = now()->subDay()->endOfDay();
        } elseif (in_array($preset, ['7', '14', '30'], true)) {
            $days = (int) $preset;
            $start = now()->subDays($days - 1)->startOfDay();
            $end = now()->endOfDay();
        } elseif ($preset === 'all') {
            $group = 'month';
            $min = Order::query()->withoutCanceledOrders()->min('created_at');
            $start = $min ? Carbon::parse($min)->startOfDay() : now()->startOfDay();
            $end = now()->endOfDay();
        } else {
            $start = now()->subDays(29)->startOfDay();
            $end = now()->endOfDay();
        }

        $dailyOrdersRevenue = $this->dailyOrdersRevenue($start, $end, $group);
        $dailyCustomers = $this->newVsReturningCustomers($start, $end, $group);
        $topProducts = $this->topProducts($start, $end, $limit);
        $topCategories = $this->topCategories($start, $end, 10);
        $topBrands = $this->topBrands($start, $end, 10);
        $trafficBreakdown = $this->trafficBreakdown($start, $end);
        $hourlyData = $this->hourlyOrdersData($start, $end);
        $conversionData = $this->conversionMetrics($start, $end);
        $orderStatusData = $this->orderStatusDistribution($start, $end);
        $stockStatusData = $this->stockStatusMetrics();

        $byDate = [];

        foreach ($dailyOrdersRevenue as $row) {
            $byDate[$row->date] = [
                'date' => $row->date,
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
                'aov' => (int) $row->orders > 0 ? (float) $row->revenue / (int) $row->orders : 0.0,
                'new_customers' => 0,
                'returning_customers' => 0,
                'abandoned' => 0,
                'recovered' => 0,
            ];
        }



        foreach ($dailyCustomers as $row) {
            if (!isset($byDate[$row->date])) {
                $byDate[$row->date] = [
                    'date' => $row->date,
                    'orders' => 0,
                    'revenue' => 0.0,
                    'aov' => 0.0,
                    'new_customers' => 0,
                    'returning_customers' => 0,
                    'abandoned' => 0,
                    'recovered' => 0,

                ];
            }

            $byDate[$row->date]['new_customers'] = (int) $row->new_customers;
            $byDate[$row->date]['returning_customers'] = (int) $row->returning_customers;
        }

        if ($group === 'month') {
            $daily = array_values($byDate);
        } else {
            $rangeDays = (int) $start->diffInDays($end) + 1;
            $daily = [];

            for ($i = 0; $i < $rangeDays; $i++) {
                $d = $start->copy()->addDays($i)->toDateString();

                $daily[] = $byDate[$d] ?? [
                    'date' => $d,
                    'orders' => 0,
                    'revenue' => 0.0,
                    'aov' => 0.0,
                    'new_customers' => 0,
                    'returning_customers' => 0,
                    'abandoned' => 0,
                    'recovered' => 0,

                ];
            }
        }

        $totOrders = array_sum(array_column($daily, 'orders'));
        $totRevenue = array_sum(array_column($daily, 'revenue'));
        $totNew = array_sum(array_column($daily, 'new_customers'));
        $totReturning = array_sum(array_column($daily, 'returning_customers'));

        $aov = $totOrders > 0 ? $totRevenue / $totOrders : 0.0;
        $repeatRate = ($totNew + $totReturning) > 0 ? $totReturning / ($totNew + $totReturning) : 0.0;

        $responseData = [
            'daily' => $daily,
            'hourly' => $hourlyData,
            'conversion' => $conversionData,
            'totals' => [
                'orders' => (int) $totOrders,
                'revenue' => (float) $totRevenue,
                'aov' => (float) $aov,
                'new_customers' => (int) $totNew,
                'returning_customers' => (int) $totReturning,
                'repeat_rate' => (float) $repeatRate,
            ],
            'top_products' => $topProducts,
            'top_categories' => $topCategories,
            'top_brands' => $topBrands,
            'traffic_breakdown' => $trafficBreakdown,
            'order_status' => $orderStatusData,
            'stock_status' => $stockStatusData,

            'group' => $group,
        ];

        // Ensure consistent data formats
        if (!is_array($responseData['top_products'])) {
            $responseData['top_products'] = [];
        }
        if (!is_array($responseData['top_categories'])) {
            $responseData['top_categories'] = [];
        }
        if (!is_array($responseData['top_brands'])) {
            $responseData['top_brands'] = [];
        }
        if (!is_array($responseData['traffic_breakdown'])) {
            $responseData['traffic_breakdown'] = [];
        }

        // Cache data with appropriate TTL based on recency
        if ($shouldCache) {
            // Use shorter TTL for recent data, longer for historical
            $cacheTTL = match($preset) {
                '7' => now()->addMinutes(15),
                '14' => now()->addMinutes(30),
                '30', 'all' => now()->addHour(),
                default => now()->addMinutes(5),
            };
            
            Cache::put($cacheKey, $responseData, $cacheTTL);
        }

        return response()->json($responseData);
    }

    private function topCategories(Carbon $start, Carbon $end, int $limit = 10): array
    {
        $loc = locale();

        $fallbackCategories = DB::table('product_categories')
            ->selectRaw('product_id, MIN(category_id) as fallback_category_id')
            ->groupBy('product_id');

        $rows = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->leftJoinSub($fallbackCategories, 'pc', function ($join) {
                $join->on('pc.product_id', '=', 'p.id');
            })
            ->leftJoin('category_translations as ct', function ($join) use ($loc) {
                $join->on('ct.category_id', '=', DB::raw('COALESCE(p.primary_category_id, pc.fallback_category_id)'))
                    ->where('ct.locale', '=', $loc);
            })
            ->whereNull('o.deleted_at')
            ->whereNotIn('o.status', [Order::CANCELED, Order::REFUNDED])
            ->whereBetween('o.created_at', [$start, $end])
            ->whereRaw('COALESCE(p.primary_category_id, pc.fallback_category_id) IS NOT NULL')
            ->selectRaw('COALESCE(p.primary_category_id, pc.fallback_category_id) as category_id')
            ->selectRaw('MAX(ct.name) as name')
            ->selectRaw('SUM(op.qty) as orders_qty')
            ->selectRaw('SUM(op.line_total) as revenue')
            ->groupBy(DB::raw('COALESCE(p.primary_category_id, pc.fallback_category_id)'))
            ->orderByDesc(DB::raw('SUM(op.qty)'))
            ->limit($limit)
            ->get();

        return $rows
            ->map(function ($r) {
                return [
                    'category_id' => (int) $r->category_id,
                    'name' => (string) ($r->name ?: ('#' . (int) $r->category_id)),
                    'orders_qty' => (float) $r->orders_qty,
                    'revenue' => (float) $r->revenue,
                ];
            })
            ->values()
            ->all();
    }

    private function topBrands(Carbon $start, Carbon $end, int $limit = 10): array
    {
        $loc = locale();

        $rows = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->leftJoin('brand_translations as bt', function ($join) use ($loc) {
                $join->on('bt.brand_id', '=', 'p.brand_id')
                    ->where('bt.locale', '=', $loc);
            })
            ->whereNull('o.deleted_at')
            ->whereNotIn('o.status', [Order::CANCELED, Order::REFUNDED])
            ->whereBetween('o.created_at', [$start, $end])
            ->whereNotNull('p.brand_id')
            ->selectRaw('p.brand_id as brand_id')
            ->selectRaw('MAX(bt.name) as name')
            ->selectRaw('SUM(op.qty) as orders_qty')
            ->selectRaw('SUM(op.line_total) as revenue')
            ->groupBy('p.brand_id')
            ->orderByDesc(DB::raw('SUM(op.qty)'))
            ->limit($limit)
            ->get();

        return $rows
            ->map(function ($r) {
                return [
                    'brand_id' => (int) $r->brand_id,
                    'name' => (string) ($r->name ?: ('#' . (int) $r->brand_id)),
                    'orders_qty' => (float) $r->orders_qty,
                    'revenue' => (float) $r->revenue,
                ];
            })
            ->values()
            ->all();
    }

    private function hourlyOrdersData(Carbon $start, Carbon $end)
    {
        return Order::query()
            ->withoutCanceledOrders()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('HOUR(created_at) as hour')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('SUM(total) as revenue')
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy(DB::raw('HOUR(created_at)'))
            ->get()
            ->map(function ($row) {
                return [
                    'hour' => (int) $row->hour,
                    'orders' => (int) $row->orders,
                    'revenue' => (float) $row->revenue,
                ];
            })
            ->all();
    }

    private function conversionMetrics(Carbon $start, Carbon $end)
    {
        // Get actual unique visitors from page_views table by IP address
        $totalVisits = 0;
        
        try {
            if (\Schema::hasTable('page_views')) {
                // Count unique IP addresses (real visitors)
                $totalVisits = DB::table('page_views')
                    ->whereBetween('created_at', [$start, $end])
                    ->distinct('ip_address')
                    ->count('ip_address');
                    
                // Fallback to session if no IP data
                if ($totalVisits === 0) {
                    $totalVisits = DB::table('page_views')
                        ->whereBetween('created_at', [$start, $end])
                        ->distinct('session_id')
                        ->count('session_id');
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to get page views', [
                'error' => $e->getMessage(),
            ]);
        }
        
        // Fallback: If no page views data, estimate from unique cart sessions
        if ($totalVisits === 0) {
            try {
                // Count unique sessions that created carts (only _cart_items, not _cart_conditions)
                $totalVisits = \Modules\Cart\Entities\Cart::whereBetween('updated_at', [$start, $end])
                    ->where('id', 'like', '%_cart_items')
                    ->distinct('id')
                    ->count();
                    
                // If still zero, use a conservative multiplier
                if ($totalVisits === 0) {
                    $totalVisits = Order::query()
                        ->withoutCanceledOrders()
                        ->whereBetween('created_at', [$start, $end])
                        ->count() * 10;
                }
            } catch (\Throwable $e) {
                // Last resort: use a conservative multiplier
                $totalVisits = Order::query()
                    ->withoutCanceledOrders()
                    ->whereBetween('created_at', [$start, $end])
                    ->count() * 10;
            }
        }

        $totalOrders = Order::query()
            ->withoutCanceledOrders()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $conversionRate = $totalVisits > 0 ? ($totalOrders / $totalVisits) * 100 : 0;

        return [
            'visits' => $totalVisits,
            'orders' => $totalOrders,
            'conversion_rate' => round($conversionRate, 2),
        ];
    }

    private function orderStatusDistribution(Carbon $start, Carbon $end): array
    {
        $statuses = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->get();

        return $statuses->map(function ($s) {
            return [
                'status' => $s->status,
                'label' => trans("order::statuses.{$s->status}"),
                'count' => (int) $s->count,
            ];
        })->all();
    }

    private function stockStatusMetrics(): array
    {
        $lowStockThreshold = 5;

        $inStock = Product::query()
            ->withoutGlobalScope('active')
            ->where('manage_stock', true)
            ->where('qty', '>', $lowStockThreshold)
            ->count();

        $lowStock = Product::query()
            ->withoutGlobalScope('active')
            ->where('manage_stock', true)
            ->whereBetween('qty', [1, $lowStockThreshold])
            ->count();

        $outOfStock = Product::query()
            ->withoutGlobalScope('active')
            ->where('manage_stock', true)
            ->where('qty', '<=', 0)
            ->count();

        return [
            'in_stock' => $inStock,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
        ];
    }

}
