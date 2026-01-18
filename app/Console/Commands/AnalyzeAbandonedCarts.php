<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use Modules\Cart\Entities\Cart;

class AnalyzeAbandonedCarts extends Command
{
    protected $signature = 'cart:analyze {--days=7 : Number of days to analyze}';
    protected $description = 'Analyze abandoned cart data for debugging';

    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("=== Analyzing Abandoned Carts (Last {$days} days) ===");
        $this->newLine();
        
        // Abandoned carts (only _cart_items)
        $abandoned = Cart::where('is_recovered', false)
            ->where('id', 'like', '%_cart_items')
            ->where('updated_at', '>=', now()->subDays($days))
            ->get();
        
        $this->info("📦 ABANDONED CARTS (_cart_items only):");
        $this->table(
            ['Cart ID', 'Email', 'Updated At', 'Has Order'],
            $abandoned->map(fn($c) => [
                substr($c->id, 0, 30) . '...',
                $c->customer_email ?? 'N/A',
                $c->updated_at->format('Y-m-d H:i'),
                $c->order_id ? 'YES' : 'NO'
            ])->toArray()
        );
        
        $this->newLine();
        
        // Recovered carts (only _cart_items)
        $recovered = Cart::where('is_recovered', true)
            ->where('id', 'like', '%_cart_items')
            ->whereNotNull('order_id')
            ->where('recovered_at', '>=', now()->subDays($days))
            ->with('order')
            ->get();
        
        $this->info("✅ RECOVERED CARTS (_cart_items only):");
        $this->table(
            ['Cart ID', 'Order ID', 'Order Total', 'Recovered At'],
            $recovered->map(fn($c) => [
                substr($c->id, 0, 30) . '...',
                $c->order_id,
                optional($c->order)->total ? \Modules\Support\Money::inDefaultCurrency($c->order->total->amount())->format() : 'N/A',
                $c->recovered_at ? $c->recovered_at->format('Y-m-d H:i') : 'N/A'
            ])->toArray()
        );
        
        $this->newLine();
        
        // Calculate stats
        $totalAbandoned = $abandoned->count();
        $totalRecovered = $recovered->count();
        $recoveredRevenue = $recovered->sum(function ($cart) {
            return optional($cart->order)->total->amount() ?? 0;
        });
        $recoveryRate = $totalAbandoned > 0 ? round(($totalRecovered / $totalAbandoned) * 100, 1) : 0;
        
        $this->info("📊 STATISTICS:");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Abandoned', $totalAbandoned],
                ['Total Recovered', $totalRecovered],
                ['Recovery Rate', "%{$recoveryRate}"],
                ['Recovered Revenue', \Modules\Support\Money::inDefaultCurrency($recoveredRevenue)->format()],
            ]
        );
        
        // Check for ALL carts (including _cart_conditions)
        $this->newLine();
        $this->warn("🔍 DOUBLE-COUNT CHECK (including _cart_conditions):");
        
        $allAbandoned = Cart::where('is_recovered', false)
            ->where('updated_at', '>=', now()->subDays($days))
            ->count();
        
        $allRecovered = Cart::where('is_recovered', true)
            ->whereNotNull('order_id')
            ->where('recovered_at', '>=', now()->subDays($days))
            ->count();
        
        $this->table(
            ['Type', 'Abandoned', 'Recovered'],
            [
                ['Only _cart_items (CORRECT)', $totalAbandoned, $totalRecovered],
                ['All records (WRONG)', $allAbandoned, $allRecovered],
                ['Difference', $allAbandoned - $totalAbandoned, $allRecovered - $totalRecovered],
            ]
        );
        
        return 0;
    }
}
