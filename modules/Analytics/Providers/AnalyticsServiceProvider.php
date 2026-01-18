<?php

namespace Modules\Analytics\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Analytics\Services\AnalyticsService;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Analytics\Listeners\TrackOrderPlaced;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(OrderPlaced::class, TrackOrderPlaced::class);

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'analytics');
    }
}
