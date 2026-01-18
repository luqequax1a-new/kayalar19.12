<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use FleetCart\Models\AdminNotification;
use Modules\Cart\Entities\Cart;

class CheckNotifications extends Command
{
    protected $signature = 'notifications:check';
    protected $description = 'Check notification system status';

    public function handle()
    {
        $this->info('=== NOTIFICATION SYSTEM CHECK ===');
        $this->newLine();
        
        // Check total notifications
        $total = AdminNotification::count();
        $this->info("Total notifications: {$total}");
        
        // Check by type
        $types = ['new_order', 'abandoned_cart', 'cart_recovered', 'new_customer', 'low_stock'];
        foreach ($types as $type) {
            $count = AdminNotification::where('type', $type)->count();
            $this->line("  - {$type}: {$count}");
        }
        
        $this->newLine();
        
        // Check abandoned carts
        $abandonedCarts = Cart::where('id', 'like', '%_cart_items')
            ->where('is_recovered', false)
            ->where('updated_at', '<=', now()->subHours(1))
            ->count();
        $this->info("Abandoned carts (>1h old): {$abandonedCarts}");
        
        // Check recovered carts
        $recoveredCarts = Cart::where('id', 'like', '%_cart_items')
            ->where('is_recovered', true)
            ->count();
        $this->info("Recovered carts: {$recoveredCarts}");
        
        $this->newLine();
        
        // Show recent notifications
        $recent = AdminNotification::orderBy('created_at', 'desc')->limit(5)->get();
        if ($recent->count() > 0) {
            $this->info('Recent notifications:');
            foreach ($recent as $notif) {
                $this->line("  [{$notif->type}] {$notif->title} - {$notif->created_at->diffForHumans()}");
            }
        } else {
            $this->warn('No notifications found!');
        }
        
        return 0;
    }
}
