<?php

namespace Modules\Question\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Question\Admin\QuestionTabs;

class QuestionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        TabManager::register('questions', QuestionTabs::class);
    }
}
