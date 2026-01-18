<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use Modules\Cart\Entities\Cart;

class ResetAbandonedCartData extends Command
{
    protected $signature = 'cart:reset-abandoned {--force : Skip confirmation}';
    protected $description = 'Reset all abandoned cart data (for testing/cleanup)';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Bu işlem TÜM terk edilmiş sepet verilerini silecek. Emin misiniz?')) {
                $this->info('İşlem iptal edildi.');
                return 0;
            }
        }

        $this->info('=== Abandoned Cart Data Reset ===');
        $this->newLine();

        // Count before
        $totalCarts = Cart::count();
        $abandonedCarts = Cart::where('id', 'like', '%_cart_items')
            ->where('updated_at', '<', now()->subHours(1))
            ->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total cart records', $totalCarts],
                ['Abandoned carts (>1 hour old)', $abandonedCarts],
            ]
        );

        $this->newLine();

        // Delete old abandoned carts (older than 1 hour)
        $this->info('Deleting abandoned carts older than 1 hour...');
        
        $deletedItems = Cart::where('id', 'like', '%_cart_items')
            ->where('updated_at', '<', now()->subHours(1))
            ->delete();

        $deletedConditions = Cart::where('id', 'like', '%_cart_conditions')
            ->where('updated_at', '<', now()->subHours(1))
            ->delete();

        $totalDeleted = $deletedItems + $deletedConditions;

        $this->newLine();
        $this->info("✓ Deleted {$deletedItems} _cart_items records");
        $this->info("✓ Deleted {$deletedConditions} _cart_conditions records");
        $this->info("✓ Total deleted: {$totalDeleted} records");

        $this->newLine();

        // Count after
        $remainingCarts = Cart::count();
        $this->info("Remaining cart records: {$remainingCarts}");

        $this->newLine();
        $this->info('=== Reset Complete ===');

        return 0;
    }
}
