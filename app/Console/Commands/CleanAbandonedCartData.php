<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use Modules\Cart\Entities\Cart;

class CleanAbandonedCartData extends Command
{
    protected $signature = 'cart:clean-abandoned-data {--dry-run : Show what would be cleaned without actually cleaning}';
    protected $description = 'Clean and reset abandoned cart data to fix double counting issues';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('=== Abandoned Cart Data Analysis ===');
        $this->newLine();
        
        // Check current state
        $totalRecovered = Cart::where('is_recovered', true)->count();
        $recoveredWithOrder = Cart::where('is_recovered', true)->whereNotNull('order_id')->count();
        $recoveredWithoutOrder = Cart::where('is_recovered', true)->whereNull('order_id')->count();
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total recovered carts', $totalRecovered],
                ['With order_id', $recoveredWithOrder],
                ['Without order_id (INVALID)', $recoveredWithoutOrder],
            ]
        );
        
        $this->newLine();
        
        // Breakdown by type
        $itemsRecovered = Cart::where('is_recovered', true)
            ->where('id', 'like', '%_cart_items')
            ->count();
        $conditionsRecovered = Cart::where('is_recovered', true)
            ->where('id', 'like', '%_cart_conditions')
            ->count();
        
        $this->table(
            ['Cart Type', 'Recovered Count'],
            [
                ['_cart_items', $itemsRecovered],
                ['_cart_conditions', $conditionsRecovered],
            ]
        );
        
        $this->newLine();
        
        // Find invalid recovered carts (without order_id)
        $invalidCarts = Cart::where('is_recovered', true)
            ->whereNull('order_id')
            ->get();
        
        if ($invalidCarts->count() > 0) {
            $this->warn("Found {$invalidCarts->count()} invalid recovered carts (without order_id)");
            
            if ($dryRun) {
                $this->info('[DRY RUN] Would reset these carts to is_recovered=false');
                $this->table(
                    ['Cart ID', 'Recovered At'],
                    $invalidCarts->map(fn($c) => [$c->id, $c->recovered_at])->take(10)->toArray()
                );
            } else {
                $this->info('Resetting invalid recovered carts...');
                Cart::where('is_recovered', true)
                    ->whereNull('order_id')
                    ->update([
                        'is_recovered' => false,
                        'recovered_at' => null,
                    ]);
                $this->info("✓ Reset {$invalidCarts->count()} invalid carts");
            }
        } else {
            $this->info('✓ No invalid recovered carts found');
        }
        
        $this->newLine();
        
        // Check for duplicate recovered carts (same order_id)
        $duplicates = Cart::where('is_recovered', true)
            ->whereNotNull('order_id')
            ->select('order_id')
            ->groupBy('order_id')
            ->havingRaw('COUNT(*) > 2') // More than 2 means duplicates (should be exactly 2: items + conditions)
            ->get();
        
        if ($duplicates->count() > 0) {
            $this->warn("Found {$duplicates->count()} orders with duplicate cart records");
        } else {
            $this->info('✓ No duplicate cart records found');
        }
        
        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Total recovered carts: {$totalRecovered}");
        $this->info("Valid recovered carts: {$recoveredWithOrder}");
        $this->info("Invalid recovered carts: {$recoveredWithoutOrder}");
        
        if ($dryRun) {
            $this->newLine();
            $this->comment('This was a DRY RUN. No changes were made.');
            $this->comment('Run without --dry-run to actually clean the data.');
        }
        
        return 0;
    }
}
