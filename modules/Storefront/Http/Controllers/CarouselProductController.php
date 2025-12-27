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
            'variants_mode' => setting('storefront_carousel_section_variants_mode') ?: 'inherit',
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

            // Preserve variant grouping (keep all variants of the same product contiguous).
            // When list_variants_separately is enabled, getProducts may return multiple items
            // for the same product id; sorting/shuffling must be applied at the product-group level.
            $groups = $products
                ->groupBy(function ($p) {
                    return $p['id'] ?? null;
                })
                ->values();

            $limit = (int) (setting('storefront_carousel_section_products_limit') ?: 12);

            if ($sortBy === 'random') {
                $groups = $groups->shuffle()->values();
            } elseif ($sortBy === 'latest') {
                $groups = $groups->sortByDesc(function ($g) {
                    $first = $g->first();
                    return $first['created_at'] ?? null;
                })->values();
            }

            // Apply limit at group level so we don't truncate variant groups.
            $groups = $groups->take($limit)->values();

            return $groups
                ->flatMap(function ($g) {
                    return $g->sortBy(function ($row) {
                        return data_get($row, 'variant.position', 0);
                    })->values();
                })
                ->values();
        });

        return response()->json($products);
    }
}
