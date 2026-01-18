<?php

namespace Modules\Brand\Observers;

use Modules\Brand\Entities\Brand;
use Modules\Support\Entities\UrlSlug;

class BrandSlugObserver
{
    /**
     * Handle the Brand "saved" event.
     */
    public function saved(Brand $brand): void
    {
        if ($brand->slug) {
            UrlSlug::reserve($brand->slug, 'brand', $brand->id);
        }
    }

    /**
     * Handle the Brand "deleted" event.
     */
    public function deleted(Brand $brand): void
    {
        UrlSlug::release('brand', $brand->id);
    }
}
