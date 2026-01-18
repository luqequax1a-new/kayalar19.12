<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use Modules\Cart\Entities\Cart;

class DebugCartData extends Command
{
    protected $signature = 'cart:debug {cart_id}';
    protected $description = 'Debug cart data structure';

    public function handle()
    {
        $cartId = $this->argument('cart_id');
        
        $cart = Cart::where('id', $cartId)->first();
        
        if (!$cart) {
            $this->error("Cart not found: {$cartId}");
            return 1;
        }
        
        $this->info("=== Cart Debug Info ===");
        $this->info("Cart ID: {$cart->id}");
        $this->info("Customer Email: " . ($cart->customer_email ?? 'N/A'));
        $this->info("Updated At: " . $cart->updated_at);
        $this->newLine();
        
        $this->info("=== Raw Data ===");
        $rawData = \DB::table('carts')->where('id', $cartId)->value('data');
        $this->line("Raw data length: " . strlen($rawData ?? ''));
        $this->line("Raw data (first 500 chars): " . substr($rawData ?? '', 0, 500));
        $this->newLine();
        
        $this->info("=== Unserialized Data ===");
        $data = $cart->data;
        $this->line("Data type: " . gettype($data));
        
        if (is_array($data)) {
            $this->line("Array count: " . count($data));
            foreach ($data as $key => $item) {
                $this->line("Item key: {$key}");
                $this->line("Item type: " . gettype($item));
                
                if (is_object($item)) {
                    $this->line("Item class: " . get_class($item));
                    $itemArray = (array) $item;
                } else {
                    $itemArray = $item;
                }
                
                $this->table(
                    ['Property', 'Value'],
                    [
                        ['id', $itemArray['id'] ?? 'N/A'],
                        ['name', $itemArray['name'] ?? 'N/A'],
                        ['quantity', $itemArray['quantity'] ?? 'N/A'],
                        ['price', $itemArray['price'] ?? 'N/A'],
                    ]
                );
            }
        } elseif ($data instanceof \Illuminate\Support\Collection) {
            $this->line("Collection count: " . $data->count());
            foreach ($data as $key => $item) {
                $this->line("Item key: {$key}");
                $this->line("Item: " . json_encode($item, JSON_PRETTY_PRINT));
            }
        } else {
            $this->line("Data: " . print_r($data, true));
        }
        
        return 0;
    }
}
