<?php

namespace Modules\Storefront\Http\ViewComposers;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Modules\Support\Money;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\ProductVariant;

class ProductIndexPageComposer
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose($view)
    {
        $view->with([
            'categories' => $this->categories(),
            'minPrice' => $this->minPrice(),
            'maxPrice' => $this->maxPrice(),
            'latestProducts' => $this->latestProducts(),
        ]);
    }


    private function categories()
    {
        $cacheKey = $this->cacheKey('categories', locale());

        return Cache::remember($cacheKey, now()->addMinutes(60), function () {
            return Category::tree();
        });
    }


    private function minPrice()
    {
        $cacheKey = $this->cacheKey('min_price', currency());

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $minProductPrice = Product::min('selling_price');
            $minVariantPrice = ProductVariant::min('selling_price');

            $candidates = array_filter([$minProductPrice, $minVariantPrice], function ($value) {
                return !is_null($value);
            });

            $minPrice = empty($candidates) ? 0 : min($candidates);

            return Money::inDefaultCurrency($minPrice)
                ->convertToCurrentCurrency()
                ->floor()
                ->amount();
        });
    }


    private function maxPrice()
    {
        $cacheKey = $this->cacheKey('max_price', currency());

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $maxProductPrice = Product::max('selling_price');
            $maxVariantPrice = ProductVariant::max('selling_price');

            $candidates = array_filter([$maxProductPrice, $maxVariantPrice], function ($value) {
                return !is_null($value);
            });

            $maxPrice = empty($candidates) ? 0 : max($candidates);

            return Money::inDefaultCurrency($maxPrice)
                ->convertToCurrentCurrency()
                ->ceil()
                ->amount();
        });
    }


    private function latestProducts()
    {
        $cacheKey = $this->cacheKey('latest_products', locale() . '::' . currency());

        return Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return Product::forCard()->take(5)->latest()->get()->map->clean();
        });
    }


    private function cacheKey(string $suffix, ?string $extra = null): string
    {
        return sprintf(
            'storefront_product_index_%s%s',
            $suffix,
            $extra ? ('_' . $extra) : ''
        );
    }
}
