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
    private Collection $popularSearches;
    private Collection $popularCategories;
    private Collection $brands;
    private Collection $bestSellers;
    private Collection $bestSellingCategories;
    private Collection $bestSellingBrands;


    /**
     * Create a new instance.
     *
     * @param string $query
     * @param Collection $products
     * @param Collection $categories
     * @param int $totalResults
     * @param Collection|null $popularSearches
     * @param Collection|null $popularCategories
     * @param Collection|null $brands
     */
    public function __construct(string $query, Collection $products, Collection $categories, int $totalResults, Collection $popularSearches = null, Collection $popularCategories = null, Collection $brands = null, Collection $bestSellers = null, Collection $bestSellingCategories = null, Collection $bestSellingBrands = null)
    {
        $this->query = $query;
        $this->products = $products;
        $this->categories = $categories;
        $this->totalResults = $totalResults;
        $this->popularSearches = $popularSearches ?? collect();
        $this->popularCategories = $popularCategories ?? collect();
        $this->brands = $brands ?? collect();
        $this->bestSellers = $bestSellers ?? collect();
        $this->bestSellingCategories = $bestSellingCategories ?? collect();
        $this->bestSellingBrands = $bestSellingBrands ?? collect();
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
            'categories' => $this->transformCategories($this->categories),
            'brands' => $this->transformBrands($this->brands),
            'products' => $this->transformProducts(),
            'remaining' => $this->getRemainingCount(),
            'popular_searches' => $this->popularSearches,
            'popular_categories' => $this->transformCategories($this->popularCategories),
            'best_sellers' => $this->transformProducts($this->bestSellers),
            'best_selling_categories' => $this->transformCategories($this->bestSellingCategories),
            'best_selling_brands' => $this->transformBrands($this->bestSellingBrands),
        ]);
    }


    /**
     * Transform the brands.
     *
     * @param Collection $brandsCollection
     * @return Collection
     */
    private function transformBrands(Collection $brandsCollection): Collection
    {
        return $brandsCollection->map(function ($brand) {
            return [
                'name' => $this->highlight($brand->name),
                'url' => $brand->url(),
                'slug' => $brand->slug,
            ];
        });
    }


    /**
     * Transform the categories.
     *
     * @param Collection $categories
     * @return Collection
     */
    private function transformCategories(Collection $categories): Collection
    {
        return $categories->map(function (Category $category) {
            return [
                'slug' => $category->slug,
                'name' => $category->name,
                'url' => $category->url(),
                'logo' => $category->logo->exists ? $category->logo->path : null,
            ];
        })->unique('slug')->values();
    }


    /**
     * Transform the products.
     *
     * @param Collection|null $productsCollection
     * @return Collection
     */
    private function transformProducts(?Collection $productsCollection = null): Collection
    {
        $productsToTransform = $productsCollection ?? $this->products;
        
        return $productsToTransform
            ->flatMap(function (Product $product) {
                $baseName = $this->highlight($product->name);

                if ($product->list_variants_separately) {
                    $variants = $product->relationLoaded('variants') 
                        ? $product->variants 
                        : $product->variants()->where('is_active', true)->get();
                    
                    $actives = $variants->filter(function ($v) {
                        return (bool) ($v->is_active ?? false);
                    });

                    if ($actives->isNotEmpty()) {
                        return $actives->map(function ($variant) use ($product, $baseName) {
                            $variantName = trim((string) ($variant->name ?? ''));
                            
                            $displayName = $baseName;
                            if ($variantName !== '') {
                                $displayName = $baseName . ' - ' . e($variantName);
                            }

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
                                'slug' => $product->slug . '-v' . (string) ($variant->uid ?? $variant->id),
                                'name' => $displayName,
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

                // Get default variant for fallback (using the variant accessor which handles loaded relations/queries)
                $defaultVariant = $product->variant;

                $baseImage = ($product->base_image && $product->base_image->id)
                    ? $product->base_image
                    : (($defaultVariant && $defaultVariant->base_image && $defaultVariant->base_image->id)
                        ? $defaultVariant->base_image
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
                        'formatted_price' => $defaultVariant?->formatted_price ?? $product->formatted_price,
                        'base_image' => $baseImage,
                        'thumb_src' => $thumbSrc,
                        'thumb_srcset' => $thumbSrcset,
                        'is_out_of_stock' => $defaultVariant?->isOutOfStock() ?? $product->isOutOfStock(),
                        'url' => $defaultVariant?->url() ?? $product->url(),
                    ],
                ]);
            })
            ->take(10)
            ->values();
    }


    /**
     * Highlight the given text.
     *
     * @param mixed $text
     *
     * @return string
     */
    private function highlight($text): string
    {
        $text = (string) ($text ?? '');

        if ($this->query === '' || is_null($this->query)) {
            return e($text);
        }

        $query = str_replace(' ', '|', preg_quote($this->query));

        return (string) preg_replace("/($query)/i", '<em>$1</em>', $text);
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
