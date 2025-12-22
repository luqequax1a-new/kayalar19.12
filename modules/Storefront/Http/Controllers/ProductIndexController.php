<?php

namespace Modules\Storefront\Http\Controllers;

use Illuminate\Support\Collection;
use Modules\Product\RecentlyViewed;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;

class ProductIndexController
{
    private $recentlyViewed;
    protected string $variantsMode = 'inherit';


    public function __construct(RecentlyViewed $recentlyViewed)
    {
        $this->recentlyViewed = $recentlyViewed;
    }


    protected function getProducts($settingPrefix)
    {
        $this->variantsMode = setting("{$settingPrefix}_variants_mode") ?: 'inherit';
        $type = setting("{$settingPrefix}_product_type", 'custom_products');
        $limit = setting("{$settingPrefix}_products_limit");

        if ($type === 'all_products') {
            return Product::forCard()
                ->with([
                    'variants',
                    'variations',
                    'tags',
                    'tags.tagBadges' => function ($query) {
                        $query->active();
                    },
                ])
                ->when(!is_null($limit), function ($q) use ($limit) {
                    $q->limit($limit);
                })
                ->get()
                ->flatMap(function (Product $product) {
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

                    $shouldListSeparately = (bool) $product->list_variants_separately;
                    if ($this->variantsMode === 'force_on') {
                        $shouldListSeparately = true;
                    } elseif ($this->variantsMode === 'force_off') {
                        $shouldListSeparately = false;
                    }

                    if ($shouldListSeparately) {
                        $variants = $product->variants()->orderBy('position')->get();
                        $actives = $variants->filter(function ($v) {
                            return (bool) ($v->is_active ?? false);
                        });

                        if ($actives->isNotEmpty()) {
                            return $actives->map(function ($variant) use ($product, $tagBadges, $variantLabel) {
                                $p = $product->clean();
                                $p['variant_attribute_label'] = $variantLabel;
                                $p['name'] = $product->name;
                                $p['list_variants_separately'] = true;
                                $p['listing_key'] = 'p' . (int) $product->id . '-v' . (int) ($variant->id ?? 0);
                                $p['variant'] = $variant->toArray();
                                $p['url'] = $variant->url() ?? $product->url();
                                $p['base_image'] = ($variant->base_image ?? $product->base_image);
                                $p['formatted_price'] = $variant->formatted_price ?? $product->formatted_price;
                                $p['formatted_price_range'] = null;
                                $p['tag_badges'] = $tagBadges;
                                return $p;
                            });
                        }
                    }

                    $base = $product->clean();
                    $base['variant_attribute_label'] = $variantLabel;
                    $base['listing_key'] = 'p' . (int) $product->id;
                    if ($this->variantsMode === 'force_off') {
                        $base['list_variants_separately'] = false;
                    } elseif ($this->variantsMode === 'force_on') {
                        $base['list_variants_separately'] = true;
                    }
                    $base['variants'] = $product->variants()
                        ->where('is_active', true)
                        ->orderBy('position')
                        ->take(10)
                        ->get()
                        ->toArray();
                    $base['tag_badges'] = $tagBadges;

                    return collect([$base]);
                });
        }

        if ($type === 'category_products') {
            return $this->categoryProducts($settingPrefix, $limit);
        }

        if ($type === 'tag_products') {
            return $this->tagProducts($settingPrefix, $limit);
        }

        if ($type === 'recently_viewed_products') {
            return $this->recentlyViewedProducts($limit);
        }

        return Product::forCard()
            ->with([
                'variants',
                'variations',
                'tags',
                'tags.tagBadges' => function ($query) {
                    $query->active();
                },
            ])
            ->when($type === 'latest_products', $this->latestProductsCallback($limit))
            ->when($type === 'custom_products', $this->customProductsCallback($settingPrefix))
            ->get()
            ->flatMap(function (Product $product) {
                $tagBadges = $product->badgeVisualsFor('listing')->map(function ($badge) {
                    return [
                        'name' => $badge->name,
                        'image_url' => $badge->image_url,
                        'listing_position' => $badge->listing_position,
                        'detail_position' => $badge->detail_position,
                        'priority' => $badge->priority,
                    ];
                })->values();
                // Grid badge label: use first variation name (e.g. Renk, Beden)
                $variantLabel = optional($product->variations->first())->name;
                $shouldListSeparately = (bool) $product->list_variants_separately;
                if ($this->variantsMode === 'force_on') {
                    $shouldListSeparately = true;
                } elseif ($this->variantsMode === 'force_off') {
                    $shouldListSeparately = false;
                }

                if ($shouldListSeparately) {
                    $variants = $product->variants()->orderBy('position')->get();
                    $actives = $variants->filter(function ($v) {
                        return (bool) ($v->is_active ?? false);
                    });

                    if ($actives->isNotEmpty()) {
                        return $actives->map(function ($variant) use ($product, $tagBadges, $variantLabel) {
                            $variantImage = ($variant->base_image && ($variant->base_image->id ?? null)) ? $variant->base_image : null;
                            $productImage = ($product->base_image && ($product->base_image->id ?? null)) ? $product->base_image : null;
                            $image = $variantImage ?: ($productImage ?: $product->base_image);

                            $p = $product->clean();
                            $p['variant_attribute_label'] = $variantLabel;
                            // Keep base product name; frontend combines with variant name once
                            $p['name'] = $product->name;
                            $p['list_variants_separately'] = true;
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
                            $p['tag_badges'] = $tagBadges;
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
                if ($this->variantsMode === 'force_off') {
                    $base['list_variants_separately'] = false;
                } elseif ($this->variantsMode === 'force_on') {
                    $base['list_variants_separately'] = true;
                }
                $base['variants'] = $product->variants()
                    ->where('is_active', true)
                    ->orderBy('position')
                    ->take(10)
                    ->get()
                    ->toArray();
                $base['tag_badges'] = $tagBadges;
                return collect([$base]);
            });
    }


    private function tagProducts($settingPrefix, $limit)
    {
        $tagIds = (array) setting("{$settingPrefix}_tags", []);

        return Product::forCard()
            ->with([
                'variants',
                'variations',
                'tags',
                'tags.tagBadges' => function ($query) {
                    $query->active();
                },
            ])
            ->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', array_filter($tagIds));
            })
            ->when(!is_null($limit), function ($q) use ($limit) {
                $q->limit($limit);
            })
            ->get()
            ->flatMap(function (Product $product) {
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

                $shouldListSeparately = (bool) $product->list_variants_separately;
                if ($this->variantsMode === 'force_on') {
                    $shouldListSeparately = true;
                } elseif ($this->variantsMode === 'force_off') {
                    $shouldListSeparately = false;
                }

                if ($shouldListSeparately) {
                    $variants = $product->variants()->orderBy('position')->get();
                    $actives = $variants->filter(function ($v) {
                        return (bool) ($v->is_active ?? false);
                    });

                    if ($actives->isNotEmpty()) {
                        return $actives->map(function ($variant) use ($product, $tagBadges, $variantLabel) {
                            $p = $product->clean();
                            $p['variant_attribute_label'] = $variantLabel;
                            $p['name'] = $product->name;
                            $p['list_variants_separately'] = true;
                            $p['variant'] = $variant->toArray();
                            $p['url'] = $variant->url() ?? $product->url();
                            $p['base_image'] = ($variant->base_image ?? $product->base_image);
                            $p['formatted_price'] = $variant->formatted_price ?? $product->formatted_price;
                            $p['formatted_price_range'] = null;
                            $p['tag_badges'] = $tagBadges;
                            return $p;
                        });
                    }
                }
                $base = $product->clean();
                $base['variant_attribute_label'] = $variantLabel;
                if ($this->variantsMode === 'force_off') {
                    $base['list_variants_separately'] = false;
                } elseif ($this->variantsMode === 'force_on') {
                    $base['list_variants_separately'] = true;
                }
                $base['variants'] = $product->variants()
                    ->where('is_active', true)
                    ->orderBy('position')
                    ->take(10)
                    ->get()
                    ->toArray();
                $base['tag_badges'] = $tagBadges;

                return collect([$base]);
            });
    }


    private function categoryProducts($settingPrefix, $limit)
    {
        return Category::findOrNew(setting("{$settingPrefix}_category_id"))
            ->products()
            ->latest()
            ->forCard()
            ->with([
                'variants',
                'variations',
                'tags',
                'tags.tagBadges' => function ($query) {
                    $query->active();
                },
            ])
            ->take($limit)
            ->get()
            ->flatMap(function (Product $product) {
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

                $shouldListSeparately = (bool) $product->list_variants_separately;
                if ($this->variantsMode === 'force_on') {
                    $shouldListSeparately = true;
                } elseif ($this->variantsMode === 'force_off') {
                    $shouldListSeparately = false;
                }

                if ($shouldListSeparately) {
                    $variants = $product->variants()->orderBy('position')->get();
                    $actives = $variants->filter(function ($v) {
                        return (bool) ($v->is_active ?? false);
                    });

                    if ($actives->isNotEmpty()) {
                        return $actives->map(function ($variant) use ($product, $tagBadges, $variantLabel) {
                            $p = $product->clean();
                            $p['variant_attribute_label'] = $variantLabel;
                            $p['name'] = $product->name;
                            $p['list_variants_separately'] = true;
                            $p['variant'] = $variant->toArray();
                            $p['url'] = $variant->url() ?? $product->url();
                            $p['base_image'] = ($variant->base_image ?? $product->base_image);
                            $p['formatted_price'] = $variant->formatted_price ?? $product->formatted_price;
                            $p['formatted_price_range'] = null;
                            $p['tag_badges'] = $tagBadges;
                            return $p;
                        });
                    }
                }
                $base = $product->clean();
                $base['variant_attribute_label'] = $variantLabel;
                $base['variants'] = $product->variants()
                    ->where('is_active', true)
                    ->orderBy('position')
                    ->take(10)
                    ->get()
                    ->toArray();
                $base['tag_badges'] = $tagBadges;

                return collect([$base]);
            });
    }


    private function recentlyViewedProducts($limit)
    {
        return collect($this->recentlyViewed->products())
            ->reverse()
            ->when(!is_null($limit), function (Collection $products) use ($limit) {
                return $products->take($limit);
            })
            ->values();
    }


    private function latestProductsCallback($limit)
    {
        return function ($query) use ($limit) {
            $query->latest()
                ->when(!is_null($limit), function ($q) use ($limit) {
                    $q->limit($limit);
                });
        };
    }


    private function customProductsCallback($settingPrefix)
    {
        return function ($query) use ($settingPrefix) {
            $productIds = setting("{$settingPrefix}_products", []);

            $query->whereIn('id', $productIds)
                ->when(!empty($productIds), function ($q) use ($productIds) {
                    $productIdsString = collect($productIds)->filter()->implode(',');

                    $q->orderByRaw("FIELD(id, {$productIdsString})");
                });
        };
    }
}
