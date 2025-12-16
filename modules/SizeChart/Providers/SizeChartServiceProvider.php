<?php

namespace Modules\SizeChart\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\SizeChart\Admin\SizeChartTabs;
use Modules\SizeChart\Services\SizeChartResolver;
use Modules\Admin\Ui\Facades\TabManager;

class SizeChartServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'size_chart');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'size_chart');

        TabManager::register('size_charts', SizeChartTabs::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/permissions.php',
            'fleetcart.modules.sizechart.permissions'
        );

        $this->app->singleton(SizeChartResolver::class);
    }
}
