<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Media\Jobs\GenerateResponsiveImagesForMedia;

use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Attribute\Entities\Attribute;
use Modules\Product\Filters\ProductFilter;
use Modules\Product\Events\ShowingProductList;
use Modules\Tag\Entities\TagBadge;
use Modules\DynamicCategory\Entities\DynamicCategory;
use Modules\DynamicCategory\Services\DynamicCategoryProductService;

trait ProductSearch
{
    protected function resolvedCategory(): ?Category
    {
        if (! request()->filled('category')) {
            return null;
        }

        try {
            if (request()->attributes->has('_resolved_category')) {
                $cached = request()->attributes->get('_resolved_category');

                return $cached instanceof Category ? $cached : null;
            }

            $category = Category::where('slug', request('category'))->first();
            request()->attributes->set('_resolved_category', $category);

            return $category;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Search products for the request.
     *
     * @param Product $model
     * @param ProductFilter $productFilter
     *
     * @return JsonResponse
     */
    public function searchProducts(Product $model, ProductFilter $productFilter)
    {
        $payload = $this->buildListingPayload($model, $productFilter);

        if (request()->filled('fragment')) {
            $products = $payload['products'];
            $productsCollection = method_exists($products, 'getCollection') ? $products->getCollection() : $products;
            
            $productsHtml = '';
            $paginationHtml = '';
            $showingText = '';
            
            try {
                if ($productsCollection && count($productsCollection) > 0) {
                    $productsHtml = view('storefront::public.partials.products.grid', [
                        'products' => $productsCollection,
                        'productsPaginator' => $products ?? null,
                    ])->render();
                }
            } catch (\Throwable $e) {
                $productsHtml = '';
            }
            
            try {
                if (isset($products) && method_exists($products, 'total')) {
                    $total = (int) $products->total();
                    $perPage = (int) request('perPage', 20);
                    
                    if ($total > $perPage) {
                        $paginationHtml = view('storefront::public.partials.pagination')->render();
                    }
                    
                    if ($total > 0) {
                        $showingText = trans('storefront::products.showing_results', [
                            'from' => $products->firstItem(),
                            'to' => $products->lastItem(),
                            'total' => $total,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                $paginationHtml = '';
                $showingText = '';
            }
            
            return response()->json([
                'products' => $payload['products'],
                'attributes' => $payload['attributes'],
                'brands' => $payload['brands'] ?? collect(),
                'category' => $payload['category'],
                'products_html' => $productsHtml,
                'pagination_html' => $paginationHtml,
                'showing_text' => $showingText,
                'total' => (int) ($payload['total'] ?? 0),
            ]);
        }

        return response()->json($payload);
    }


    protected function buildListingPayload(Product $model, ProductFilter $productFilter): array
    {
        $productIds = [];

        if (request()->filled('query')) {
            $search = $model->search(request('query'));
            $productIds = $search->keys();

            if ($productIds->isEmpty()) {
                $query = $productFilter->apply(
                    $model->newQuery()->whereTranslationLike('name', '%' . request('query') . '%')
                );
            } else {
                $query = $search->filter($productFilter);
            }
        } elseif (request()->filled('category')) {
            $slug = request('category');
            $category = $this->resolvedCategory();

            // prioritized: if standard category exists, use standard filter logic handled by ProductFilter
            if ($category && $category->exists) {
                $query = $model->filter($productFilter);
            } else {
                // Check Dynamic Category
                $dynamicCategory = DynamicCategory::where('slug', $slug)->first();
                if ($dynamicCategory) {
                    $query = app(DynamicCategoryProductService::class)->buildQuery($dynamicCategory);
                    
                    // Temporarily remove 'category' from request parameters 
                    // to prevent QueryStringFilter from applying a failing standard category constraint
                    $originalCategory = request()->query('category');
                    request()->query->remove('category');
                    
                    $query = $productFilter->apply($query);
                    
                    // Restore 'category' for subsequent logic/view
                    request()->query->set('category', $originalCategory);
                    
                    // Update category display data for dynamic category
                    $category = (object)[
                        'exists' => true,
                        'name' => $dynamicCategory->name,
                        'slug' => $dynamicCategory->slug,
                        'description' => $dynamicCategory->description,
                        'meta_title' => $dynamicCategory->meta_title,
                        'meta_description' => $dynamicCategory->meta_description,
                        'faq_items' => [],
                        'isRoot' => false,
                    ];
                    // Cache it so resolvedCategory() might use it if called, though resolvedCategory() expects Category model
                    // We can't easily cache mismatch types, but we handled the query building manually here.
                } else {
                    $query = $model->filter($productFilter);
                }
            }
        } else {
            $query = $model->filter($productFilter);
        }

        $productIds = (clone $query)->select('products.id')->resetOrders();

        // Check if category_position is in the select
        // If yes, include it in GROUP BY to satisfy MySQL strict mode
        $selectColumns = $query->getQuery()->columns ?? [];
        $hasCategoryPosition = false;
        
        if (!empty($selectColumns)) {
            foreach ($selectColumns as $column) {
                if (is_string($column) && strpos($column, 'category_position') !== false) {
                    $hasCategoryPosition = true;
                    break;
                }
            }
        }
        
        if ($hasCategoryPosition) {
            $query->groupBy('products.id', 'category_position');
        } else {
            $query->groupBy('products.id');
        }

        $perPage = (int) request('perPage', 20);
        $page = max(1, (int) request('page', 1));

        $listingQuery = clone $query;

        // Optimization: Reset and re-apply only necessary eager loads
        $listingQuery->setEagerLoads([]);
        
        $listingQuery->with([
            'files' => function ($q) {
                $q->wherePivotIn('zone', ['base_image', 'additional_images']);
            },
            'saleUnit',
            'productMedia' => function ($q) {
                $q->where('is_active', true)
                    ->where('type', 'video')
                    ->orderBy('position')
                    ->select(['id', 'product_id', 'variant_id', 'type', 'path', 'poster', 'position', 'is_active']);
            },
            'variants' => function ($q) {
                $q->where('is_active', true)
                    ->orderBy('position')
                    ->select([
                        'id',
                        'product_id',
                        'uid',
                        'name',
                        'price',
                        'special_price',
                        'special_price_type',
                        'special_price_start',
                        'special_price_end',
                        'selling_price',
                        'manage_stock',
                        'qty',
                        'in_stock',
                        'is_active',
                        'is_default',
                    ])
                    ->with([
                        'files' => function ($qf) {
                            $qf->wherePivotIn('zone', ['base_image', 'additional_images']);
                        },
                    ]);
            },
            'variations' => function ($q) {
                $q->without(['values'])
                    ->select(['variations.id', 'variations.uid', 'variations.type', 'variations.is_global', 'variations.position'])
                    ->with(['translations:id,variation_id,locale,name']);
            },
            'tags' => function ($q) {
                $q->select(['tags.id']);
            },
        ]);

        $paginator = $listingQuery->paginate($perPage, ['*'], 'page', $page);
        $products = $paginator->getCollection();

        try {
            $first = $products->first();

            $baseImage = $first ? ($first->base_image ?? null) : null;

            if ($baseImage && isset($baseImage->id)) {
                $fastWebp = data_get($first, 'base_image.fast_webp_url');
                $fastAvif = data_get($first, 'base_image.fast_avif_url');

                if (!$fastWebp && !$fastAvif) {
                    GenerateResponsiveImagesForMedia::dispatch((int) $baseImage->id)->afterResponse();
                }
            }
        } catch (\Throwable $e) {
        }

        $allTagIds = $products
            ->flatMap(function (Product $p) {
                return $p->relationLoaded('tags') ? $p->tags->pluck('id') : collect();
            })
            ->filter()
            ->unique()
            ->values();

        $badgesByTagId = collect();

        if ($allTagIds->isNotEmpty()) {
            $badgesByTagId = TagBadge::query()
                ->active()
                ->where('show_on_listing', true)
                ->whereIn('tag_id', $allTagIds)
                ->orderByDesc('priority')
                ->get()
                ->groupBy('tag_id');
        }

        $paginator->setCollection(
            $products->flatMap(function (Product $product) use ($badgesByTagId) {
                $tagIds = $product->relationLoaded('tags') ? $product->tags->pluck('id')->all() : [];

                $tagBadges = collect($tagIds)
                    ->flatMap(function ($tagId) use ($badgesByTagId) {
                        return $badgesByTagId->get($tagId, collect());
                    })
                    ->unique('id')
                    ->map(function ($badge) {
                        return [
                            'name' => $badge->name,
                            'image_url' => $badge->image_url,
                            'listing_position' => $badge->listing_position,
                            'detail_position' => $badge->detail_position,
                            'priority' => $badge->priority,
                        ];
                    })
                    ->values();

                $avg = (float) ($product->reviews_avg_rating ?? 0);
                $ratingPercent = $avg > 0 ? ($avg / 5) * 100 : 0;

                $variantLabel = optional($product->variations->first())->name;

                if ((bool) $product->list_variants_separately) {
                    $variants = $product->relationLoaded('variants') ? $product->variants : collect();
                    $actives = $variants->filter(function ($v) {
                        return (bool) ($v->is_active ?? false);
                    });

                    if ($actives->isNotEmpty()) {
                        return $actives->map(function ($variant) use ($product, $tagBadges, $variantLabel, $ratingPercent) {
                            $variantImage = ($variant->base_image && ($variant->base_image->id ?? null)) ? $variant->base_image : null;
                            $productImage = ($product->base_image && ($product->base_image->id ?? null)) ? $product->base_image : null;
                            $image = $variantImage ?: ($productImage ?: $product->base_image);

                            $p = $product->clean();
                            $p['list_variants_separately'] = true;
                            $p['variant_attribute_label'] = $variantLabel;
                            $p['name'] = $product->name;
                            $p['listing_key'] = 'p' . (int) $product->id . '-v' . (int) $variant->id;
                            $p['variant'] = $variant->clean();
                            
                            $p['url'] = $variant->url() ?? $product->url();
                            
                            $p['base_image'] = $image;
                            $p['base_image_thumb'] = [
                                'path' => media_variant_url(
                                    $image,
                                    (int) config('image_optimization.variants.widths.grid', 400)
                                )
                            ];
                            $p['variant']['base_image_thumb'] = [
                                'path' => media_variant_url(
                                    $image,
                                    (int) config('image_optimization.variants.widths.thumb', 80)
                                )
                            ];
                            $p['formatted_price'] = $variant->formatted_price ?? $product->formatted_price;
                            $p['formatted_price_range'] = null;
                            $p['additional_images'] = $product->additional_images;
                            $p['videos'] = $this->mapListingVideos($product);
                            $p['tag_badges'] = $tagBadges;
                            $p['rating_percent'] = $ratingPercent;
                            $p['variants'] = [];
                            $p['variations'] = $product->variations->map(function ($v) {
                                return [
                                    'name' => $v->name,
                                    'uid' => $v->uid,
                                ];
                            });
                            return $p;
                        });
                    }
                }

                $base = $product->clean();
                $defaultVariant = $product->variant;
                $base['variant_attribute_label'] = $variantLabel;
                $base['listing_key'] = 'p' . (int) $product->id;
                $base['formatted_price'] = $defaultVariant ? $defaultVariant->formatted_price : $product->formatted_price;
                $base['url'] = $defaultVariant ? ($defaultVariant->url() ?? $product->url()) : $product->url();
                $base['base_image_thumb'] = [
                    'path' => media_variant_url(
                        $product->base_image,
                        (int) config('image_optimization.variants.widths.grid', 400)
                    )
                ];
                $base['additional_images'] = $product->additional_images;
                $base['videos'] = $this->mapListingVideos($product);
                $base['tag_badges'] = $tagBadges;
                $base['rating_percent'] = $ratingPercent;
                $base['reviews_count'] = $product->reviews_count ?? ($product->relationLoaded('reviews') ? $product->reviews->count() : 0);
                
                $base['variant'] = $defaultVariant ? $defaultVariant->clean() : null;

                $base['variants'] = $product->variants->map(function ($v) {
                    $vArr = $v->clean();
                    $vArr['base_image_thumb'] = [
                        'path' => media_variant_url($v->base_image, 80)
                    ];
                    return $vArr;
                });

                $base['variations'] = $product->variations->map(function ($v) {
                    return [
                        'name' => $v->name,
                        'uid' => $v->uid,
                    ];
                });

                return collect([$base]);
            })
        );

        event(new ShowingProductList($paginator));

        $categoryData = [
            'name' => null,
            'slug' => null,
            'description_html' => '',
            'meta_title' => '',
            'meta_description' => '',
            'faq_items' => [],
        ];

        if (request()->filled('category')) {
            $category = $this->resolvedCategory();

            if ($category && $category->exists) {
                $faqItems = is_array($category->faq_items) ? $category->faq_items : [];

                $categoryData['name'] = $category->name;
                $categoryData['slug'] = $category->slug;
                $categoryData['description_html'] = $category->description ?? '';
                $categoryData['meta_title'] = $category->meta_title ?? '';
                $categoryData['meta_description'] = $category->meta_description ?? '';
                $categoryData['faq_items'] = $faqItems;
            } elseif (isset($dynamicCategory) && $dynamicCategory) {
                $categoryData['name'] = $dynamicCategory->name;
                $categoryData['slug'] = $dynamicCategory->slug;
                $categoryData['description_html'] = $dynamicCategory->description ?? '';
                $categoryData['meta_title'] = $dynamicCategory->meta_title ?? '';
                $categoryData['meta_description'] = $dynamicCategory->meta_description ?? '';
                $categoryData['faq_items'] = [];
            }
        } elseif (request()->filled('query')) {
             // Leave empty for search results, or populate "Search Result" title if desired.
             // Usually search handles its own title in frontend or different view.
        } else {
            // Main category for /products page - fallback for ANY filter combination 
            // if not explicit category or search query
            $categoryData['name'] = setting('products_page_name', trans('storefront::products.shop'));
            $categoryData['slug'] = setting('products_page_slug', 'products');
            $categoryData['description_html'] = setting('products_page_description', '');
            $categoryData['meta_title'] = setting('products_page_meta_title', '');
            $categoryData['meta_description'] = setting('products_page_meta_description', '');
            $categoryData['faq_items'] = json_decode(setting('products_page_faq_items', '[]'), true) ?: [];
        }

        $baseFacetProductIds = $this->getBaseFacetProductIds($model);

        $collection = $paginator->getCollection();
        
        // If we are on the first page and the total is small, 
        // the collection count is the most accurate total.
        if ($paginator->currentPage() === 1 && $paginator->total() <= $paginator->perPage()) {
            $total = $collection->count();
        } else {
            // Otherwise, we use the paginator's total (product count).
            $total = (int) $paginator->total();
        }

        return [
            'products' => $paginator,
            'attributes' => $this->getAttributes($baseFacetProductIds),
            'brands' => $this->getBrands($baseFacetProductIds),
            'category' => $categoryData,
            'total' => $total,
        ];
    }


    /**
     * Get product IDs for facets (filters) based only on core context (category/search/tag).
     * This ensures filters don't disappear when one is selected.
     */
    protected function getBaseFacetProductIds(Product $model)
    {
        $categorySlug = request('category');
        
        // If it's a dynamic category, use its specific builder for facets
        if ($categorySlug) {
            $dynamicCategory = \Modules\DynamicCategory\Entities\DynamicCategory::where('slug', $categorySlug)->first();
            if ($dynamicCategory) {
                return app(\Modules\DynamicCategory\Services\DynamicCategoryProductService::class)
                    ->buildQuery($dynamicCategory)
                    ->select('products.id');
            }
        }

        // Use forCard() to ensure proper joins and selects
        $query = $model->forCard();

        if (request()->filled('query')) {
            $query->whereTranslationLike('name', '%' . request('query') . '%');
        }

        if ($categorySlug) {
            app(\Modules\Product\Filters\QueryStringFilter::class)->category($query, $categorySlug);
        }

        if (request()->filled('tag')) {
            app(\Modules\Product\Filters\QueryStringFilter::class)->tag($query, request('tag'));
        }

        return $query->select('products.id');
    }


    protected function mapListingVideos(Product $product): array
    {
        try {
            $videos = $product->relationLoaded('productMedia') ? $product->productMedia : collect();

            return $videos
                ->where('is_active', true)
                ->where('type', 'video')
                ->sortBy('position')
                ->values()
                ->map(function ($m) use ($product) {
                    $variantBase = optional($product->variant)->base_image?->path;
                    $poster = $m->poster ?: ($variantBase ?: ($product->base_image?->path ?? asset('build/assets/image-placeholder.png')));
                    return [
                        'variant_id' => $m->variant_id,
                        'src' => $m->path,
                        'thumb' => $poster,
                    ];
                })
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }


    private function getAttributes($productIds)
    {
        if (!$productIds) {
            return collect();
        }

        try {
            $attributeIds = DB::table('product_attributes')
                ->distinct()
                ->whereIn('product_id', $productIds)
                ->pluck('attribute_id')
                ->all();

            if (empty($attributeIds)) {
                return collect();
            }

            $attributes = Attribute::with(['values' => function ($query) use ($productIds) {
                $query->whereExists(function ($q) use ($productIds) {
                    $q->select(DB::raw(1))
                        ->from('product_attribute_values')
                        ->join('product_attributes', 'product_attribute_values.product_attribute_id', '=', 'product_attributes.id')
                        ->whereRaw('product_attribute_values.attribute_value_id = attribute_values.id')
                        ->whereIn('product_attributes.product_id', $productIds);
                })->orderBy('position');
            }])
                ->where('is_filterable', true)
                ->whereIn('id', $attributeIds)
                ->get();

            return $attributes->map(function ($attribute) use ($productIds) {
                if ($attribute->filterable_type === 'range') {
                    $minMax = DB::table('product_attribute_values')
                        ->join('product_attributes', 'product_attribute_values.product_attribute_id', '=', 'product_attributes.id')
                        ->join('attribute_values', 'product_attribute_values.attribute_value_id', '=', 'attribute_values.id')
                        ->join('attribute_value_translations', 'attribute_values.id', '=', 'attribute_value_translations.attribute_value_id')
                        ->where('product_attributes.attribute_id', $attribute->id)
                        ->whereIn('product_attributes.product_id', $productIds)
                        ->selectRaw('MIN(CAST(attribute_value_translations.value AS DECIMAL(10,2))) as min')
                        ->selectRaw('MAX(CAST(attribute_value_translations.value AS DECIMAL(10,2))) as max')
                        ->first();

                    $attribute->min = (int) ($minMax->min ?? 0);
                    $attribute->max = (int) ($minMax->max ?? 0);
                }

                $attribute->setRelation('values', $attribute->values->values());

                return $attribute;
            });
        } catch (\Throwable $e) {
            \Log::error('getAttributes error: ' . $e->getMessage());
            return collect();
        }
    }


    private function filteringViaRootCategory()
    {
        $category = $this->resolvedCategory();

        return ($category && $category->exists)
            ? $category->isRoot()
            : Category::firstOrNew([])->isRoot();
    }


    private function getProductsCategoryIds($productIds)
    {
        return DB::table('product_categories')
            ->whereIn('product_id', $productIds)
            ->select('category_id')
            ->distinct();
    }


    private function getBrands($productIds)
    {
        if (!$productIds) {
            return collect();
        }

        try {
            // Direct approach: get brand IDs from products table
            $query = DB::table('products')
                ->whereNotNull('brand_id')
                ->distinct()
                ->select('brand_id');

            $query->whereIn('id', $productIds);

            $brandIds = $query->pluck('brand_id')->all();

            if (empty($brandIds)) {
                return collect();
            }

            $brands = \Modules\Brand\Entities\Brand::withoutGlobalScope('active')
                ->with('translations')
                ->whereIn('id', $brandIds)
                ->where('is_active', true)
                ->get()
                ->sortBy('name'); // Sort in memory after translations are loaded

            return $brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'slug' => $brand->slug,
                    'name' => $brand->name,
                ];
            })->values();
        } catch (\Throwable $e) {
            \Log::error('getBrands error: ' . $e->getMessage());
            return collect();
        }
    }


    private function buildFaqHtml(array $faqList): string
    {
        if (empty($faqList)) {
            return '';
        }

        $html = '';

        foreach ($faqList as $item) {
            $q = isset($item['q']) ? e($item['q']) : '';
            $a = isset($item['a']) ? (string) $item['a'] : '';

            if ($q === '' && trim($a) === '') {
                continue;
            }

            $html .= '<div class="faq-item">';

            if ($q !== '') {
                $html .= '<h3 class="faq-question">' . $q . '</h3>';
            }

            if (trim($a) !== '') {
                $html .= '<div class="faq-answer">' . $a . '</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }
}
