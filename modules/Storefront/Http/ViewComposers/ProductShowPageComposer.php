<?php

namespace Modules\Storefront\Http\ViewComposers;

use Illuminate\View\View;
use Spatie\SchemaOrg\Schema;
use Modules\Storefront\Banner;
use Modules\Storefront\Feature;
use Illuminate\Support\Collection;
use Modules\Product\Entities\Product;
use Spatie\SchemaOrg\ItemAvailability;
use Modules\Shipping\Services\SmartShippingCalculator;
use Illuminate\Support\Facades\Cache;
use Modules\SizeChart\Services\SizeChartResolver;

class ProductShowPageComposer
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $product = $view->getData()['product'];

        $sizeChartData = [
            'hasSizeChart' => false,
            'sizeChartEndpoint' => null,
        ];

        try {
            $resolver = app(SizeChartResolver::class);
            $sizeChart = $resolver->resolveForProduct($product);

            if (!is_null($sizeChart)) {
                $sizeChartData['hasSizeChart'] = true;
                $sizeChartData['sizeChartEndpoint'] = route('size_charts.product.show', ['productId' => $product->id]);
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        $variantId = null;
        try {
            $variantId = optional($product->variant)->id;
        } catch (\Throwable $e) {
            $variantId = null;
        }

        $locale = null;
        try {
            $locale = locale();
        } catch (\Throwable $e) {
            $locale = null;
        }

        $schemaCacheKey = 'storefront:product_schema_script:' . $product->id . ':' . ($variantId ?: 0) . ':' . ($locale ?: '');
        $breadcrumbSchemaCacheKey = 'storefront:breadcrumb_schema_script:' . $product->id . ':' . ($variantId ?: 0) . ':' . ($locale ?: '');

        $view->with(array_merge([
            'features' => Cache::rememberForever('storefront_product_page_features', function () {
                return Feature::all();
            }),
            'banner' => Cache::rememberForever('storefront_product_page_banner', function () {
                return Banner::getProductPageBanner();
            }),
            // Pre-render and cache the Schema.org script to reduce TTFB.
            'productSchemaMarkupScript' => Cache::remember($schemaCacheKey, now()->addMinutes(30), function () use ($product) {
                return $this->schemaMarkup($product)->toScript();
            }),
            'categoryBreadcrumb' => $this->getSeoCategoryBreadcrumb($product),
            'breadcrumbSchemaMarkupScript' => Cache::remember($breadcrumbSchemaCacheKey, now()->addMinutes(30), function () use ($product) {
                return $this->breadcrumbSchema($product)->toScript();
            }),
        ], $sizeChartData));
    }


    private function schemaMarkup(Product $product)
    {
        $imagePath = null;
        try {
            $imagePath = optional(optional($product->variant)->base_image)->path
                ?: optional($product->base_image)->path
                ?: 'storage/media/image-placeholder.png';
            
            // Ensure absolute URL
            $imagePath = str_starts_with($imagePath, 'http') ? $imagePath : url($imagePath);
        } catch (\Throwable $e) {
            $imagePath = url('storage/media/image-placeholder.png');
        }

        $rawDescription = $product->short_description ?: $product->description ?: $product->seo_meta_description;

        if ($rawDescription) {
            try {
                $rawDescription = trim(strip_tags((string) $rawDescription));
                // Çok uzun açıklamaları kısalt, Google için temiz ve öz bir metin bırak
                if (mb_strlen($rawDescription) > 300) {
                    $rawDescription = mb_substr($rawDescription, 0, 300) . '...';
                }
            } catch (\Throwable $e) {
                // Temizleme sırasında hata olursa olduğu gibi bırak
            }
        }

        $schema = Schema::product()
            ->name($product->name)
            ->sku($product->sku ?: (string) $product->id)
            ->url($product->url()) // $product->url() already returns absolute URL usually
            ->image($imagePath)
            ->brand($this->brandSchema($product))
            ->description($rawDescription);

        $offers = $this->offersSchema($product);
        if ($offers) {
            $schema->offers($offers);
        }

        try {
            $categoryPath = $this->buildCategoryPath($product);
            if ($categoryPath) {
                $schema->setProperty('category', $categoryPath);
            }

            if ($product->google_product_category_path) {
                $schema->setProperty('googleProductCategory', $product->google_product_category_path);
            }

            $schema->setProperty('productId', (string) $product->id);

            $mpn = $product->sku ?: ($product->id ? (string) $product->id : null);
            if ($mpn) {
                $schema->setProperty('mpn', $mpn);
            }

            $schema->setProperty('itemCondition', 'https://schema.org/NewCondition');
        } catch (\Throwable $e) {
        }

        $reviewStats = $this->reviewStats($product);

        if (($reviewStats['count'] ?? 0) > 0) {
            $schema->aggregateRating($this->aggregateRatingSchema($reviewStats));
            
            // Add actual reviews to the schema
            $reviews = $this->reviewsSchema($product);
            if (!empty($reviews)) {
                $schema->review($reviews);
            }
        }

        return $schema;
    }


    private function brandSchema(Product $product)
    {
        $brandName = null;
        try {
            $brandName = optional($product->brand)->name;
        } catch (\Throwable $e) {
        }
        if (!$brandName || strtolower($brandName) === 'brand') {
            $brandName = config('app.name') ?: 'Store';
        }
        return Schema::brand()->name($brandName);
    }


    private function aggregateRatingSchema(array $reviewStats)
    {
        return Schema::aggregateRating()
            ->ratingValue((float) ($reviewStats['avg'] ?? 0))
            ->ratingCount((int) ($reviewStats['count'] ?? 0));
    }


    private function reviewsSchema(Product $product): array
    {
        try {
            $cacheKey = 'storefront:product_schema_reviews:' . $product->id;

            $reviews = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($product) {
                return $product->reviews()
                    ->latest()
                    ->take(50)
                    ->get();
            });

            if ($reviews->isEmpty()) {
                return [];
            }

            return $reviews->map(function ($review) {
                try {
                    $authorName = trim((string) $review->reviewer_name);
                } catch (\Throwable $e) {
                    $authorName = '';
                }

                $author = null;

                if ($authorName !== '') {
                    $author = Schema::person()->name($authorName);
                }

                $ratingValue = null;

                try {
                    $ratingValue = (int) $review->rating;
                } catch (\Throwable $e) {
                    $ratingValue = null;
                }

                $ratingSchema = null;

                if ($ratingValue && $ratingValue > 0) {
                    $ratingSchema = Schema::rating()
                        ->ratingValue($ratingValue)
                        ->bestRating(5)
                        ->worstRating(1);
                }

                $reviewBody = '';

                try {
                    $reviewBody = trim(strip_tags((string) $review->comment));
                } catch (\Throwable $e) {
                    $reviewBody = '';
                }

                $datePublished = null;

                try {
                    if ($review->created_at) {
                        $datePublished = $review->created_at->toDateString();
                    }
                } catch (\Throwable $e) {
                    $datePublished = null;
                }

                $schemaReview = Schema::review();

                if ($author) {
                    $schemaReview->author($author);
                }

                if ($reviewBody !== '') {
                    $schemaReview->reviewBody($reviewBody);
                }

                if ($ratingSchema) {
                    $schemaReview->reviewRating($ratingSchema);
                }

                if ($datePublished) {
                    $schemaReview->datePublished($datePublished);
                }

                return $schemaReview;
            })->all();
        } catch (\Throwable $e) {
            return [];
        }
    }


    private function offersSchema(Product $product)
    {
        $format = function ($money) {
            try {
                $amount = $money->convertToCurrentCurrency()->amount();
                return number_format((float) $amount, 2, '.', '');
            } catch (\Throwable $e) {
                return null;
            }
        };

        try {
            $variants = $product->relationLoaded('variants')
                ? $product->variants
                : $product->variants()->withoutGlobalScope('active')->get();
            $valid = $variants->filter(function ($v) {
                try {
                    $price = optional($v->selling_price)->convertToCurrentCurrency()->amount();
                    return (bool) $v->is_active && (bool) $v->isInStock() && $price !== null && $price > 0;
                } catch (\Throwable $e) {
                    return false;
                }
            });

            if ($valid->isEmpty()) {
                $base = ($product->variant ?? $product);
                $priceStr = $format($base->selling_price);
                if (!$priceStr) {
                    return null;
                }

                $offer = Schema::offer()
                    ->price($priceStr)
                    ->priceCurrency(currency())
                    ->availability($product->isInStock() ? ItemAvailability::InStock : ItemAvailability::OutOfStock)
                    ->url(url($product->url()))
                    ->setProperty('itemCondition', 'https://schema.org/NewCondition');

                $offer = $this->attachShippingDetails($offer);

                return $this->attachPriceValidUntil($offer, $product);
            }

            if ($valid->count() === 1) {
                $v = $valid->first();
                $priceStr = $format($v->selling_price);
                $offer = Schema::offer()
                    ->price($priceStr)
                    ->priceCurrency(currency())
                    ->availability($v->isInStock() ? ItemAvailability::InStock : ItemAvailability::OutOfStock)
                    ->url(url($product->url()))
                    ->setProperty('itemCondition', 'https://schema.org/NewCondition');

                $offer = $this->attachShippingDetails($offer);

                return $this->attachPriceValidUntil($offer, $product);
            }

            $prices = $valid->map(function ($v) use ($format) {
                return $format($v->selling_price);
            })->filter(function ($p) { return is_string($p) && $p !== '' && (float) $p > 0; });

            $low = $prices->map('floatval')->min();
            $high = $prices->map('floatval')->max();
            $offerCount = $valid->count();

            return Schema::aggregateOffer()
                ->lowPrice(number_format((float) $low, 2, '.', ''))
                ->highPrice(number_format((float) $high, 2, '.', ''))
                ->offerCount($offerCount)
                ->priceCurrency(currency())
                ->url(url($product->url()));
        } catch (\Throwable $e) {
            $base = ($product->variant ?? $product);
            $priceStr = $format($base->selling_price);
            if (!$priceStr) {
                return null;
            }
            $offer = Schema::offer()
                ->price($priceStr)
                ->priceCurrency(currency())
                ->availability($product->isInStock() ? ItemAvailability::InStock : ItemAvailability::OutOfStock)
                ->url(url($product->url()))
                ->setProperty('itemCondition', 'https://schema.org/NewCondition');

            $offer = $this->attachShippingDetails($offer);

            return $this->attachPriceValidUntil($offer, $product);
        }
    }


    private function attachPriceValidUntil($offer, Product $product)
    {
        try {
            if ($product->special_price && $product->special_price_end) {
                // special_price_end is casted to datetime (Carbon), ensure it's a future date
                $end = $product->special_price_end;

                if (method_exists($end, 'isFuture') && $end->isFuture()) {
                    // Google is fine with a simple YYYY-MM-DD date
                    $offer->priceValidUntil($end->toDateString());
                }
            }
        } catch (\Throwable $e) {
            // Fail silently; priceValidUntil is optional
        }

        return $offer;
    }


    private function attachShippingDetails($offer)
    {
        try {
            /** @var SmartShippingCalculator $calculator */
            $calculator = app(SmartShippingCalculator::class);

            if (! $calculator->isEnabled()) {
                return $offer;
            }

            $shippingMoney = $calculator->costForCurrentCart();

            try {
                $amount = $shippingMoney->convertToCurrentCurrency()->amount();
                $priceStr = number_format((float) $amount, 2, '.', '');
            } catch (\Throwable $e) {
                $priceStr = null;
            }

            if (! $priceStr || (float) $priceStr < 0) {
                return $offer;
            }

            $shippingRate = Schema::monetaryAmount()
                ->value($priceStr)
                ->currency(currency());

            $details = Schema::offerShippingDetails()
                ->shippingRate($shippingRate);

            // deliveryTime (ShippingDeliveryTime) içinde handlingTime + transitTime
            try {
                $handlingValue = Schema::quantitativeValue()
                    ->minValue(1)
                    ->maxValue(3)
                    ->unitCode('d'); // 1-3 gün hazırlama süresi

                $transitValue = Schema::quantitativeValue()
                    ->minValue(1)
                    ->maxValue(4)
                    ->unitCode('d'); // 1-4 gün teslim süresi

                $deliveryTime = Schema::shippingDeliveryTime()
                    ->handlingTime($handlingValue)
                    ->transitTime($transitValue);

                $details->deliveryTime($deliveryTime);
            } catch (\Throwable $e) {
                // deliveryTime isteğe bağlı; hata olursa yoksay
            }

            // shippingDestination (isteğe bağlı) - mağaza ülkesine göre
            try {
                $country = (string) (setting('store_country') ?: 'TR');

                if ($country !== '') {
                    $destination = Schema::definedRegion()
                        ->addressCountry($country);

                    $details->shippingDestination($destination);
                }
            } catch (\Throwable $e) {
                // shippingDestination isteğe bağlı; hata olursa yoksay
            }

            $offer->shippingDetails($details);

            return $offer;
        } catch (\Throwable $e) {
            return $offer;
        }
    }

    private function buildCategoryPath(Product $product): ?string
    {
        try {
            $category = $product->seoCategory();
            if (!$category) {
                return null;
            }

            $trail = $this->categoryTrail($category);
            $names = $trail->map(function ($cat) { return (string) $cat->name; })->all();

            return implode(' > ', $names);
        } catch (\Throwable $e) {
            return null;
        }
    }


    private function getSeoCategoryBreadcrumb(Product $product)
    {
        $category = $product->seoCategory();

        if (!$category) {
            return '';
        }

        $cacheKey = 'storefront:product_category_breadcrumb:' . $product->id . ':' . locale();
        $trail = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($category) {
            return $this->categoryTrail($category);
        });

        return $trail->map(function ($cat) {
            return "<li><a href='" . $cat->url() . "'>" . e($cat->name) . "</a></li>";
        })->implode('');
    }


    private function breadcrumbSchema(Product $product)
    {
        $items = [];

        $position = 1;
        $items[] = Schema::listItem()
            ->position($position++)
            ->item(
                Schema::thing()
                    ->name(trans('storefront::layouts.home'))
                    ->id(route('home'))
            );

        $category = $product->seoCategory();

        if ($category) {
            $trail = $this->categoryTrail($category);

            foreach ($trail as $cat) {
                $items[] = Schema::listItem()
                    ->position($position++)
                    ->item(
                        Schema::thing()
                            ->name($cat->name)
                            ->id($cat->url())
                    );
            }
        }

        $items[] = Schema::listItem()
            ->position($position++)
            ->item(
                Schema::thing()
                    ->name($product->name)
                    ->id($product->url())
            );

        return Schema::breadcrumbList()->itemListElement($items);
    }


    private function reviewStats(Product $product): array
    {
        if (($product->reviews_count ?? null) !== null || ($product->reviews_avg_rating ?? null) !== null) {
            return [
                'count' => (int) ($product->reviews_count ?? 0),
                'avg' => (float) ($product->reviews_avg_rating ?? 0),
            ];
        }

        $cacheKey = 'storefront:product_review_stats:' . $product->id;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($product) {
            $row = $product->reviews()
                ->selectRaw('count(*) as count')
                ->selectRaw('avg(rating) as avg')
                ->first();

            return [
                'count' => (int) ($row->count ?? 0),
                'avg' => (float) ($row->avg ?? 0),
            ];
        });
    }


    private function categoryTrail($category)
    {
        try {
            $levels = 0;
            $frontier = collect([(int) $category->id]);
            $all = collect();

            while ($frontier->isNotEmpty() && $levels++ < 10) {
                $chunk = \Modules\Category\Entities\Category::withoutGlobalScope('active')
                    ->whereIn('id', $frontier->all())
                    ->get();

                if ($chunk->isEmpty()) {
                    break;
                }

                $all = $all->merge($chunk);

                $parentIds = $chunk
                    ->pluck('parent_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $frontier = $parentIds->diff($all->pluck('id')->map(fn ($id) => (int) $id))->values();
            }

            if ($all->isEmpty()) {
                return collect();
            }

            $map = $all->keyBy('id');
            $trail = collect();
            $cursorId = (int) $category->id;

            while ($cursorId && $trail->count() < 20) {
                $node = $map->get($cursorId);
                if (!$node) {
                    break;
                }

                $trail->prepend($node);
                $cursorId = $node->parent_id ? (int) $node->parent_id : 0;
            }

            return $trail;
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
