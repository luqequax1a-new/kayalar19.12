<?php

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- Checking setting() helper ---\n";

$enabled = setting('storefront_testimonials_enabled');
echo "Value from setting() helper: ";
var_dump($enabled);

$cacheKey = app(\Modules\Setting\Services\SettingsCacheService::class)->cacheKey(locale());
echo "Cache Key: $cacheKey\n";
echo "Cache exists: " . (\Illuminate\Support\Facades\Cache::store('file')->has($cacheKey) ? 'Yes' : 'No') . "\n";

if (\Illuminate\Support\Facades\Cache::store('file')->has($cacheKey)) {
    echo "Clearing cache...\n";
    \Illuminate\Support\Facades\Cache::store('file')->forget($cacheKey);
    echo "Cache cleared.\n";
    $enabledAfter = setting('storefront_testimonials_enabled');
    echo "Value after clear: ";
    var_dump($enabledAfter);
}
