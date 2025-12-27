<?php

namespace Modules\Admin\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\Checkout\Events\OrderPlaced;

class InvalidateAnalyticsCache
{
    /**
     * Handle the event.
     *
     * @param OrderPlaced $event
     * @return void
     */
    public function handle(OrderPlaced $event): void
    {
        // Clear all analytics cache when a new order is placed
        $patterns = [
            'dashboard_analytics_*',
        ];

        foreach ($patterns as $pattern) {
            // Clear cache keys matching pattern
            // Note: This is a simple implementation. For production with Redis,
            // you might want to use Cache::tags() or a more sophisticated approach
            $this->clearCacheByPattern($pattern);
        }
    }

    /**
     * Clear cache by pattern
     */
    private function clearCacheByPattern(string $pattern): void
    {
        try {
            // For file/database cache drivers, we'll clear specific known keys
            $presets = ['today', 'yesterday', '7', '14', '30', 'all'];
            $limits = [5, 10, 15, 20];
            
            foreach ($presets as $preset) {
                foreach ($limits as $limit) {
                    $hours = range(0, 23);
                    foreach ($hours as $hour) {
                        $date = now()->format('Y-m-d');
                        $cacheKey = "dashboard_analytics_{$preset}_{$limit}_{$date}_" . str_pad($hour, 2, '0', STR_PAD_LEFT);
                        Cache::forget($cacheKey);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to clear analytics cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
