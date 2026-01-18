<?php

namespace Modules\ProductFeeds\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Tax\Entities\TaxRate;

class ProductFeedBuilder
{
    private function sanitizeCategoryString(?string $value): string
    {
        $decoded = (string) ($value ?? '');

        if ($decoded === '') {
            return '';
        }

        $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = strip_tags($decoded);
        $decoded = str_replace(['&gt;', '&amp;gt;', '&amp;amp;gt;'], '>', $decoded);
        $decoded = preg_replace('/\s*>\s*/u', ' > ', $decoded) ?? '';
        $decoded = preg_replace('/\s+/u', ' ', $decoded) ?? '';

        return trim($decoded);
    }

    private function productTypeFor(Product $product, ?string $categoryPath, ?string $googleCategory): string
    {
        $categoryPath = $this->sanitizeCategoryString($categoryPath);
        if ($categoryPath !== '') {
            return $categoryPath;
        }

        $primaryName = $this->sanitizeCategoryString($product->primaryCategory?->name);
        if ($primaryName !== '') {
            return $primaryName;
        }

        $googleCategory = $this->sanitizeCategoryString($googleCategory);
        if ($googleCategory !== '') {
            $parts = array_values(array_filter(array_map('trim', explode(' > ', $googleCategory)), fn ($p) => $p !== ''));
            if (! empty($parts)) {
                return (string) end($parts);
            }
        }

        $firstCategoryName = $this->sanitizeCategoryString($product->categories->first()?->name);
        if ($firstCategoryName !== '') {
            return $firstCategoryName;
        }

        return '';
    }

    public function queryProducts(array $options = [])
    {
        $includeOutOfStock = (bool) ($options['include_out_of_stock'] ?? setting('product_feeds.global.include_out_of_stock', false));
        $includeUnpublished = (bool) ($options['include_unpublished'] ?? setting('product_feeds.global.include_unpublished', false));

        $query = Product::query()
            ->with(['primaryCategory', 'categories', 'brand', 'productMedia', 'variants', 'variants.files', 'taxClass.taxRates', 'variations', 'variations.values'])
            ->with('translations');

        if (! $includeUnpublished) {
            $query->where('is_active', true);
        }

        if (! $includeOutOfStock) {
            $query->where(function ($q) {
                $q->where('in_stock', true)
                    ->where(function ($sq) {
                        $sq->where('manage_stock', false)->orWhere('qty', '>', 0);
                    });
            });
        }

        return $query;
    }


    /**
     * Build readable variation query params for a variant.
     *
     * key = Str::slug(variation.name)
     * value = Str::slug(selectedValue.label)
     *
     * Ordering is stable: uses product variations display order.
     */
    public function readableParamsForVariant(Product $product, ProductVariant $variant): array
    {
        try {
            $product->loadMissing(['variations.values']);

            $uids = array_filter(explode('.', (string) $variant->uids));
            if (empty($uids)) {
                return [];
            }

            $uidSet = array_fill_keys($uids, true);
            $params = [];

            foreach ($product->variations as $variation) {
                $key = Str::slug((string) ($variation->name ?? ''));
                if ($key === '') {
                    continue;
                }

                $selected = null;
                foreach ($variation->values as $value) {
                    if (isset($uidSet[$value->uid])) {
                        $selected = $value;
                        break;
                    }
                }

                if (! $selected) {
                    continue;
                }

                $valueSlug = Str::slug((string) ($selected->label ?? ''));
                if ($valueSlug === '') {
                    continue;
                }

                $params[$key] = $valueSlug;
            }

            return $params;
        } catch (\Throwable $e) {
            return [];
        }
    }


    public function productUrlWithReadableVariantParams(Product $product, ProductVariant $variant): string
    {
        $base = route('products.show', ['slug' => $product->slug]);
        $params = $this->readableParamsForVariant($product, $variant);

        if (! empty($params)) {
            $base .= '?' . http_build_query($params);
        }

        return $base;
    }

