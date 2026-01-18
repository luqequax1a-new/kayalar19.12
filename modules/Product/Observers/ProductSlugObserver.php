<?php

namespace Modules\Product\Observers;

use Modules\Product\Entities\Product;
use Modules\Support\Entities\UrlSlug;

class ProductSlugObserver
{
    /**
     * Handle the Product "saved" event.
     */
    public function saved(Product $product): void
    {
        if ($product->slug) {
            UrlSlug::reserve($product->slug, 'product', $product->id);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        UrlSlug::release('product', $product->id);
    }
}
