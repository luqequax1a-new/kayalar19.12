<?php

namespace Modules\Analytics\Listeners;

use Modules\Checkout\Events\OrderPlaced;
use Modules\Analytics\Services\AnalyticsService;
use Illuminate\Support\Facades\Log;

class TrackOrderPlaced
{
    protected $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        try {
            $this->analytics->trackPurchase($event->order);
            
            Log::info('Analytics: Purchase tracked', [
                'order_id' => $event->order->id,
                'total' => $event->order->total->amount()
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics: Failed to track purchase', [
                'order_id' => $event->order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