    public function streamNormalizedItemsForFeed(string $channel, callable $yield): void
    {
        $includeVariantsGlobal = (bool) setting('product_feeds.global.include_variants', true);

        $includeVariants = match ($channel) {
            'meta' => (bool) setting('product_feeds.meta.use_variants', $includeVariantsGlobal),
            default => $includeVariantsGlobal,
        };

        $currency = (string) setting('product_feeds.global.currency', currency());
        $defaultGoogleCategory = (string) setting('product_feeds.google.category', '');

        $this->queryProducts()
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($yield, $includeVariants, $currency, $defaultGoogleCategory, $channel) {
                foreach ($products as $product) {
                    $categoryPath = $this->buildCategoryPath($product);

                    $productGoogleCategory = (string) ($product->google_product_category_path ?? '');

                    if ($productGoogleCategory !== '') {
                        $googleCategory = $productGoogleCategory;
                    } elseif ($defaultGoogleCategory !== '') {
                        $googleCategory = $defaultGoogleCategory;
                    } else {
                        $googleCategory = $categoryPath ?: null;
                    }

                    $brand = $this->brandName($product);
                    $description = $this->buildDescription($product);

                    $variants = $product->variants ?? collect();

                    if ($includeVariants && $variants->count() > 0) {
                        foreach ($variants as $variant) {
                            if ((bool) $variant->is_active === false) {
                                continue;
                            }

                            $row = $this->buildRowForVariant(
                                $product,
                                $variant,
                                $channel,
                                $currency,
                                $brand,
                                $description,
                                $categoryPath,
                                $googleCategory
                            );

                            $yield($row);
                        }
                    } else {
                        $row = $this->buildRowForProduct(
                            $product,
                            $channel,
                            $currency,
                            $brand,
                            $description,
                            $categoryPath,
                            $googleCategory
                        );

                        $yield($row);
                    }
                }
            });
    }

    public function buildCategoryPath($product): string
    {
        $category = $product->seoCategory();

        if (! $category) {
            return '';
        }

        $segments = [];
        $current = $category;

        while ($current) {
            $segments[] = $current->name;

            $parentId = $current->parent_id ?? null;

            if (! $parentId) {
                break;
            }

            $current = Category::query()->find($parentId);
        }

        return $this->sanitizeCategoryString(implode(' > ', array_reverse($segments)));
    }

    public function productUrl(Product $product, $variantId = null): string
    {
        $url = $product->url();

        if ($variantId) {
            $separator = str_contains($url, '?') ? '&' : '?';
            return $url . $separator . 'variant=' . $variantId;
        }

        return $url;
    }

    public function brandName(Product $product): string
    {
        if ($product->relationLoaded('brand') && $product->brand) {
            return (string) $product->brand->name;
        }

        $fallback = (string) setting('product_feeds.global.brand_name', setting('store_name'));

        return trim($fallback);
    }

    public function availability(Product $product): string
    {
        return $product->is_out_of_stock ? 'out of stock' : 'in stock';
    }

    public function priceWithCurrency(Product $product): string
    {
        $currency = (string) setting('product_feeds.global.currency', currency());
        $amount = $product->selling_price ?? $product->price;

        $value = number_format((float) $amount, 2, '.', '');

        return $value . ' ' . $currency;
    }

    public function mainImage(Product $product): ?string
    {
        $baseImage = $product->base_image;

        return $baseImage['path'] ?? null;
    }

    public function additionalImages(Product $product): array
    {
        if (! $product->additional_images) {
            return [];
        }

        return $product->additional_images->pluck('path')->all();
    }

    protected function cleanText(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $clean = strip_tags($text);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = str_replace("\xC2\xA0", ' ', $clean);
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';
        $clean = trim($clean);

        if ($clean === '') {
            return '';
        }

        return mb_convert_encoding($clean, 'UTF-8', 'UTF-8');
    }

    protected function buildDescription(Product $product): string
    {
        $meta = $product->seo_meta_description ?? null;

        if (is_string($meta) && $meta !== '') {
            $clean = $this->cleanText($meta);
            return Str::limit($clean, 5000, '');
        }

        $short = $this->cleanText($product->short_description ?? null);

        if ($short !== '') {
            return Str::limit($short, 5000, '');
        }

        $desc = $this->cleanText($product->description ?? null);

        if ($desc !== '') {
            return Str::limit($desc, 5000, '');
        }

        return Str::limit($this->cleanText($product->name ?? ''), 5000, '');
    }


    protected function buildTitle(Product $product, ?ProductVariant $variant = null, string $channel = 'google'): string
    {
        $metaTitle = null;

        try {
            $metaTitle = optional($product->meta)->meta_title;
        } catch (\Throwable $e) {
            $metaTitle = null;
        }

        $base = $metaTitle ?: ($product->name ?? '');
        $base = $this->cleanText($base);

        if ($variant !== null && ! empty($variant->name)) {
            $variantName = $this->cleanText($variant->name);

            if ($variantName !== '') {
                $base = trim($base . ' - ' . $variantName);
            }
        }

        return Str::limit($base, 150, '');
    }

    protected function numericPriceForProduct(Product $product): array
    {
        $base = $product->price;
        $price = (float) $base->amount();

        $sale = null;

        if ($product->hasSpecialPrice()) {
            $sale = (float) $product->getSpecialPrice()->amount();
        }

        return [$price, $sale];
    }

    protected function numericPriceForVariant(ProductVariant $variant): array
    {
        $base = $variant->price;
        $price = (float) $base->amount();

        $sale = null;

        if ($variant->hasSpecialPrice()) {
            $sale = (float) $variant->getSpecialPrice()->amount();
        }

        return [$price, $sale];
    }

    /**
     * Build normalized feed items for a given channel.
     */
    public function normalizedItemsForFeed(string $channel): Collection
    {
        $rows = [];

        $this->streamNormalizedItemsForFeed($channel, function (array $row) use (&$rows) {
            $rows[] = $row;
        });

        return collect($rows);
    }

    public function resolveVatRate(Product $product): int
    {
        try {
            $country = (string) setting('product_feeds.google.shipping_country', 'TR');
            $taxClass = $product->taxClass ?? null;

            if (! $taxClass) {
                return 0;
            }

            $rates = $taxClass->taxRates ?? null;

            if (! $rates || (is_countable($rates) && count($rates) === 0)) {
                return 0;
            }

            $picked = null;

            foreach ($rates as $rate) {
                if (! $rate instanceof TaxRate) {
                    continue;
                }

                if ((string) ($rate->country ?? '') === $country) {
                    $picked = $rate;
                    break;
                }
            }

            if ($picked === null) {
                $picked = $rates->first();
            }

            $value = (int) round((float) ($picked->rate ?? 0));

            return $value > 0 ? $value : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function normalizeAbsoluteUrl(?string $url): string
    {
        $url = (string) ($url ?? '');

        if ($url === '') {
            return '';
        }

        $base = (string) config('app.url');

        if ($base === '') {
            $base = url('/');
        }

        $base = rtrim($base, '/');

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $parsed = parse_url($url);
            $host = strtolower((string) ($parsed['host'] ?? ''));

            if ($host === '' || $host === '127.0.0.1' || $host === 'localhost') {
                $path = (string) ($parsed['path'] ?? '');
                $query = isset($parsed['query']) ? ('?' . $parsed['query']) : '';
                $fragment = isset($parsed['fragment']) ? ('#' . $parsed['fragment']) : '';

                if ($path !== '') {
                    return $base . $path . $query . $fragment;
                }
            }

            return $url;
        }

        return $base . '/' . ltrim($url, '/');
    }

    public function normalizeStorefrontUrl(string $url): string
    {
        try {
            if (function_exists('non_localized_url')) {
                return (string) non_localized_url($url);
            }
        } catch (\Throwable $e) {
        }

        return $url;
    }

    private function buildRowForProduct(
        Product $product,
        string $channel,
        string $currency,
        string $brand,
        string $description,
        ?string $categoryPath,
        ?string $googleCategory
    ): array {
        [$price, $sale] = $this->numericPriceForProduct($product);

        $url = $this->normalizeAbsoluteUrl($this->normalizeStorefrontUrl($this->productUrl($product)));

        $categoryPath = $this->sanitizeCategoryString($categoryPath);
        $googleCategory = $this->sanitizeCategoryString($googleCategory);
        $productType = $this->productTypeFor($product, $categoryPath, $googleCategory);

        return [
            'product' => $product,
            'variant' => null,
            'id' => (string) $product->id,
            'item_group_id' => (string) $product->id,
            'sku' => $product->sku,
            'availability' => $this->availability($product),
            'title' => $this->buildTitle($product, null, $channel),
            'description' => $description,
            'url' => $url,
            'brand' => $brand,
            'category_path' => $categoryPath !== '' ? $categoryPath : null,
            'product_type' => $productType,
            'google_category' => $googleCategory !== '' ? $googleCategory : null,
            'price' => $price,
            'sale_price' => $sale,
            'sale_price_start' => $product->special_price_start,
            'sale_price_end' => $product->special_price_end,
            'currency' => $currency,
            'main_image' => $this->normalizeAbsoluteUrl($this->mainImage($product)),
            'additional_images' => array_slice(array_map([$this, 'normalizeAbsoluteUrl'], $this->additionalImages($product)), 0, 10),
            'vat_rate' => $this->resolveVatRate($product),
            'stock' => (int) $product->qty,
            'weight' => (float) ($product->weight ?? 0),
        ];
    }

    private function buildRowForVariant(
        Product $product,
        ProductVariant $variant,
        string $channel,
        string $currency,
        string $brand,
        string $description,
        ?string $categoryPath,
        ?string $googleCategory
    ): array {
        [$price, $sale] = $this->numericPriceForVariant($variant);

        $mainImage = $variant->base_image?->path ?: $this->mainImage($product);
        $additional = $variant->additional_images->pluck('path')->all();
        // Feed link: point each variant to readable-parameter product URL (no ?variant=UID, no path-based variant URL).
        $url = $this->normalizeAbsoluteUrl(
            $this->normalizeStorefrontUrl($this->productUrlWithReadableVariantParams($product, $variant))
        );

        $categoryPath = $this->sanitizeCategoryString($categoryPath);
        $googleCategory = $this->sanitizeCategoryString($googleCategory);
        $productType = $this->productTypeFor($product, $categoryPath, $googleCategory);

        return [
            'product' => $product,
            'variant' => $variant,
            'id' => (string) $variant->id,
            'item_group_id' => (string) $product->id,
            'sku' => $variant->sku ?: $product->sku,
            'availability' => $variant->is_out_of_stock ? 'out of stock' : 'in stock',
            'title' => $this->buildTitle($product, $variant, $channel),
            'description' => $description,
            'url' => $url,
            'brand' => $brand,
            'category_path' => $categoryPath !== '' ? $categoryPath : null,
            'product_type' => $productType,
            'google_category' => $googleCategory !== '' ? $googleCategory : null,
            'price' => $price,
            'sale_price' => $sale,
            'sale_price_start' => $variant->special_price_start,
            'sale_price_end' => $variant->special_price_end,
            'currency' => $currency,
            'main_image' => $this->normalizeAbsoluteUrl($mainImage),
            'additional_images' => array_slice(array_map([$this, 'normalizeAbsoluteUrl'], $additional), 0, 10),
            'vat_rate' => $this->resolveVatRate($product),
            'stock' => (int) $variant->qty,
            'weight' => (float) ($variant->weight ?? $product->weight ?? 0),
        ];
    }
}
