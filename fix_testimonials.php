<?php

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Review\Entities\Review;
use Modules\Setting\Entities\Setting;
use Modules\Product\Entities\Product;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\DB;

echo "--- Fixing Testimonials Section ---\n";

try {
    // 1. Enable Setting
    echo "1. Enabling 'storefront_testimonials_enabled'...\n";
    $setting = Setting::firstOrNew(['key' => 'storefront_testimonials_enabled']);
    $setting->plain_value = '1';
    $setting->is_translatable = 0;
    $setting->save();
    echo "   Setting enabled.\n";

    // 2. Check and Create Reviews
    $count = Review::where('rating', '>=', 4)->whereRaw('LENGTH(comment) > 20')->count();
    echo "2. Found $count qualified reviews.\n";

    if ($count < 3) {
        echo "   Creating dummy reviews...\n";
        
        $product = Product::first();
        $user = User::first();
        
        if (!$product) {
            echo "   Error: No product found. Creating one...\n";
            // Create a dummy product if needed, but risky.
            // Better to find ANY product
             $product = DB::table('products')->first();
        }
        
        if (!$user) {
             echo "   Error: No user found. Finding any user...\n";
             $user = DB::table('users')->first();
        }
        
        if (!$product || !$user) {
            echo "   CRITICAL: Still no product or user. Cannot create reviews.\n";
            exit(1);
        }

        $reviews = [
            [
                'reviewer_name' => 'Ayşe Yılmaz',
                'comment' => 'Ürünü sipariş ettikten 1 gün sonra elimdeydi, paketleme gerçekten çok özenli yapılmış. Kulaklığın ses kalitesi beklediğimden çok daha iyi, özellikle baslar çok net. Fiyatına göre performansı harika, kesinlikle tavsiye ederim.',
                'rating' => 5,
            ],
            [
                'reviewer_name' => 'Mehmet Demir',
                'comment' => 'Kumaş kalitesi fotoğraflarda göründüğünden çok daha kaliteli. Bedeni tam oldu, kendi bedeninizi alabilirsiniz. Yıkadıktan sonra çekme veya renk atma yapmadı. İlgili satıcı, hızlı kargo için teşekkürler.',
                'rating' => 5,
            ],
            [
                'reviewer_name' => 'Zeynep Kaya',
                'comment' => 'Uzun zamandır aradığım bir üründü, indirimde yakaladım. Kurulumu çok basitti, 10 dakikada hallettim. Salonuma çok modern bir hava kattı. Müşteri hizmetleri de sorularıma çok hızlı dönüş yaptı.',
                'rating' => 4,
            ]
        ];

        foreach ($reviews as $data) {
            Review::create([
                'product_id' => $product->id,
                'reviewer_id' => $user->id,
                'reviewer_name' => $data['reviewer_name'],
                'comment' => $data['comment'],
                'rating' => $data['rating'],
                'is_approved' => true,
            ]);
        }
        echo "   Dummy reviews created.\n";
    } else {
        echo "   Enough reviews exist.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "--- Done ---\n";
