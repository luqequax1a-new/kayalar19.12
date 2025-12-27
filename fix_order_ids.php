<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING RECOVERED CARTS ===" . PHP_EOL . PHP_EOL;

$order = \Modules\Order\Entities\Order::first();

if ($order) {
    echo "Found Order #" . $order->id . " - Total: " . $order->total->format() . PHP_EOL;
    
    // Update recovered carts with order_id
    $sessionId = 'XNKr4o2JQNNuLRk2MS4XM4G8LuAfXEI8kN2mpDPh';
    
    $updated = \Modules\Cart\Entities\Cart::where('id', 'like', $sessionId . '%')
        ->where('is_recovered', true)
        ->update(['order_id' => $order->id]);
    
    echo "Updated " . $updated . " cart records with order_id" . PHP_EOL;
    
    // Verify
    echo PHP_EOL . "Verification:" . PHP_EOL;
    $carts = \Modules\Cart\Entities\Cart::where('id', 'like', $sessionId . '%')->get();
    foreach($carts as $cart) {
        echo "  " . $cart->id . " - Order ID: " . ($cart->order_id ?? 'NULL') . PHP_EOL;
    }
} else {
    echo "No orders found!" . PHP_EOL;
}
