<?php

namespace Modules\Category\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Product\Filters\ProductFilter;
use Modules\Product\Http\Controllers\ProductSearch;
use Modules\DynamicCategory\Entities\DynamicCategory;
use Modules\DynamicCategory\Services\DynamicCategoryProductService;
use Modules\Product\Events\ShowingProductList;

class CategoryProductController
{
    use ProductSearch;

    private DynamicCategoryProductService $dynamicCategoryProductService;

    public function __construct(DynamicCategoryProductService $dynamicCategoryProductService)
    {
        $this->dynamicCategoryProductService = $dynamicCategoryProductService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param string $slug
     * @param Product $model
     * @param ProductFilter $productFilter
     *
     * @return Response
     */
    public function index($slug, Product $model, ProductFilter $productFilter)
    {
        request()->merge(['category' => $slug]);

        // Önce dinamik kategoriye bak (aynı slug varsa dinamik kategori öncelikli olsun)
        $dynamicCategory = DynamicCategory::where('slug', $slug)->first();

        if ($dynamicCategory) {
            $service = $this->dynamicCategoryProductService;

            if (request()->expectsJson()) {
                // Build base query using tag rules only (no price/brand/date filters)
                $query = $service->buildQuery($dynamicCategory);

                $perPage = (int) request('perPage', 30);
                $page = max(1, (int) request('page', 1));

                // 1) Use database pagination on the base query first
                $paginator = $query->paginate($perPage, ['*'], 'page', $page);
                $all = $paginator->getCollection();

                // 2) Eager load necessary relations for the current page only
                $all->load([
                    'variants' => function ($q) {
                        $q->where('is_active', true)->orderBy('position')->with(['files']);
                    },
                    'variations',
                    'tags',
                    'files' => function ($q) {
                        $q->wherePivotIn('zone', ['base_image', 'additional_images']);
                    },
                    'reviews' => function ($q) {
                        $q->select('id', 'product_id', 'rating', 'is_approved')->where('is_approved', true);
                    },
                ]);

                // 3) Calculate tag badges in bulk for this page to avoid N+1
                $allTagIds = $all->flatMap(fn($p) => $p->tags->pluck('id'))->unique()->filter()->values();
                $badgesByTagId = collect();
                if ($allTagIds->isNotEmpty()) {
                    $badgesByTagId = \Modules\Tag\Entities\TagBadge::query()
                        ->active()
                        ->where('show_on_listing', true)
                        ->whereIn('tag_id', $allTagIds)
                        ->orderByDesc('priority')
                        ->get()
                        ->groupBy('tag_id');
                }

                $items = $all->flatMap(function (Product $product) use ($badgesByTagId) {
                    $tagIds = $product->tags->pluck('id')->all();
                    $tagBadges = collect($tagIds)
                        ->flatMap(fn($id) => $badgesByTagId->get($id, collect()))
                        ->unique('id')
                        ->map(fn($b) => [
                            'name' => $b->name,
                            'image_url' => $b->image_url,
                            'listing_position' => $b->listing_position,
                            'detail_position' => $b->detail_position,
                            'priority' => $b->priority,
                        ])->values();

                    $variantLabel = optional($product->variations->first())->name;
                    if ($product->list_variants_separately) {
                        $actives = $product->variants;

                        if ($actives->isNotEmpty()) {
                            return $actives->map(function ($variant) use ($product, $tagBadges, $variantLabel) {
                                $p = $product->clean();
                                $p['list_variants_separately'] = true;
                                $p['variant_attribute_label'] = $variantLabel;
                                $p['name'] = $product->name;
                                $p['listing_key'] = 'p' . (int) $product->id . '-v' . (int) $variant->id;
                                $p['variant'] = $variant->clean();
                                $slug = (string) ($product->slug ?? '');
                                $uid = (string) ($variant->uid ?? '');
                                // İkas-style clean URL
                                $p['url'] = $slug !== ''
                                    ? url('/' . $slug) . ($uid !== '' ? ('?variant=' . $uid) : '')
                                    : $product->url();
                                
                                $image = ($variant->base_image && $variant->base_image->id) ? $variant->base_image : $product->base_image;
                                $p['base_image'] = $image;
                                $p['base_image_thumb'] = [
                                    'path' => media_variant_url($image, (int) config('image_optimization.variants.widths.grid', 400))
                                ];
                                $p['variant']['base_image_thumb'] = [
                                    'path' => media_variant_url($image, (int) config('image_optimization.variants.widths.thumb', 80))
                                ];
                                $p['formatted_price'] = $variant->formatted_price ?? $product->formatted_price;
                                $p['formatted_price_range'] = null;
                                $p['reviews_count'] = $product->reviews_count ?? $product->reviews->count();
                                $p['rating_percent'] = $product->rating_percent;
                                $p['tag_badges'] = $tagBadges;
                                return $p;
                            });
                        }
                    }

                    $base = $product->clean();
                    $base['variant_attribute_label'] = $variantLabel;
                    $base['listing_key'] = 'p' . (int) $product->id;
                    $base['reviews_count'] = $product->reviews_count ?? $product->reviews->count();
                    $base['rating_percent'] = $product->rating_percent;
                    $base['base_image_thumb'] = [
                        'path' => media_variant_url($product->base_image, (int) config('image_optimization.variants.widths.grid', 400))
                    ];
                    $base['tag_badges'] = $tagBadges;

                    return collect([$base]);
                })->values();

                // 4) Update the paginator's collection with processed items
                // Note: The total count remains the same (product count), which is slightly inaccurate if 
                // list_variants_separately is used, but it's the only way to keep DB pagination performant.
                $paginator->setCollection($items);

                event(new ShowingProductList($paginator));

                return response()->json([
                    'products' => $paginator,
                    'attributes' => collect(), // no extra attribute filters for dynamic categories
                ]);
            }

            // Frontend product index view expects a $category object to read
            // description / FAQ data from. We provide a lightweight object
            // with the same shape for dynamic categories so that the rich
            // text description can be rendered below the product list.

            $perPage = (int) request('perPage', 30);
            $page = max(1, (int) request('page', 1));

            $paginator = $service->buildQuery($dynamicCategory)->paginate($perPage, ['*'], 'page', $page);
            $all = $paginator->getCollection();

            $all->load([
                'variants' => function ($q) {
                    $q->where('is_active', true)->orderBy('position')->with(['files']);
                },
                'variations',
                'tags',
                'files' => function ($q) {
                    $q->wherePivotIn('zone', ['base_image', 'additional_images']);
                },
                'reviews' => function ($q) {
                    $q->select('id', 'product_id', 'rating', 'is_approved')->where('is_approved', true);
                },
            ]);

            $allTagIds = $all->flatMap(fn($p) => $p->tags->pluck('id'))->unique()->filter()->values();
            $badgesByTagId = collect();
            if ($allTagIds->isNotEmpty()) {
                $badgesByTagId = \Modules\Tag\Entities\TagBadge::query()
                    ->active()
                    ->where('show_on_listing', true)
                    ->whereIn('tag_id', $allTagIds)
                    ->orderByDesc('priority')
                    ->get()
                    ->groupBy('tag_id');
            }

            $items = $all->flatMap(function (Product $product) use ($badgesByTagId) {
                $tagIds = $product->tags->pluck('id')->all();
                $tagBadges = collect($tagIds)
                    ->flatMap(fn($id) => $badgesByTagId->get($id, collect()))
                    ->unique('id')
                    ->map(fn($b) => [
                        'name' => $b->name,
                        'image_url' => $b->image_url,
                        'listing_position' => $b->listing_position,
                        'detail_position' => $b->detail_position,
                        'priority' => $b->priority,
                    ])->values();

                $variantLabel = optional($product->variations->first())->name;
                if ($product->list_variants_separately) {
                    $actives = $product->variants;

                    if ($actives->isNotEmpty()) {
                        return $actives->map(function ($variant) use ($product, $tagBadges, $variantLabel) {
                            $p = $product->clean();
                            $p['list_variants_separately'] = true;
                            $p['variant_attribute_label'] = $variantLabel;
                            $p['name'] = $product->name;
                            $p['listing_key'] = 'p' . (int) $product->id . '-v' . (int) $variant->id;
                            $p['variant'] = $variant->clean();
                            $slug = (string) ($product->slug ?? '');
                            $uid = (string) ($variant->uid ?? '');
                            $p['url'] = $slug !== '' ? url('/' . $slug) . ($uid !== '' ? ('?variant=' . $uid) : '') : $product->url();
                            
                            $image = ($variant->base_image && $variant->base_image->id) ? $variant->base_image : $product->base_image;
                            $p['base_image'] = $image;
                            $p['base_image_thumb'] = [
                                'path' => media_variant_url($image, (int) config('image_optimization.variants.widths.grid', 400))
                            ];
                            $p['variant']['base_image_thumb'] = [
                                'path' => media_variant_url($image, (int) config('image_optimization.variants.widths.thumb', 80))
                            ];
                            $p['formatted_price'] = $variant->formatted_price ?? $product->formatted_price;
                            $p['formatted_price_range'] = null;
                            $p['reviews_count'] = $product->reviews_count ?? $product->reviews->count();
                            $p['rating_percent'] = $product->rating_percent;
                            $p['tag_badges'] = $tagBadges;
                            return $p;
                        });
                    }
                }

                $base = $product->clean();
                $base['variant_attribute_label'] = $variantLabel;
                $base['listing_key'] = 'p' . (int) $product->id;
                $base['reviews_count'] = $product->reviews_count ?? $product->reviews->count();
                $base['rating_percent'] = $product->rating_percent;
                $base['base_image_thumb'] = [
                    'path' => media_variant_url($product->base_image, (int) config('image_optimization.variants.widths.grid', 400))
                ];
                $base['tag_badges'] = $tagBadges;

                return collect([$base]);
            })->values();

            $paginator->setCollection($items);

            $categoryLike = (object) [
                'description' => $dynamicCategory->description,
                'faq_items' => [],
            ];

            return view('storefront::public.products.index', [
                'category' => $categoryLike,
                'categoryName' => $dynamicCategory->name,
                'categoryBanner' => optional($dynamicCategory->image)->path,
                'categoryMetaTitle' => $dynamicCategory->meta_title ?: $dynamicCategory->name,
                'categoryMetaDescription' => $dynamicCategory->meta_description,
                'initialProducts' => $paginator,
                'initialAttributes' => collect(),
                'initialCategoryData' => [
                    'description' => $dynamicCategory->description,
                    'faq_items' => [],
                ],
            ]);
        }

        // Dinamik kategori yoksa normal kategori akışına düş
        $category = Category::with(['files', 'translations'])->where('slug', $slug)->firstOrNew([]);

        // Normal kategori bulunduysa, mevcut davranışa devam et
        if ($category->exists) {
            if (request()->expectsJson()) {
                return $this->searchProducts($model, $productFilter);
            }

            $payload = $this->buildListingPayload($model, $productFilter);

            return view('storefront::public.products.index', [
                'category' => $category,
                'categoryName' => $category->name,
                'categoryBanner' => $category->banner->path,
                'categoryMetaTitle' => $category->meta_title,
                'categoryMetaDescription' => $category->meta_description,
                'initialProducts' => $payload['products'] ?? null,
                'initialAttributes' => $payload['attributes'] ?? null,
                'initialCategoryData' => $payload['category'] ?? null,
            ]);
        }

        // Fallback: önceki davranışla uyumlu kal, bilinmeyen slug için boş liste sayfası göster
        if (request()->expectsJson()) {
            return $this->searchProducts($model, $productFilter);
        }

        return view('storefront::public.products.index', [
            'initialProducts' => null,
            'initialAttributes' => null,
            'initialCategoryData' => null,
        ]);
    }
}
