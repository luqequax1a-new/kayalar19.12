<?php

namespace Modules\Product\Http\Response;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Illuminate\Contracts\Support\Responsable;

class SuggestionsResponse implements Responsable
{
    private string $query;
    private Collection $products;
    private Collection $categories;
    private int $totalResults;


    /**
     * Create a new instance.
     *
     * @param string $query
     * @param int $totalResults
     * @param Collection $products
     * @param Collection $categories
     */
    public function __construct(string $query, Collection $products, Collection $categories, int $totalResults)
    {
        $this->query = $query;
        $this->products = $products;
        $this->categories = $categories;
        $this->totalResults = $totalResults;
    }


    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'categories' => $this->transformCategories(),
            'products' => $this->transformProducts(),
            'remaining' => $this->getRemainingCount(),
        ]);
    }


    /**
     * Transform the categories.
     *
     * @return Collection
     */
    private function transformCategories(): Collection
    {
        return $this->categories->map(function (Category $category) {
            return [
                'slug' => $category->slug,
                'name' => $category->name,
                'url' => $category->url(),
            ];
        })->unique('slug')->values();
    }


    /**
     * Transform the products.
     *
     * @return Collection
     */
    private function transformProducts(): Collection
    {
        return $this->products
            ->flatMap(function (Product $product) {
                $baseName = $this->highlight($product->name);

                if ((bool) ($product->list_variants_separately ?? false)) {
                    $variants = $product->relationLoaded('variants') ? $product->variants : collect();
                    $actives = $variants->filter(function ($v) {
                        return (bool) ($v->is_active ?? false);
                    });

                    if ($actives->isNotEmpty()) {
                        return $actives->map(function ($variant) use ($product, $baseName) {
                            $variantLabel = trim((string) ($variant->name ?? ''));

                            $baseImage = ($variant->base_image && $variant->base_image->id)
                                ? $variant->base_image
                                : $product->base_image;

                            $thumbWidth = (int) config('image_optimization.variants.widths.thumb', 80);
                            $thumb2xWidth = (int) config('image_optimization.variants.widths.thumb_2x', 0);
                            $thumbSrc = $baseImage ? (media_variant_url($baseImage, $thumbWidth, 'webp') ?? ($baseImage->path ?? null)) : null;
                            $thumb2xSrc = ($baseImage && $thumb2xWidth > 0) ? media_variant_url($baseImage, $thumb2xWidth, 'webp') : null;
                            $thumbSrcset = null;
                            if ($thumbSrc) {
                                $thumbSrcset = $thumbSrc . ' ' . $thumbWidth . 'w';
                                if ($thumb2xSrc) {
                                    $thumbSrcset .= ', ' . $thumb2xSrc . ' ' . $thumb2xWidth . 'w';
                                }
                            }

                            return [
                                // Use a unique identifier for keyboard navigation in HeaderSearch.
                                // (It doesn't have to match the actual product slug.)
                                'slug' => $product->slug . '-v' . (string) ($variant->uid ?? $variant->id),
                                'name' => $variantLabel !== '' ? ($baseName . ' - ' . e($variantLabel)) : $baseName,
                                'formatted_price' => $variant->formatted_price ?? $product->formatted_price,
                                'base_image' => $baseImage,
                                'thumb_src' => $thumbSrc,
                                'thumb_srcset' => $thumbSrcset,
                                'is_out_of_stock' => $variant->isOutOfStock(),
                                'url' => $variant->url() ?? $product->url(),
                            ];
                        });
                    }
                }

                $baseImage = ($product->base_image && $product->base_image->id)
                    ? $product->base_image
                    : (($product->variant && $product->variant->base_image && $product->variant->base_image->id)
                        ? $product->variant->base_image
                        : $product->base_image);

                $thumbWidth = (int) config('image_optimization.variants.widths.thumb', 80);
                $thumb2xWidth = (int) config('image_optimization.variants.widths.thumb_2x', 0);
                $thumbSrc = $baseImage ? (media_variant_url($baseImage, $thumbWidth, 'webp') ?? ($baseImage->path ?? null)) : null;
                $thumb2xSrc = ($baseImage && $thumb2xWidth > 0) ? media_variant_url($baseImage, $thumb2xWidth, 'webp') : null;
                $thumbSrcset = null;
                if ($thumbSrc) {
                    $thumbSrcset = $thumbSrc . ' ' . $thumbWidth . 'w';
                    if ($thumb2xSrc) {
                        $thumbSrcset .= ', ' . $thumb2xSrc . ' ' . $thumb2xWidth . 'w';
                    }
                }

                return collect([
                    [
                        'slug' => $product->slug,
                        'name' => $baseName,
                        'formatted_price' => $product->variant?->formatted_price ?? $product->formatted_price,
                        'base_image' => $baseImage,
                        'thumb_src' => $thumbSrc,
                        'thumb_srcset' => $thumbSrcset,
                        'is_out_of_stock' => $product->variant?->isOutOfStock() ?? $product->isOutOfStock(),
                        'url' => $product->variant?->url() ?? $product->url(),
                    ],
                ]);
            })
            ->take(10)
            ->values();
    }


    /**
     * Highlight the given text.
     *
     * @param string $text
     *
     * @return string
     */
    private function highlight($text): string
    {
        $query = str_replace(' ', '|', preg_quote($this->query));

        return preg_replace("/($query)/i", '<em>$1</em>', $text);
    }


    /**
     * Get remaining results count.
     *
     * @return int
     */
    private function getRemainingCount(): int
    {
        return $this->totalResults - $this->products->count();
    }
}
