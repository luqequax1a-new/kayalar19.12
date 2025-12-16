<?php

namespace Modules\Product\Listeners;

use Modules\Product\Entities\Product;
use Modules\Product\Events\ProductViewed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IncrementProductView
{
    /**
     * Handle the event.
     *
     * @param ProductViewed $event
     *
     * @return void
     */
    public function handle(ProductViewed $event)
    {
        $productId = (int) ($event->product->id ?? 0);

        $request = app('request');
        $sessionId = (string) optional($request->session())->getId();
        $ip = (string) $request->ip();

        $fingerprint = $sessionId !== '' ? $sessionId : $ip;
        $key = 'storefront:product_viewed:' . $productId . ':' . md5($fingerprint) . ':v1';

        $shouldIncrement = Cache::store('file')->add($key, 1, now()->addMinutes(10));

        $isProfiling = false;
        try {
            $isProfiling = (bool) $request->query('__profile');
        } catch (\Throwable $e) {
            $isProfiling = false;
        }

        if ($isProfiling) {
            try {
                Log::channel('single')->info('PROFILE: product viewed throttle', [
                    'product_id' => $productId,
                    'cache_key' => $key,
                    'store' => 'file',
                    'hit' => !$shouldIncrement,
                ]);
            } catch (\Throwable $e) {
            }
        }

        if ($shouldIncrement) {
            Product::withoutTimestamps(fn () => $event->product->increment('viewed'));
        }
    }
}
