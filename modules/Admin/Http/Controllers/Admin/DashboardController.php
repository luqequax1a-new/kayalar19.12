<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\User\Entities\User;
use Modules\Order\Entities\Order;
use Modules\Review\Entities\Review;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Entities\SearchTerm;
use Illuminate\Database\Eloquent\Collection;

class DashboardController
{
    /**
     * Display the dashboard with its widgets.
     *
     * @return Response
     */
    public function index()
    {
        return view('admin::dashboard.index', [
            'totalSales' => Order::totalSales(),
            'totalOrders' => Order::withoutCanceledOrders()->count(),
            'totalProducts' => Product::withoutGlobalScope('active')->count(),
            'totalCustomers' => User::totalCustomers(),
            'latestSearchTerms' => $this->getLatestSearchTerms(),
            'latestOrders' => $this->getLatestOrders(),
            'latestReviews' => $this->getLatestReviews(),
            'topCustomers' => $this->getTopCustomers(),
            'lowStockProducts' => $this->getLowStockProducts(),
            'latestCustomers' => $this->getLatestCustomers(),
        ]);
    }


    private function getLatestSearchTerms()
    {
        return SearchTerm::latest('updated_at')->take(5)->get();
    }


    /**
     * Get latest five orders.
     *
     * @return Collection
     */
    private function getLatestOrders()
    {
        return Order::select([
            'id',
            'order_number',
            'customer_first_name',
            'customer_last_name',
            'payment_method',
            'total',
            'status',
            'created_at',
        ])->latest()->take(15)->get();
    }


    /**
     * Get latest five reviews.
     *
     * @return Collection
     */
    private function getLatestReviews()
    {
        return Review::select('id', 'product_id', 'reviewer_name', 'rating')
            ->has('product')
            ->with('product:id')
            ->limit(5)
            ->get();
    }


    private function getTopCustomers()
    {
        return DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.customer_id')
            ->whereNotNull('orders.customer_id')
            ->whereNotIn('orders.status', [Order::CANCELED, Order::REFUNDED])
            ->groupBy('orders.customer_id', 'users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->orderByDesc(DB::raw('SUM(orders.total)'))
            ->limit(10)
            ->get([
                'users.id as id',
                'users.first_name as first_name',
                'users.last_name as last_name',
                'users.email as email',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('SUM(orders.total) as total_spent'),
            ]);
    }


    private function getLowStockProducts(): array
    {
        $threshold = 5;
        $limit = 10;

        $variants = ProductVariant::query()
            ->withoutGlobalScope('active')
            ->where('is_active', true)
            ->where('manage_stock', true)
            ->where('in_stock', true)
            ->whereNotNull('qty')
            ->where('qty', '>', 0)
            ->where('qty', '<=', $threshold)
            ->with(['product' => function ($q) {
                $q->withoutGlobalScope('active')->with(['saleUnit', 'files']);
            }])
            ->orderBy('qty')
            ->limit($limit)
            ->get();

        $rows = $variants
            ->map(function (ProductVariant $v) {
                $product = $v->product;
                $name = $product ? (string) $product->name : '—';
                $variant = (string) ($v->name ?? '');

                $thumbUrl = null;
                $previewUrl = null;
                try {
                    if (method_exists($v, 'getBaseImageAttribute') && $v->base_image && (int) ($v->base_image->id ?? 0) > 0) {
                        $thumbUrl = $v->base_image->thumb_webp_url
                            ?: $v->base_image->thumb_jpeg_url
                            ?: $v->base_image->url;
                        $previewUrl = $v->base_image->detail_webp_url
                            ?: $v->base_image->detail_jpeg_url
                            ?: $v->base_image->url;
                    }
                } catch (\Throwable $e) {
                }

                if (!$thumbUrl || !$previewUrl) {
                    try {
                        if ($product && $product->base_image && (int) ($product->base_image->id ?? 0) > 0) {
                            $thumbUrl = $thumbUrl
                                ?: ($product->base_image->thumb_webp_url
                                    ?: $product->base_image->thumb_jpeg_url
                                    ?: $product->base_image->url);
                            $previewUrl = $previewUrl
                                ?: ($product->base_image->detail_webp_url
                                    ?: $product->base_image->detail_jpeg_url
                                    ?: $product->base_image->url);
                        }
                    } catch (\Throwable $e) {
                    }
                }

                $qty = (float) ($v->qty ?? 0);
                $qtyValue = fmod($qty, 1) === 0.0
                    ? (string) (int) $qty
                    : rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');

                $suffix = '';
                try {
                    $suffix = $product && $product->saleUnit ? trim($product->saleUnit->getDisplaySuffix()) : '';
                } catch (\Throwable $e) {
                    $suffix = '';
                }

                $qtyDisplay = $suffix !== '' ? ($qtyValue . ' ' . $suffix) : $qtyValue;

                return [
                    'product_id' => (int) ($product?->id ?? $v->product_id),
                    'variant_id' => (int) ($v->id ?? 0),
                    'name' => $name,
                    'variant' => $variant,
                    'qty' => $qty,
                    'qty_display' => $qtyDisplay,
                    'unit_suffix' => $suffix,
                    'image_url' => $thumbUrl,
                    'image_preview_url' => $previewUrl,
                ];
            })
            ->values()
            ->all();

        if (count($rows) >= $limit) {
            return $rows;
        }

        $remaining = $limit - count($rows);

        $products = Product::query()
            ->withoutGlobalScope('active')
            ->with(['saleUnit', 'files'])
            ->where('manage_stock', true)
            ->where('in_stock', true)
            ->whereNotNull('qty')
            ->where('qty', '>', 0)
            ->where('qty', '<=', $threshold)
            ->orderBy('qty')
            ->limit($remaining)
            ->get();

        foreach ($products as $p) {
            $qty = (float) ($p->qty ?? 0);
            $qtyValue = fmod($qty, 1) === 0.0
                ? (string) (int) $qty
                : rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');

            $suffix = '';
            try {
                $suffix = $p->saleUnit ? trim($p->saleUnit->getDisplaySuffix()) : '';
            } catch (\Throwable $e) {
                $suffix = '';
            }

            $qtyDisplay = $suffix !== '' ? ($qtyValue . ' ' . $suffix) : $qtyValue;

            $thumbUrl = null;
            $previewUrl = null;
            try {
                if ($p->base_image && (int) ($p->base_image->id ?? 0) > 0) {
                    $thumbUrl = $p->base_image->thumb_webp_url
                        ?: $p->base_image->thumb_jpeg_url
                        ?: $p->base_image->url;
                    $previewUrl = $p->base_image->detail_webp_url
                        ?: $p->base_image->detail_jpeg_url
                        ?: $p->base_image->url;
                }
            } catch (\Throwable $e) {
            }

            $rows[] = [
                'product_id' => (int) $p->id,
                'variant_id' => 0,
                'name' => (string) $p->name,
                'variant' => '',
                'qty' => $qty,
                'qty_display' => $qtyDisplay,
                'unit_suffix' => $suffix,
                'image_url' => $thumbUrl,
                'image_preview_url' => $previewUrl,
            ];
        }

        return $rows;
    }


    private function getLatestCustomers()
    {
        return User::latest()->take(10)->get();
    }
}
