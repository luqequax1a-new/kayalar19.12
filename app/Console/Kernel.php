<?php

namespace FleetCart\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ScaffoldModuleCommand::class,
        Commands\ScaffoldEntityCommand::class,
        Commands\ManualInstallCommand::class,
        Commands\NormalizeMediaPathsCommand::class,
        \Modules\Order\Console\BackfillOrderProductsSnapshots::class,
        \Modules\ProductFeeds\Console\RefreshFeedCommand::class,
    ];


    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('order:send-second-review-requests')->dailyAt('09:00');
    }
}
