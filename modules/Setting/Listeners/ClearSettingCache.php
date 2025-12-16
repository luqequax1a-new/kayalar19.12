<?php

namespace Modules\Setting\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\Setting\Services\SettingsCacheService;

class ClearSettingCache
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle()
    {
        foreach (supported_locale_keys() as $locale) {
            Cache::forget(md5('settings.all:' . $locale));

            try {
                app(SettingsCacheService::class)->forget($locale);
            } catch (\Throwable $e) {
                Cache::store('file')->forget('storefront:settings:' . $locale . ':v1');
            }
        }
    }
}
