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
            'variants_mode' => setting('storefront_carousel_section_2_variants_mode') ?: 'inherit',
        ]));

        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $products = $this->getProducts('storefront_carousel_section_2');

            if (setting('storefront_carousel_section_2_only_in_stock')) {
                $products = $products->filter(function ($p) {
                    return ! empty($p['is_in_stock']);
                });
            }

            $sortBy = setting('storefront_carousel_section_2_sort_by');

            // Preserve variant grouping (keep all variants of the same product contiguous).
            $groups = $products
                ->groupBy(function ($p) {
                    return $p['id'] ?? null;
                })
                ->values();

            $limit = (int) (setting('storefront_carousel_section_2_products_limit') ?: 12);

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
