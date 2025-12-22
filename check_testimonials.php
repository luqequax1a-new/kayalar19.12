<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Review\Entities\Review;
use Illuminate\Support\Facades\DB;

echo "Checking Testimonials Availability...\n";

// 1. Check Setting
$enabled = setting('storefront_testimonials_enabled');
echo "Setting 'storefront_testimonials_enabled': " . ($enabled ? 'TRUE' : 'FALSE') . "\n";

// 2. Check Reviews Count Total
$total = Review::count();
echo "Total Reviews in DB: " . $total . "\n";

// 3. Check Reviews meeting criteria
$qualified = Review::where('rating', '>=', 4)
    ->whereRaw('LENGTH(comment) > 20')
    ->count();
echo "Qualified Reviews (Rating >= 4, Length > 20): " . $qualified . "\n";

// 4. Check if approved scope is affecting
// Note: Global scope might be applied by default.
$qualifiedApproved = Review::where('rating', '>=', 4)
    ->whereRaw('LENGTH(comment) > 20')
    ->where('is_approved', true)
    ->count();
echo "Qualified & Approved Reviews: " . $qualifiedApproved . "\n";
