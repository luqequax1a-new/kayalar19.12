<?php

namespace Modules\Page\Observers;

use Modules\Page\Entities\Page;
use Modules\Support\Entities\UrlSlug;

class PageSlugObserver
{
    /**
     * Handle the Page "saved" event.
     */
    public function saved(Page $page): void
    {
        if ($page->slug) {
            UrlSlug::reserve($page->slug, 'page', $page->id);
        }
    }

    /**
     * Handle the Page "deleted" event.
     */
    public function deleted(Page $page): void
    {
        UrlSlug::release('page', $page->id);
    }
}
