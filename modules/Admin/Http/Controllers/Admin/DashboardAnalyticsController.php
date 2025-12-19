<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Order\Entities\Order;

class DashboardAnalyticsController
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
        $trafficBreakdown = $this->trafficBreakdown($start, $end);

        $byDate = [];

        foreach ($dailyOrdersRevenue as $row) {
            $byDate[$row->date] = [
                'date' => $row->date,
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
                'aov' => (int) $row->orders > 0 ? (float) $row->revenue / (int) $row->orders : 0.0,
                'new_customers' => 0,
                'returning_customers' => 0,
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
                ];
            }
        }

        $totOrders = array_sum(array_column($daily, 'orders'));
        $totRevenue = array_sum(array_column($daily, 'revenue'));
        $totNew = array_sum(array_column($daily, 'new_customers'));
        $totReturning = array_sum(array_column($daily, 'returning_customers'));

        $aov = $totOrders > 0 ? $totRevenue / $totOrders : 0.0;
        $repeatRate = ($totNew + $totReturning) > 0 ? $totReturning / ($totNew + $totReturning) : 0.0;

        return response()->json([
            'daily' => $daily,
            'totals' => [
                'orders' => (int) $totOrders,
                'revenue' => (float) $totRevenue,
                'aov' => (float) $aov,
                'new_customers' => (int) $totNew,
                'returning_customers' => (int) $totReturning,
                'repeat_rate' => (float) $repeatRate,
            ],
            'top_products' => $topProducts,
            'traffic_breakdown' => $trafficBreakdown,
            'group' => $group,
        ]);
    }

    private function trafficBreakdown(Carbon $start, Carbon $end): array
    {
        $rows = Order::query()
            ->withoutCanceledOrders()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("COALESCE(NULLIF(traffic_source, ''), 'other') as source")
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('SUM(total) as revenue')
            ->groupBy(DB::raw("COALESCE(NULLIF(traffic_source, ''), 'other')"))
            ->get();

        $map = $rows
            ->mapWithKeys(function ($r) {
                return [
                    (string) $r->source => [
                        'source' => (string) $r->source,
                        'orders' => (int) $r->orders,
                        'revenue' => (float) $r->revenue,
                    ],
                ];
            })
            ->all();

        $preferred = [
            'google_ads',
            'instagram_ads',
            'facebook_ads',
            'google_organic',
            'direct',
            'etsy',
            'other',
        ];

        $out = [];
        $seen = [];

        foreach ($preferred as $key) {
            if (!isset($map[$key])) {
                continue;
            }
            $row = $map[$key];
            if (($row['orders'] ?? 0) <= 0 && (float) ($row['revenue'] ?? 0) <= 0) {
                continue;
            }
            $out[] = $row;
            $seen[$key] = true;
        }

        foreach ($map as $key => $row) {
            if (isset($seen[$key])) {
                continue;
            }
            if (($row['orders'] ?? 0) <= 0 && (float) ($row['revenue'] ?? 0) <= 0) {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }

    private function dailyOrdersRevenue(Carbon $start, Carbon $end, string $group)
    {
        $dateExpr = $group === 'month'
            ? "DATE_FORMAT(created_at, '%Y-%m')"
            : 'DATE(created_at)';

        return Order::query()
            ->withoutCanceledOrders()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("{$dateExpr} as date")
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('SUM(total) as revenue')
            ->groupBy(DB::raw($dateExpr))
            ->orderBy(DB::raw($dateExpr))
            ->get();
    }

    private function newVsReturningCustomers(Carbon $start, Carbon $end, string $group)
    {
        $sub = Order::query()
            ->withoutCanceledOrders()
            ->selectRaw("COALESCE(CAST(customer_id AS CHAR), CONCAT('email:', customer_email)) as customer_key")
            ->selectRaw('MIN(created_at) as first_order_at')
            ->selectRaw('MAX(created_at) as last_order_at')
            ->groupBy('customer_key');

        $dateExpr = $group === 'month'
            ? "DATE_FORMAT(c.last_order_at, '%Y-%m')"
            : 'DATE(c.last_order_at)';

        return DB::query()
            ->fromSub($sub, 'c')
            ->whereBetween('c.last_order_at', [$start, $end])
            ->selectRaw("{$dateExpr} as date")
            ->selectRaw('SUM(CASE WHEN c.first_order_at >= ? THEN 1 ELSE 0 END) as new_customers', [$start])
            ->selectRaw('SUM(CASE WHEN c.first_order_at < ? THEN 1 ELSE 0 END) as returning_customers', [$start])
            ->groupBy(DB::raw($dateExpr))
            ->orderBy(DB::raw($dateExpr))
            ->get();
    }

    private function topProducts(Carbon $start, Carbon $end, int $limit = 10): array
    {
        $items = DB::table('order_products as op')
            ->join('orders as o', 'o.id', '=', 'op.order_id')
            ->leftJoin('order_product_variations as opv', 'opv.order_product_id', '=', 'op.id')
            ->whereNull('o.deleted_at')
            ->whereNotIn('o.status', [Order::CANCELED, Order::REFUNDED])
            ->whereBetween('o.created_at', [$start, $end])
            ->selectRaw('op.id as order_product_id')
            ->selectRaw('op.product_id as product_id')
            ->selectRaw('op.product_variant_id as product_variant_id')
            ->selectRaw('MAX(op.product_name) as product_name')
            ->selectRaw('MAX(op.product_image_path) as product_image_path')
            ->selectRaw('MAX(op.unit_label) as unit_label')
            ->selectRaw('MAX(op.unit_short_suffix) as unit_short_suffix')
            ->selectRaw('op.qty as qty')
            ->selectRaw('op.line_total as line_total')
            ->selectRaw("GROUP_CONCAT(DISTINCT opv.value ORDER BY opv.id SEPARATOR ' / ') as variant_label")
            ->groupBy('op.id', 'op.product_id', 'op.product_variant_id', 'op.qty', 'op.line_total')

            ;

        $rows = DB::query()
            ->fromSub($items, 'x')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'x.product_variant_id')
            ->selectRaw('x.product_id as product_id')
            ->selectRaw('x.product_variant_id as product_variant_id')
            ->selectRaw('MAX(x.product_name) as product_name')
            ->selectRaw('MAX(x.product_image_path) as product_image_path')
            ->selectRaw('MAX(x.unit_label) as unit_label')
            ->selectRaw('MAX(x.unit_short_suffix) as unit_short_suffix')
            ->selectRaw('MAX(pv.name) as variant_name')
            ->selectRaw('MAX(x.variant_label) as variant_label')
            ->selectRaw('SUM(x.qty) as orders_qty')
            ->selectRaw('SUM(x.line_total) as revenue')
            ->groupBy('x.product_id', 'x.product_variant_id', 'x.variant_label')
            ->orderByDesc('orders_qty')
            ->limit($limit)
            ->get();

        $productIds = $rows->pluck('product_id')->filter()->unique()->values()->all();
        $variantIds = $rows->pluck('product_variant_id')->filter()->unique()->values()->all();

        $productsById = Product::query()
            ->withoutGlobalScope('active')
            ->withBaseImage()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $variantsById = ProductVariant::query()
            ->withoutGlobalScope('active')
            ->withTrashed()
            ->withBaseImage()
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        return $rows
            ->map(function ($row) use ($productsById, $variantsById) {
                $productId = $row->product_id ? (int) $row->product_id : null;
                $variantId = $row->product_variant_id ? (int) $row->product_variant_id : null;

                $productName = (string) ($row->product_name ?? '');
                $variantName = (string) ($row->variant_name ?? '');
                $variantLabel = (string) ($row->variant_label ?? '');

                $displayName = $productName;
                $displayVariant = $variantLabel !== '' ? $variantLabel : $variantName;

                $product = $productId ? $productsById->get($productId) : null;
                $variant = $variantId ? $variantsById->get($variantId) : null;

                $imageUrl = null;

                if (!$imageUrl) {
                    $imageUrl = $this->normalizeImageUrl($row->product_image_path ?: null);
                }

                if (!$imageUrl && $variant) {
                    $imageUrl = media_variant_url($variant->base_image, 80) ?: $this->normalizeImageUrl($variant->base_image?->path ?: null);
                }

                if (!$imageUrl && $product) {
                    $imageUrl = media_variant_url($product->base_image, 80) ?: $this->normalizeImageUrl($product->base_image?->path ?: null);
                }

                $imageUrl = $this->makeRelativeUrl($imageUrl);
                $imageUrl = $this->onlyIfPublicStorageFileExists($imageUrl);

                $qty = (float) $row->orders_qty;
                $qtyValue = rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
                $unitSuffix = $row->unit_short_suffix ?: $row->unit_label;
                $qtyDisplay = $unitSuffix ? trim($qtyValue . ' ' . $unitSuffix) : $qtyValue;

                return [
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'name' => $displayName,
                    'variant' => $displayVariant,
                    'image_url' => $imageUrl,
                    'orders_qty' => $qty,
                    'qty_display' => $qtyDisplay,
                    'revenue' => (float) $row->revenue,
                ];
            })
            ->values()
            ->all();
    }

    private function normalizeImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim($path);

        if ($path === '') {
            return null;
        }

        return $this->makeRelativeUrl($path);
    }

    private function makeRelativeUrl(?string $urlOrPath): ?string
    {
        if (!$urlOrPath) {
            return null;
        }

        $urlOrPath = trim($urlOrPath);

        if ($urlOrPath === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $urlOrPath)) {
            $parts = parse_url($urlOrPath);
            $path = $parts['path'] ?? '';
            $query = $parts['query'] ?? '';

            if ($path === '') {
                return null;
            }

            return $query !== '' ? ($path . '?' . $query) : $path;
        }

        if (str_starts_with($urlOrPath, '/')) {
            return $urlOrPath;
        }

        if (str_starts_with($urlOrPath, 'storage/')) {
            return '/' . $urlOrPath;
        }

        return '/storage/' . ltrim($urlOrPath, '/');
    }

    private function onlyIfPublicStorageFileExists(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $parts = parse_url($url);
        $path = $parts['path'] ?? null;

        if (!$path) {
            return $url;
        }

        $pos = strpos($path, '/storage/');

        if ($pos === false) {
            return $url;
        }

        $relative = ltrim(substr($path, $pos + strlen('/storage/')), '/');

        if ($relative === '') {
            return null;
        }

        $candidates = [$relative];

        if (!str_starts_with($relative, 'media/')) {
            $candidates[] = 'media/' . ltrim($relative, '/');
        }

        $disks = array_values(array_unique([
            'public',
            (string) config('filesystems.default'),
        ]));

        try {
            $exists = false;

            foreach ($disks as $disk) {
                foreach ($candidates as $candidate) {
                    if (Storage::disk($disk)->exists($candidate)) {
                        $exists = true;
                        break 2;
                    }
                }
            }

            if (!$exists) {
                return null;
            }
        } catch (\Throwable $e) {
            return $url;
        }

        return $url;
    }
}
