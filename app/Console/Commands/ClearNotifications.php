<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use FleetCart\Models\AdminNotification;

class ClearNotifications extends Command
{
    protected $signature = 'notifications:clear';
    protected $description = 'Clear all notifications';

    public function handle()
    {
        $count = AdminNotification::count();
        AdminNotification::truncate();
        $this->info("Deleted {$count} notifications");
        
        return 0;
    }
}
