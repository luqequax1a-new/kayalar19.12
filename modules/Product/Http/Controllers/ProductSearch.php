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

trait ProductSearch
{
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
                )->forCard();
            } else {
                $query = $search->filter($productFilter)->forCard();
            }
        } else {
            $query = $model->filter($productFilter)->forCard();
        }

        if (request()->filled('category')) {
            $productIds = (clone $query)->select('products.id')->resetOrders();
        }

        $perPage = (int) request('perPage', 20);
        $page = max(1, (int) request('page', 1));

        $listingQuery = clone $query;

        $eagerLoads = method_exists($listingQuery, 'getEagerLoads') ? $listingQuery->getEagerLoads() : [];
        unset($eagerLoads['reviews']);
        $listingQuery->setEagerLoads($eagerLoads);

        $listingQuery->withAvg('reviews', 'rating');

        $listingQuery->with([
            'files' => function ($q) {
                $q->wherePivotIn('zone', ['base_image', 'additional_images']);
            },
            'productMedia' => function ($q) {
                $q->where('is_active', true)
                    ->where('type', 'video')
                    ->orderBy('position')
                    ->select(['id', 'product_id', 'variant_id', 'type', 'path', 'poster', 'position', 'is_active']);
            },
            'variants' => function ($q) {
                $q->where('is_active', true)
                    ->orderBy('position')
                    ->addSelect([
                        'id',
                        'product_id',
                        'uid',
                        'name',
                        'position',
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
                            $p['variant_attribute_label'] = $variantLabel;
                            $p['name'] = $product->name;
                            $p['listing_key'] = 'p' . (int) $product->id . '-v' . (int) $variant->id;
                            $p['variant'] = $variant->toArray();
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
                            return $p;
                        });
                    }
                }

                $base = $product->clean();
                $base['variant_attribute_label'] = $variantLabel;
                $base['listing_key'] = 'p' . (int) $product->id;
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

                return collect([$base]);
            })
        );

        event(new ShowingProductList($paginator));

        $categoryData = [
            'name' => null,
            'slug' => null,
            'description_html' => '',
            'faq_items' => [],
        ];

        if (request()->filled('category')) {
            $category = Category::where('slug', request('category'))->first();

            if ($category && $category->exists) {
                $faqItems = is_array($category->faq_items) ? $category->faq_items : [];

                $categoryData['name'] = $category->name;
                $categoryData['slug'] = $category->slug;
                $categoryData['description_html'] = $category->description ?? '';
                $categoryData['faq_items'] = $faqItems;
            }
        }

        return [
            'products' => $paginator,
            'attributes' => $this->getAttributes($productIds),
            'category' => $categoryData,
        ];
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
        if (!request()->filled('category') || $this->filteringViaRootCategory()) {
            return collect();
        }

        return Attribute::with('values')
            ->where('is_filterable', true)
            ->whereHas('categories', function ($query) use ($productIds) {
                $query->whereIn('id', $this->getProductsCategoryIds($productIds));
            })
            ->get();
    }


    private function filteringViaRootCategory()
    {
        return Category::where('slug', request('category'))
            ->firstOrNew([])
            ->isRoot();
    }


    private function getProductsCategoryIds($productIds)
    {
        // $productIds can be an array/collection OR a subquery builder.
        // DB::table()->whereIn supports subquery builders.
        return DB::table('product_categories')
            ->whereIn('product_id', $productIds)
            ->distinct()
            ->pluck('category_id');
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
