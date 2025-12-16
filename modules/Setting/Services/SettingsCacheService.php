<?php

namespace Modules\Setting\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Entities\SettingTranslation;

class SettingsCacheService
{
    public function cacheKey(string $locale): string
    {
        return 'storefront:settings:' . $locale . ':v1';
    }


    public function all(string $locale): Collection
    {
        $key = $this->cacheKey($locale);

        $isProfiling = false;
        try {
            $isProfiling = (bool) app('request')->query('__profile');
        } catch (\Throwable $e) {
            $isProfiling = false;
        }

        if ($isProfiling) {
            try {
                Log::channel('single')->info('PROFILE: settings cache', [
                    'cache_key' => $key,
                    'store' => 'file',
                    'hit' => Cache::store('file')->has($key),
                ]);
            } catch (\Throwable $e) {
            }
        }

        return Cache::store('file')->remember($key, now()->addMinutes(10), function () use ($locale) {
            $settings = Setting::query()
                ->with([])
                ->select(['id', 'key', 'is_translatable', 'plain_value'])
                ->get();

            $translatableSettingIds = $settings
                ->where('is_translatable', true)
                ->pluck('id')
                ->values();

            if ($translatableSettingIds->isNotEmpty()) {
                $fallbackLocale = (string) config('app.fallback_locale');
                $locales = array_values(array_unique(array_filter([$locale, $fallbackLocale])));

                $translationsBySettingId = SettingTranslation::query()
                    ->whereIn('setting_id', $translatableSettingIds)
                    ->whereIn('locale', $locales)
                    ->get()
                    ->groupBy('setting_id');

                $settings->each(function (Setting $setting) use ($translationsBySettingId) {
                    if ($setting->is_translatable) {
                        $setting->setRelation('translations', $translationsBySettingId->get($setting->id, collect()));
                    }
                });
            }

            return $settings->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            });
        });
    }


    public function get(string $key, $default = null, ?string $locale = null)
    {
        $locale = $locale ?: locale();
        $settings = $this->all($locale);

        if ($settings->has($key)) {
            $value = $settings->get($key);

            return $value === null ? $default : $value;
        }

        return $default;
    }


    public function forget(string $locale): void
    {
        Cache::store('file')->forget($this->cacheKey($locale));
    }
}
