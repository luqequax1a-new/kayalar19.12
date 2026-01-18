<?php

namespace Modules\Category\Observers;

use Modules\Category\Entities\Category;
use Modules\Support\Entities\UrlSlug;

class CategorySlugObserver
{
    /**
     * Handle the Category "saved" event.
     */
    public function saved(Category $category): void
    {
        if ($category->slug) {
            UrlSlug::reserve($category->slug, 'category', $category->id);
        }
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        UrlSlug::release('category', $category->id);
    }
}
