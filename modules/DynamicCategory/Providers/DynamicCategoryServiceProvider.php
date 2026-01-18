<?php

namespace Modules\DynamicCategory\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\DynamicCategory\Admin\DynamicCategoryTabs;
use Modules\DynamicCategory\Services\DynamicCategoryRuleQueryBuilder;
use Modules\DynamicCategory\Services\Rules\PriceRuleApplier;
use Modules\DynamicCategory\Services\Rules\TagRuleApplier;
use Modules\DynamicCategory\Services\Rules\DiscountedRuleApplier;
use Modules\DynamicCategory\Services\Rules\CreatedAtRuleApplier;

class DynamicCategoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'dynamic_category');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'dynamic_category');

        TabManager::register('dynamic_categories', DynamicCategoryTabs::class);
    }

    public function register(): void
    {
        // Ensure permissions are visible to User\Repositories\Permission
        // Core ModuleServiceProvider already merges permissions under
        // fleetcart.modules.dynamic_category.permissions (using alias),
        // but Permission::all() looks for fleetcart.modules.dynamiccategory.permissions
        // (using strtolower(Module::getName())). We mirror the config there as well.

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/permissions.php',
            'fleetcart.modules.dynamiccategory.permissions'
        );

        $this->app->singleton(DynamicCategoryRuleQueryBuilder::class, function () {
            return new DynamicCategoryRuleQueryBuilder([
                new PriceRuleApplier(),
                new TagRuleApplier(),
                new \Modules\DynamicCategory\Services\Rules\BrandRuleApplier(),
                new DiscountedRuleApplier(),
                new CreatedAtRuleApplier(),
            ]);
        });
    }
}
