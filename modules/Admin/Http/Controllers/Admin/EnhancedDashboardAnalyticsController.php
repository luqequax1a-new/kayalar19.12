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

        $groupVariants = $request->query('group_variants', '1') === '1';
        $cacheKey = "dashboard_analytics_{$preset}_{$limit}_{$groupVariants}_v2_" . now()->format('Y-m-d_H');

        if ($request->query('clear_cache') === '1') {
            Cache::forget($cacheKey);
        }

        if (Cache::has($cacheKey)) {
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
        $topProducts = $this->topProducts($start, $end, $limit, $groupVariants);
        $topCategories = $this->topCategories($start, $end, 10);
        $topBrands = $this->topBrands($start, $end, 10);
        $trafficBreakdown = $this->trafficBreakdown($start, $end);
        $hourlyData = $this->hourlyOrdersData($start, $end);
        $conversionData = $this->conversionMetrics($start, $end);
        $orderStatusData = $this->orderStatusDistribution($start, $end);
        $stockStatusData = $this->stockStatusMetrics();
        $abandonedCartData = $this->abandonedCartTrend($start, $end, $group);

        $byDate = [];

        foreach ($dailyOrdersRevenue as $row) {
            $byDate[$row->date] = [
                'date' => $row->date,
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
                'aov' => (int) $row->orders > 0 ? (float) $row->revenue / (int) $row->orders : 0.0,
                'new_customers' => 0, 'returning_customers' => 0, 'abandoned' => 0, 'recovered' => 0,
            ];
        }

        foreach ($dailyCustomers as $row) {
            if (!isset($byDate[$row->date])) {
                $byDate[$row->date] = [
                    'date' => $row->date,
                    'orders' => 0, 'revenue' => 0.0, 'aov' => 0.0,
                    'new_customers' => 0, 'returning_customers' => 0, 'abandoned' => 0, 'recovered' => 0,
                ];
            }
            $byDate[$row->date]['new_customers'] = (int) $row->new_customers;
            $byDate[$row->date]['returning_customers'] = (int) $row->returning_customers;
        }

        foreach ($abandonedCartData as $row) {
            if (!isset($byDate[$row->date])) {
                $byDate[$row->date] = [
                    'date' => $row->date,
                    'orders' => 0, 'revenue' => 0.0, 'aov' => 0.0,
                    'new_customers' => 0, 'returning_customers' => 0, 'abandoned' => 0, 'recovered' => 0,
                ];
            }
            $byDate[$row->date]['abandoned'] = (int) $row->abandoned;
            $byDate[$row->date]['recovered'] = (int) $row->recovered;
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
                    'orders' => 0, 'revenue' => 0.0, 'aov' => 0.0,
                    'new_customers' => 0, 'returning_customers' => 0, 'abandoned' => 0, 'recovered' => 0,
                ];
            }
        }

        $totOrders = array_sum(array_column($daily, 'orders'));
        $totRevenue = array_sum(array_column($daily, 'revenue'));
        $uniqueStats = $this->getUniqueCustomerStats($start, $end);
        $totAbandoned = array_sum(array_column($daily, 'abandoned'));
        $totRecovered = array_sum(array_column($daily, 'recovered'));

        $responseData = [
            'daily' => $daily,
            'hourly' => $hourlyData,
            'conversion' => $conversionData,
            'totals' => [
                'orders' => (int) $totOrders,
                'revenue' => (float) $totRevenue,
                'aov' => $totOrders > 0 ? (float) ($totRevenue / $totOrders) : 0.0,
                'new_customers' => (int) $uniqueStats['new'],
                'returning_customers' => (int) $uniqueStats['returning'],
                'repeat_rate' => ($uniqueStats['new'] + $uniqueStats['returning']) > 0 ? (float) ($uniqueStats['returning'] / ($uniqueStats['new'] + $uniqueStats['returning'])) : 0.0,
                'abandoned' => (int) $totAbandoned,
                'recovered' => (int) $totRecovered,
                'recovery_rate' => $totAbandoned > 0 ? ($totRecovered / $totAbandoned) * 100 : 0,
            ],
            'top_products' => $topProducts,
            'top_categories' => $topCategories,
            'top_brands' => $topBrands,
            'traffic_breakdown' => $trafficBreakdown,
            'order_status' => $orderStatusData,
            'stock_status' => $stockStatusData,
            'group' => $group,
        ];

        $cacheTTL = match($preset) {
            'today', 'yesterday' => now()->addMinutes(5),
            '7' => now()->addMinutes(15),
            '14' => now()->addMinutes(30),
            '30', 'all' => now()->addHour(),
            default => now()->addMinutes(10),
        };
        Cache::put($cacheKey, $responseData, $cacheTTL);

        return response()->json($responseData);
    }

    public function topCategories(Carbon $start, Carbon $end, int $limit = 10): array
    {
        $loc = app()->getLocale();
        $fallbackCategories = DB::table('product_categories')->selectRaw('product_id, MIN(category_id) as fallback_category_id')->groupBy('product_id');
        $rows = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->leftJoinSub($fallbackCategories, 'pc', function ($join) { $join->on('pc.product_id', '=', 'p.id'); })
            ->leftJoin('category_translations as ct', function ($join) use ($loc) { $join->on('ct.category_id', '=', DB::raw('COALESCE(p.primary_category_id, pc.fallback_category_id)'))->where('ct.locale', '=', $loc); })
            ->whereNull('o.deleted_at')->whereNotIn('o.status', [Order::CANCELED, Order::REFUNDED])->whereBetween('o.created_at', [$start, $end])
            ->whereRaw('COALESCE(p.primary_category_id, pc.fallback_category_id) IS NOT NULL')
            ->selectRaw('COALESCE(p.primary_category_id, pc.fallback_category_id) as category_id')->selectRaw('MAX(ct.name) as name')->selectRaw('SUM(op.qty) as orders_qty')->selectRaw('SUM(op.line_total) as revenue')
            ->groupBy(DB::raw('COALESCE(p.primary_category_id, pc.fallback_category_id)'))->orderByDesc(DB::raw('SUM(op.qty)'))->limit($limit)->get();
        return $rows->map(function ($r) { return ['category_id' => (int) $r->category_id, 'name' => (string) ($r->name ?: ('#' . (int) $r->category_id)), 'orders_qty' => (float) $r->orders_qty, 'revenue' => (float) $r->revenue]; })->values()->all();
    }

    public function topBrands(Carbon $start, Carbon $end, int $limit = 10): array
    {
        $loc = app()->getLocale();
        $rows = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->join('products as p', 'p.id', '=', 'op.product_id')
            ->leftJoin('brand_translations as bt', function ($join) use ($loc) { $join->on('bt.brand_id', '=', 'p.brand_id')->where('bt.locale', '=', $loc); })
            ->whereNull('o.deleted_at')->whereNotIn('o.status', [Order::CANCELED, Order::REFUNDED])->whereBetween('o.created_at', [$start, $end])
            ->whereNotNull('p.brand_id')->selectRaw('p.brand_id as brand_id')->selectRaw('MAX(bt.name) as name')->selectRaw('SUM(op.qty) as orders_qty')->selectRaw('SUM(op.line_total) as revenue')
            ->groupBy('p.brand_id')->orderByDesc(DB::raw('SUM(op.qty)'))->limit($limit)->get();
        return $rows->map(function ($r) { return ['brand_id' => (int) $r->brand_id, 'name' => (string) ($r->name ?: ('#' . (int) $r->brand_id)), 'orders_qty' => (float) $r->orders_qty, 'revenue' => (float) $r->revenue]; })->values()->all();
    }

    public function hourlyOrdersData(Carbon $start, Carbon $end)
    {
        return Order::query()->withoutCanceledOrders()->whereBetween('created_at', [$start, $end])->selectRaw('HOUR(created_at) as hour')->selectRaw('COUNT(*) as orders')->selectRaw('SUM(total) as revenue')->groupBy(DB::raw('HOUR(created_at)'))->orderBy(DB::raw('HOUR(created_at)'))->get()->map(function ($row) { return ['hour' => (int) $row->hour, 'orders' => (int) $row->orders, 'revenue' => (float) $row->revenue]; })->all();
    }

    public function conversionMetrics(Carbon $start, Carbon $end)
    {
        $totalVisits = 0;
        try { if (\Schema::hasTable('page_views')) { $totalVisits = DB::table('page_views')->whereBetween('created_at', [$start, $end])->distinct('ip_address')->count('ip_address'); if ($totalVisits === 0) { $totalVisits = DB::table('page_views')->whereBetween('created_at', [$start, $end])->distinct('session_id')->count('session_id'); } } } catch (\Throwable $e) {}
        if ($totalVisits === 0) { try { $totalVisits = \Modules\Cart\Entities\Cart::whereBetween('updated_at', [$start, $end])->where('id', 'like', '%_cart_items')->distinct('id')->count(); if ($totalVisits === 0) { $totalVisits = Order::query()->withoutCanceledOrders()->whereBetween('created_at', [$start, $end])->count() * 10; } } catch (\Throwable $e) { $totalVisits = Order::query()->withoutCanceledOrders()->whereBetween('created_at', [$start, $end])->count() * 10; } }
        $totalOrders = Order::query()->withoutCanceledOrders()->whereBetween('created_at', [$start, $end])->count();
        return ['visits' => $totalVisits, 'orders' => $totalOrders, 'conversion_rate' => $totalVisits > 0 ? round(($totalOrders / $totalVisits) * 100, 2) : 0];
    }

    public function orderStatusDistribution(Carbon $start, Carbon $end): array
    {
        return Order::query()->whereBetween('created_at', [$start, $end])->select('status')->selectRaw('count(*) as count')->groupBy('status')->get()->map(function ($s) { return ['status' => $s->status, 'label' => trans("order::statuses.{$s->status}"), 'count' => (int) $s->count]; })->all();
    }

    public function stockStatusMetrics(): array
    {
        $lowStockThreshold = 5;
        return ['in_stock' => Product::query()->withoutGlobalScope('active')->where('manage_stock', true)->where('qty', '>', $lowStockThreshold)->count(), 'low_stock' => Product::query()->withoutGlobalScope('active')->where('manage_stock', true)->whereBetween('qty', [1, $lowStockThreshold])->count(), 'out_of_stock' => Product::query()->withoutGlobalScope('active')->where('manage_stock', true)->where('qty', '<=', 0)->count()];
    }

    public function topProducts(Carbon $start, Carbon $end, int $limit = 10, bool $groupVariants = true): array
    {
        try {
            $orderTable = (new Order)->getTable();
            $variantTable = (new ProductVariant)->getTable();
            $orderProductTable = (new \Modules\Order\Entities\OrderProduct)->getTable();

            $query = \Modules\Order\Entities\OrderProduct::query()
                ->join($orderTable, "{$orderTable}.id", '=', "{$orderProductTable}.order_id")
                ->leftJoin($variantTable, "{$variantTable}.id", '=', "{$orderProductTable}.product_variant_id")
                ->whereNull("{$orderTable}.deleted_at")
                ->whereNotIn("{$orderTable}.status", [Order::CANCELED, Order::REFUNDED])
                ->whereBetween("{$orderTable}.created_at", [$start, $end]);

            if ($groupVariants) {
                $query->selectRaw("{$orderProductTable}.product_id, {$orderProductTable}.product_variant_id")
                    ->selectRaw("MAX({$orderProductTable}.product_name) as product_name")
                    ->selectRaw("MAX({$variantTable}.name) as variant_name")
                    ->selectRaw("MAX({$orderProductTable}.product_image_path) as product_image_path")
                    ->selectRaw("MAX({$orderProductTable}.unit_label) as unit_label")
                    ->selectRaw("MAX({$orderProductTable}.unit_short_suffix) as unit_short_suffix")
                    ->selectRaw("SUM({$orderProductTable}.qty) as orders_qty")
                    ->selectRaw("SUM({$orderProductTable}.line_total) as revenue")
                    ->groupBy("{$orderProductTable}.product_id", "{$orderProductTable}.product_variant_id");
            } else {
                $query->selectRaw("{$orderProductTable}.product_id, NULL as product_variant_id")
                    ->selectRaw("MAX({$orderProductTable}.product_name) as product_name")
                    ->selectRaw('NULL as variant_name')
                    ->selectRaw("MAX({$orderProductTable}.product_image_path) as product_image_path")
                    ->selectRaw("MAX({$orderProductTable}.unit_label) as unit_label")
                    ->selectRaw("MAX({$orderProductTable}.unit_short_suffix) as unit_short_suffix")
                    ->selectRaw("SUM({$orderProductTable}.qty) as orders_qty")
                    ->selectRaw("SUM({$orderProductTable}.line_total) as revenue")
                    ->groupBy("{$orderProductTable}.product_id");
            }

            $rows = $query->orderByDesc('orders_qty')->limit($limit)->get();

            if ($rows->isEmpty()) { return []; }

            $productIds = $rows->pluck('product_id')->filter()->unique()->values()->all();
            $variantIds = $rows->pluck('product_variant_id')->filter()->unique()->values()->all();

            $productsById = Product::query()->withoutGlobalScope('active')->withBaseImage()->whereIn('id', $productIds)->get()->keyBy('id');
            $variantsById = ProductVariant::query()->withoutGlobalScope('active')->withTrashed()->withBaseImage()->whereIn('id', $variantIds)->get()->keyBy('id');

            return $rows->map(function ($row) use ($productsById, $variantsById) {
                $product = $row->product_id ? $productsById->get($row->product_id) : null;
                $variant = $row->product_variant_id ? $variantsById->get($row->product_variant_id) : null;
                $imageUrl = $this->normalizeImageUrl($row->product_image_path ?: null);
                if (!$imageUrl && $variant) $imageUrl = media_variant_url($variant->base_image, 80) ?: $this->normalizeImageUrl($variant->base_image?->path ?: null);
                if (!$imageUrl && $product) $imageUrl = media_variant_url($product->base_image, 80) ?: $this->normalizeImageUrl($product->base_image?->path ?: null);
                $qty = (float) $row->orders_qty;
                $unitSuffix = $row->unit_short_suffix ?: $row->unit_label;
                $qtyDisplay = rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
                if ($unitSuffix) $qtyDisplay .= ' ' . $unitSuffix;
                return ['product_id' => $row->product_id, 'product_variant_id' => $row->product_variant_id, 'name' => (string) ($row->product_name ?? ''), 'variant' => (string) ($row->variant_name ?? ''), 'image_url' => $this->onlyIfPublicStorageFileExists($this->makeRelativeUrl($imageUrl)), 'orders_qty' => $qty, 'qty_display' => $qtyDisplay, 'revenue' => (float) $row->revenue];
            })->values()->all();
        } catch (\Exception $e) { \Illuminate\Support\Facades\Log::error('topProducts Error: ' . $e->getMessage()); return []; }
    }

    public function getUniqueCustomerStats(Carbon $start, Carbon $end): array
    {
        $customerKeyExpr = "COALESCE(CAST(customer_id AS CHAR), CONCAT('email:', customer_email))";
        $firstOrdersSub = Order::query()->withoutCanceledOrders()->selectRaw("{$customerKeyExpr} as customer_key")->selectRaw('MIN(created_at) as first_order_at')->groupBy(DB::raw($customerKeyExpr));
        $stats = DB::table('orders as o')->joinSub($firstOrdersSub, 'c', function ($join) use ($customerKeyExpr) { $join->on(DB::raw($customerKeyExpr), '=', 'c.customer_key'); })->whereNull('o.deleted_at')->whereNotIn('o.status', [Order::CANCELED, Order::REFUNDED])->whereBetween('o.created_at', [$start, $end])->selectRaw("COUNT(DISTINCT CASE WHEN c.first_order_at >= ? AND c.first_order_at <= ? THEN c.customer_key END) as new_customers", [$start, $end])->selectRaw("COUNT(DISTINCT CASE WHEN c.first_order_at < ? THEN c.customer_key END) as returning_customers", [$start])->first();
        return ['new' => (int) ($stats->new_customers ?? 0), 'returning' => (int) ($stats->returning_customers ?? 0)];
    }

    public function abandonedCartTrend(Carbon $start, Carbon $end, string $group): array
    {
        $dateExpr = $group === 'month' ? "DATE_FORMAT(updated_at, '%Y-%m')" : 'DATE(updated_at)';
        return DB::table('carts')->whereBetween('updated_at', [$start, $end])->where('id', 'like', '%_cart_items')->where(function($q) { $q->whereNotNull('customer_email')->orWhereNotNull('customer_phone'); })->selectRaw("{$dateExpr} as date")->selectRaw("COUNT(CASE WHEN is_recovered = 0 THEN 1 END) as abandoned")->selectRaw("COUNT(CASE WHEN is_recovered = 1 THEN 1 END) as recovered")->groupBy(DB::raw($dateExpr))->get()->toArray();
    }
}
