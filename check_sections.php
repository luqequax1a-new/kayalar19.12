<?php

require __DIR__ . '/bootstrap/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sections = setting('storefront_home_page_sections');

echo "Storefront Home Page Sections:\n";
print_r($sections);

$enabled = setting('storefront_testimonials_enabled');
echo "\nTestimonials Enabled: " . ($enabled ? 'Yes' : 'No') . "\n";
