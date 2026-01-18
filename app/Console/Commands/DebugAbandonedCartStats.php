<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use Modules\Cart\Entities\Cart;

class DebugAbandonedCartStats extends Command
{
    protected $signature = 'cart:debug-stats';
    protected $description = 'Debug abandoned cart statistics to find inconsistencies';

    public function handle()
    {
        $this->info('=== ABANDONED CART STATISTICS DEBUG ===');
        $this->newLine();

        // 1. Total recovered carts (all records)
        $totalRecoveredAll = Cart::where('is_recovered', true)
            ->where('id', 'like', '%_cart_items')
            ->whereNotNull('order_id')
            ->where('recovered_at', '>=', now()->subDays(30))
            ->count();

        $this->info("Total recovered cart records (last 30 days): {$totalRecoveredAll}");

        // 2. Unique order_ids (what we should show)
        $uniqueOrderIds = Cart::where('is_recovered', true)
            ->where('id', 'like', '%_cart_items')
            ->whereNotNull('order_id')
            ->where('recovered_at', '>=', now()->subDays(30))
            ->distinct()
            ->count('order_id');

        $this->info("Unique order_ids (correct count): {$uniqueOrderIds}");
        $this->newLine();

        // 3. Show all recovered carts with details
        $recoveredCarts = Cart::where('is_recovered', true)
            ->where('id', 'like', '%_cart_items')
            ->whereNotNull('order_id')
            ->where('recovered_at', '>=', now()->subDays(30))
            ->with('order')
            ->get();

        $this->info("Detailed recovered carts:");
        $this->table(
            ['Cart ID', 'Order ID', 'Email', 'Recovered At', 'Order Total'],
            $recoveredCarts->map(function ($cart) {
                return [
                    substr($cart->id, 0, 30) . '...',
                    $cart->order_id,
                    $cart->customer_email,
                    $cart->recovered_at->format('Y-m-d H:i'),
                    optional($cart->order)->total ? $cart->order->total->format() : 'N/A',
                ];
            })->toArray()
        );

        $this->newLine();

        // 4. Check for duplicate order_ids
        $duplicates = Cart::where('is_recovered', true)
            ->where('id', 'like', '%_cart_items')
            ->whereNotNull('order_id')
            ->where('recovered_at', '>=', now()->subDays(30))
            ->select('order_id')
            ->groupBy('order_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->count() > 0) {
            $this->warn("Found {$duplicates->count()} order_ids with multiple carts!");
            foreach ($duplicates as $dup) {
                $carts = Cart::where('order_id', $dup->order_id)
                    ->where('is_recovered', true)
                    ->where('id', 'like', '%_cart_items')
                    ->get();
                
                $this->error("Order ID {$dup->order_id} has {$carts->count()} carts:");
                foreach ($carts as $c) {
                    $this->line("  - Cart: " . substr($c->id, 0, 40) . "... | Email: {$c->customer_email}");
                }
            }
        } else {
            $this->info("✓ No duplicate order_ids found");
        }

        $this->newLine();

        // 5. Calculate revenue correctly
        $seenOrderIds = [];
        $totalRevenue = 0;
        
        foreach ($recoveredCarts as $cart) {
            if (!in_array($cart->order_id, $seenOrderIds)) {
                $seenOrderIds[] = $cart->order_id;
                $totalRevenue += optional($cart->order)->total->amount() ?? 0;
            }
        }

        $this->info("Total revenue (unique orders): " . \Modules\Support\Money::inDefaultCurrency($totalRevenue)->format());

        $this->newLine();
        $this->info('=== SUMMARY ===');
        $this->info("Records in DB: {$totalRecoveredAll}");
        $this->info("Unique orders: {$uniqueOrderIds}");
        $this->info("Difference: " . ($totalRecoveredAll - $uniqueOrderIds));
        
        if ($totalRecoveredAll != $uniqueOrderIds) {
            $this->warn("⚠ There IS a discrepancy between total records and unique orders!");
        } else {
            $this->info("✓ No discrepancy - counts match");
        }

        return 0;
    }
}
