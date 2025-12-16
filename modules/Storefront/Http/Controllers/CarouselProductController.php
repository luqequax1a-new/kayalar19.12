<?php

namespace Modules\Storefront\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class CarouselProductController extends ProductIndexController
{
    /**
     * Return products for the home page carousel section.
     *
     * @return Response
     */
    public function index()
    {
        $cacheKey = md5(json_encode([
            'prefix' => 'storefront_carousel_section',
            'type' => setting('storefront_carousel_section_product_type'),
            'products' => setting('storefront_carousel_section_products', []),
            'category_id' => setting('storefront_carousel_section_category_id'),
            'tags' => setting('storefront_carousel_section_tags', []),
            'sort_by' => setting('storefront_carousel_section_sort_by'),
            'only_in_stock' => (bool) setting('storefront_carousel_section_only_in_stock'),
        ]));

        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $products = $this->getProducts('storefront_carousel_section');

            // Sadece stokta olan ürünler
            if (setting('storefront_carousel_section_only_in_stock')) {
                $products = $products->filter(function ($p) {
                    return !empty($p['is_in_stock']);
                });
            }

            // Sıralama
            $sortBy = setting('storefront_carousel_section_sort_by');

            if ($sortBy === 'random') {
                $products = $products->shuffle();
            } elseif ($sortBy === 'latest') {
                $products = $products->sortByDesc(function ($p) {
                    return $p['created_at'] ?? null;
                })->values();
            }

            return $products
                ->take((int) (setting('storefront_carousel_section_products_limit') ?: 12))
                ->values();
        });

        return response()->json($products);
    }
}
