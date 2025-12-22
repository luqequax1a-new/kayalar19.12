<?php

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Review\Entities\Review;
use Modules\Setting\Entities\Setting;

echo "--- Verifying DB Content ---\n";

// Check Setting
$setting = Setting::where('key', 'storefront_testimonials_enabled')->first();
echo "Setting 'storefront_testimonials_enabled': " . ($setting ? $setting->plain_value : 'NOT FOUND') . "\n";

// Check Reviews Raw (ignoring scopes)
$rawCount = \Illuminate\Support\Facades\DB::table('reviews')->count();
echo "Total reviews in DB (raw): $rawCount\n";

// Check Reviews via Model (with scopes)
$modelCount = Review::count();
echo "Total reviews via Model (approved scope?): $modelCount\n";

// Check Qualified Reviews
$qualifiedQuery = Review::where('rating', '>=', 4)->whereRaw('LENGTH(comment) > 20');
echo "Qualified reviews query SQL: " . $qualifiedQuery->toSql() . "\n";
$qualifiedCount = $qualifiedQuery->count();
echo "Qualified reviews count: $qualifiedCount\n";

if ($qualifiedCount > 0) {
    echo "\nSample Review:\n";
    $review = $qualifiedQuery->first();
    echo "ID: " . $review->id . "\n";
    echo "Rating: " . $review->rating . "\n";
    echo "Comment Length: " . strlen($review->comment) . "\n";
    echo "Is Approved: " . ($review->is_approved ? 'Yes' : 'No') . "\n";
    echo "Product ID: " . $review->product_id . "\n";
    echo "Product Relation: " . ($review->product ? 'Found' : 'NULL') . "\n";
} else {
    echo "\nNo qualified reviews found. Dumping all reviews:\n";
    $allReviews = Review::withoutGlobalScopes()->get();
    foreach ($allReviews as $r) {
        echo "ID: {$r->id}, Rating: {$r->rating}, Length: " . strlen($r->comment) . ", Approved: {$r->is_approved}\n";
    }
}
