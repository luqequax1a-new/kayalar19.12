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

                $all = $query->get();
                $all->load([
                    'variants' => function ($q) {
                        $q->orderBy('position');
                    },
                    'variations',
                    'tags',
                    'tags.tagBadges' => function ($q) {
                        $q->active();
                    },
                ]);

                $items = $all->flatMap(function (Product $product) {
                    $tagBadges = $product->badgeVisualsFor('listing')->map(function ($badge) {
                        return [
                            'name' => $badge->name,
                            'image_url' => $badge->image_url,
                            'listing_position' => $badge->listing_position,
                            'detail_position' => $badge->detail_position,
                            'priority' => $badge->priority,
                        ];
                    })->values();
                    $variantLabel = optional($product->variations->first())->name;
                    if ($product->list_variants_separately) {
                        $variants = $product->variants()->orderBy('position')->get();
                        $actives = $variants->filter(function ($v) {
                            return (bool) ($v->is_active ?? false);
                        });

                        if ($actives->isNotEmpty()) {
                            return $actives->map(function ($variant) use ($product, $tagBadges, $variantLabel) {
                                $p = $product->clean();
                                $p['variant_attribute_label'] = $variantLabel;
                                $p['name'] = $product->name;
                                $p['listing_key'] = 'p' . (int) $product->id . '-v' . (int) $variant->id;
                                $p['variant'] = $variant->toArray();
                                $slug = (string) ($product->slug ?? '');
                                $uid = (string) ($variant->uid ?? '');
                                $p['url'] = $slug !== ''
                                    ? url('/products/' . $slug) . ($uid !== '' ? ('?variant=' . $uid) : '')
                                    : $product->url();
                                $p['base_image'] = ($variant->base_image ?? $product->base_image);
                                $p['base_image_thumb'] = [
                                    'path' => media_variant_url(
                                        ($variant->base_image ?? $product->base_image),
                                        (int) config('image_optimization.variants.widths.grid', 400)
                                    )
                                ];
                                $p['variant']['base_image_thumb'] = [
                                    'path' => media_variant_url(
                                        ($variant->base_image ?? $product->base_image),
                                        (int) config('image_optimization.variants.widths.thumb', 80)
                                    )
                                ];
                                $p['formatted_price'] = $variant->formatted_price ?? $product->formatted_price;
                                $p['formatted_price_range'] = null;
                                $p['reviews_count'] = $product->reviews_count ?? ($product->relationLoaded('reviews') ? $product->reviews->count() : 0);
                                $p['rating_percent'] = $product->rating_percent;
                                $p['tag_badges'] = $tagBadges;
                                return $p;
                            });
                        }
                    }

                    $base = $product->clean();
                    $base['variant_attribute_label'] = $variantLabel;
                    $base['listing_key'] = 'p' . (int) $product->id;
                    $base['reviews_count'] = $product->reviews_count ?? ($product->relationLoaded('reviews') ? $product->reviews->count() : 0);
                    $base['rating_percent'] = $product->rating_percent;
                    $base['base_image_thumb'] = [
                        'path' => media_variant_url(
                            $product->base_image,
                            (int) config('image_optimization.variants.widths.grid', 400)
                        )
                    ];
                    $base['tag_badges'] = $tagBadges;

                    return collect([$base]);
                })->values();

                $total = $items->count();
                $sliced = $items->forPage($page, $perPage)->values();
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $sliced,
                    $total,
                    $perPage,
                    $page,
                    [
                        'path' => request()->url(),
                        'query' => request()->query(),
                    ]
                );

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
                'initialProducts' => null,
                'initialAttributes' => null,
                'initialCategoryData' => null,
            ]);
        }

        // Dinamik kategori yoksa normal kategori akışına düş
        $category = Category::findBySlug($slug);

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
