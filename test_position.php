<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test 1: Update a product
$product = \Modules\Product\Entities\Product::withoutGlobalScope('active')->first();
echo "Product ID: {$product->id}\n";
echo "Before: {$product->main_page_position}\n";

$product->main_page_position = 999;
$product->save();

echo "After save: {$product->main_page_position}\n";

// Test 2: Check database
$result = DB::select('SELECT id, main_page_position FROM products WHERE id = ?', [$product->id]);
echo "Database value: {$result[0]->main_page_position}\n";
