<?php

namespace Modules\Storefront\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class CarouselProduct2Controller extends ProductIndexController
{
    /**
     * Return products for the second home page carousel section.
     *
     * @return Response
     */
    public function index()
    {
        $cacheKey = md5(json_encode([
            'prefix' => 'storefront_carousel_section_2',
            'type' => setting('storefront_carousel_section_2_product_type'),
            'products' => setting('storefront_carousel_section_2_products', []),
            'category_id' => setting('storefront_carousel_section_2_category_id'),
            'tags' => setting('storefront_carousel_section_2_tags', []),
            'sort_by' => setting('storefront_carousel_section_2_sort_by'),
            'only_in_stock' => (bool) setting('storefront_carousel_section_2_only_in_stock'),
        ]));

        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $products = $this->getProducts('storefront_carousel_section_2');

            if (setting('storefront_carousel_section_2_only_in_stock')) {
                $products = $products->filter(function ($p) {
                    return ! empty($p['is_in_stock']);
                });
            }

            $sortBy = setting('storefront_carousel_section_2_sort_by');

            if ($sortBy === 'random') {
                $products = $products->shuffle();
            } elseif ($sortBy === 'latest') {
                $products = $products->sortByDesc(function ($p) {
                    return $p['created_at'] ?? null;
                })->values();
            }

            return $products
                ->take((int) (setting('storefront_carousel_section_2_products_limit') ?: 12))
                ->values();
        });

        return response()->json($products);
    }
}
