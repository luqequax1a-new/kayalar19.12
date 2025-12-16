<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Support\Carbon;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;

class GoogleFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): Response
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.google.enabled', true)) {
            abort(404);
        }
        $channel = 'google';

        if ($this->cache->isEnabled() && ! $this->cache->shouldRegenerate($channel)) {
            $cached = $this->cache->readCache($channel);

            if ($cached !== null) {
                return new Response($cached, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
            }
        }

        if ($this->cache->isEnabled()) {
            $this->regenerateCache();
            $cached = $this->cache->readCache($channel);

            return new Response((string) $cached, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
        }

        return $this->generate();
    }

    public function generate(): Response
    {
        $storeName = (string) setting('store_name');
        $storeUrl = url('/');
        $storeTagline = (string) (setting('store_tagline') ?: $storeName);

        $missingBehavior = (string) setting('product_feeds.google.missing_identifier_behavior', 'mpn_from_id');

        $shippingCountry = (string) setting('product_feeds.google.shipping_country', 'TR');
        $shippingService = (string) setting('product_feeds.google.shipping_service', 'Standard');
        $shippingPrice = (float) setting('product_feeds.google.shipping_price', 0);
        $freeShippingThreshold = setting('product_feeds.google.free_shipping_threshold');
        $priceIncludesVat = (bool) setting('product_feeds.google.price_includes_vat', true);
        $currency = (string) setting('product_feeds.google.currency', 'TRY');

        return new StreamedResponse(function () use ($storeName, $storeUrl, $storeTagline, $shippingCountry, $shippingService, $shippingPrice, $freeShippingThreshold, $priceIncludesVat, $currency, $missingBehavior) {
            $itemsCount = 0;

            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            echo "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">\n";
            echo "<channel>\n";
            echo '<title>' . e($storeName) . "</title>\n";
            echo '<link>' . e($storeUrl) . "</link>\n";
            echo '<description>' . e($storeTagline) . "</description>\n";

            $this->feeds->streamNormalizedItemsForFeed('google', function (array $row) use (&$itemsCount, $shippingCountry, $shippingService, $shippingPrice, $freeShippingThreshold, $priceIncludesVat, $currency, $missingBehavior) {
                $itemsCount++;

                $sku = (string) ($row['sku'] ?? '');
                $hasSku = $sku !== '';
                $id = (string) ($hasSku ? $sku : ($row['id'] ?? ''));
                $isVariant = ($row['item_group_id'] ?? null) !== null && (string) $row['item_group_id'] !== (string) $row['id'];
                $groupId = (string) ($row['item_group_id'] ?? $row['id']);

                $title = (string) ($row['title'] ?? '');
                $description = (string) ($row['description'] ?? '');

                if ($description === '') {
                    $description = $title !== '' ? $title : ('ID-' . (string) ($row['id'] ?? ''));
                }

                $link = (string) ($row['url'] ?? '');
                $image = (string) ($row['main_image'] ?? '');
                $additional = (array) ($row['additional_images'] ?? []);

                $availability = (string) ($row['availability'] ?? 'in stock');

                $vatRate = (int) ($row['vat_rate'] ?? 0);
                $basePrice = (float) ($row['price'] ?? 0);
                $baseSale = $row['sale_price'];
                $salePrice = is_null($baseSale) ? null : (float) $baseSale;

                $priceOut = $priceIncludesVat ? $basePrice : ($basePrice * (1 + ($vatRate / 100)));
                $saleOut = null;
                if (! is_null($salePrice)) {
                    $saleOut = $priceIncludesVat ? $salePrice : ($salePrice * (1 + ($vatRate / 100)));
                }

                $priceStr = number_format((float) $priceOut, 2, '.', '') . ' ' . $currency;
                $saleStr = ! is_null($saleOut) ? (number_format((float) $saleOut, 2, '.', '') . ' ' . $currency) : null;

                $brand = trim((string) ($row['brand'] ?? ''));
                if ($brand === '') {
                    $brand = $storeName;
                }

                $googleCategory = (string) ($row['google_category'] ?? '');
                $productType = (string) ($row['product_type'] ?? ($row['category_path'] ?? ''));

                $mpn = $hasSku ? trim($sku) : '';
                $identifierExists = $hasSku ? 'yes' : 'no';

                echo "<item>\n";
                echo '<g:id>' . e($id) . "</g:id>\n";

                if ($isVariant) {
                    echo '<g:item_group_id>' . e($groupId) . "</g:item_group_id>\n";
                }

                echo '<title>' . e($title) . "</title>\n";
                echo '<description>' . e($description) . "</description>\n";
                echo '<link>' . e($link) . "</link>\n";

                if ($image !== '') {
                    echo '<g:image_link>' . e($image) . "</g:image_link>\n";
                }

                $additional = array_slice($additional, 0, 10);
                foreach ($additional as $img) {
                    if (! empty($img)) {
                        echo '<g:additional_image_link>' . e($img) . "</g:additional_image_link>\n";
                    }
                }

                echo '<g:availability>' . e($availability) . "</g:availability>\n";
                echo "<g:condition>new</g:condition>\n";
                echo '<g:price>' . e($priceStr) . "</g:price>\n";

                if (! is_null($saleStr)) {
                    echo '<g:sale_price>' . e($saleStr) . "</g:sale_price>\n";

                    $start = $row['sale_price_start'] ?? null;
                    $end = $row['sale_price_end'] ?? null;
                    if ($start && $end) {
                        try {
                            $startIso = Carbon::parse($start)->toIso8601String();
                            $endIso = Carbon::parse($end)->toIso8601String();
                            echo '<g:sale_price_effective_date>' . e($startIso . '/' . $endIso) . "</g:sale_price_effective_date>\n";
                        } catch (\Throwable $e) {
                        }
                    }
                }

                echo '<g:brand>' . e($brand) . "</g:brand>\n";

                if ($identifierExists === 'yes' && $mpn !== '') {
                    echo '<g:mpn>' . e($mpn) . "</g:mpn>\n";
                }
                echo '<g:identifier_exists>' . e($identifierExists) . "</g:identifier_exists>\n";

                if ($googleCategory !== '') {
                    echo '<g:google_product_category>' . e($googleCategory) . "</g:google_product_category>\n";
                }

                if ($productType !== '') {
                    echo '<g:product_type>' . e($productType) . "</g:product_type>\n";
                }

                if ($vatRate > 0) {
                    echo "<g:tax>\n";
                    echo '<g:country>' . e($shippingCountry) . "</g:country>\n";
                    echo '<g:rate>' . e((string) $vatRate) . "</g:rate>\n";
                    echo "</g:tax>\n";
                }

                $shippingOut = $shippingPrice;
                try {
                    if (! is_null($freeShippingThreshold) && (float) $freeShippingThreshold > 0) {
                        if ((float) $priceOut >= (float) $freeShippingThreshold) {
                            $shippingOut = 0.0;
                        }
                    }
                } catch (\Throwable $e) {
                }

                echo "<g:shipping>\n";
                echo '<g:country>' . e($shippingCountry) . "</g:country>\n";
                echo '<g:service>' . e($shippingService) . "</g:service>\n";
                echo '<g:price>' . e(number_format((float) $shippingOut, 2, '.', '') . ' ' . $currency) . "</g:price>\n";
                echo "</g:shipping>\n";

                echo "</item>\n";
            });

            echo "</channel>\n";
            echo "</rss>\n";
        }, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function regenerateCache(): void
    {
        $channel = 'google';

        $storeName = (string) setting('store_name');
        $storeUrl = url('/');
        $storeTagline = (string) (setting('store_tagline') ?: $storeName);

        $missingBehavior = (string) setting('product_feeds.google.missing_identifier_behavior', 'mpn_from_id');

        $shippingCountry = (string) setting('product_feeds.google.shipping_country', 'TR');
        $shippingService = (string) setting('product_feeds.google.shipping_service', 'Standard');
        $shippingPrice = (float) setting('product_feeds.google.shipping_price', 0);
        $freeShippingThreshold = setting('product_feeds.google.free_shipping_threshold');
        $priceIncludesVat = (bool) setting('product_feeds.google.price_includes_vat', true);
        $currency = (string) setting('product_feeds.google.currency', 'TRY');

        $meta = ['items_count' => 0];

        $appUrl = (string) config('app.url');
        $appHost = strtolower((string) (parse_url($appUrl, PHP_URL_HOST) ?? ''));
        if ($appHost === '127.0.0.1' || $appHost === 'localhost') {
            $meta['warnings'] = array_values(array_unique(array_merge((array) ($meta['warnings'] ?? []), ['APP_URL is localhost'])));
        }

        $this->cache->writeCacheAtomic($channel, function ($handle) use (&$meta, $storeName, $storeUrl, $storeTagline, $shippingCountry, $shippingService, $shippingPrice, $freeShippingThreshold, $priceIncludesVat, $currency, $missingBehavior) {
            fwrite($handle, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
            fwrite($handle, "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">\n");
            fwrite($handle, "<channel>\n");
            fwrite($handle, '<title>' . e($storeName) . "</title>\n");
            fwrite($handle, '<link>' . e($storeUrl) . "</link>\n");
            fwrite($handle, '<description>' . e($storeTagline) . "</description>\n");

            $this->feeds->streamNormalizedItemsForFeed('google', function (array $row) use ($handle, &$meta, $shippingCountry, $shippingService, $shippingPrice, $freeShippingThreshold, $priceIncludesVat, $currency, $missingBehavior) {
                $meta['items_count'] = (int) ($meta['items_count'] ?? 0) + 1;

                $sku = (string) ($row['sku'] ?? '');
                $hasSku = $sku !== '';
                $id = (string) ($hasSku ? $sku : ($row['id'] ?? ''));
                $isVariant = ($row['item_group_id'] ?? null) !== null && (string) $row['item_group_id'] !== (string) $row['id'];
                $groupId = (string) ($row['item_group_id'] ?? $row['id']);

                $title = (string) ($row['title'] ?? '');
                $description = (string) ($row['description'] ?? '');

                if ($description === '') {
                    $description = $title !== '' ? $title : ('ID-' . (string) ($row['id'] ?? ''));
                }

                $link = (string) ($row['url'] ?? '');
                $image = (string) ($row['main_image'] ?? '');
                $additional = (array) ($row['additional_images'] ?? []);

                $availability = (string) ($row['availability'] ?? 'in stock');

                $vatRate = (int) ($row['vat_rate'] ?? (int) setting('product_feeds.google.default_vat_rate', 20));
                $basePrice = (float) ($row['price'] ?? 0);
                $baseSale = $row['sale_price'];
                $salePrice = is_null($baseSale) ? null : (float) $baseSale;

                $priceOut = $priceIncludesVat ? $basePrice : ($basePrice * (1 + ($vatRate / 100)));
                $saleOut = null;
                if (! is_null($salePrice)) {
                    $saleOut = $priceIncludesVat ? $salePrice : ($salePrice * (1 + ($vatRate / 100)));
                }

                $priceStr = number_format((float) $priceOut, 2, '.', '') . ' ' . $currency;
                $saleStr = ! is_null($saleOut) ? (number_format((float) $saleOut, 2, '.', '') . ' ' . $currency) : null;

                $brand = trim((string) ($row['brand'] ?? ''));
                if ($brand === '') {
                    $brand = $storeName;
                }
                $googleCategory = (string) ($row['google_category'] ?? '');
                $productType = (string) ($row['product_type'] ?? ($row['category_path'] ?? ''));
                $mpn = $hasSku ? trim($sku) : '';
                $identifierExists = $hasSku ? 'yes' : 'no';

                fwrite($handle, "<item>\n");
                fwrite($handle, '<g:id>' . e($id) . '</g:id>' . "\n");

                if ($isVariant) {
                    fwrite($handle, '<g:item_group_id>' . e($groupId) . '</g:item_group_id>' . "\n");
                }

                fwrite($handle, '<title>' . e($title) . '</title>' . "\n");
                fwrite($handle, '<description>' . e($description) . '</description>' . "\n");
                fwrite($handle, '<link>' . e($link) . '</link>' . "\n");

                if ($image !== '') {
                    fwrite($handle, '<g:image_link>' . e($image) . '</g:image_link>' . "\n");
                }

                $additional = array_slice($additional, 0, 10);
                foreach ($additional as $img) {
                    if (! empty($img)) {
                        fwrite($handle, '<g:additional_image_link>' . e($img) . '</g:additional_image_link>' . "\n");
                    }
                }

                fwrite($handle, '<g:availability>' . e($availability) . '</g:availability>' . "\n");
                fwrite($handle, '<g:condition>new</g:condition>' . "\n");
                fwrite($handle, '<g:price>' . e($priceStr) . '</g:price>' . "\n");

                if (! is_null($saleStr)) {
                    fwrite($handle, '<g:sale_price>' . e($saleStr) . '</g:sale_price>' . "\n");

                    $start = $row['sale_price_start'] ?? null;
                    $end = $row['sale_price_end'] ?? null;
                    if ($start && $end) {
                        try {
                            $startIso = Carbon::parse($start)->toIso8601String();
                            $endIso = Carbon::parse($end)->toIso8601String();
                            fwrite($handle, '<g:sale_price_effective_date>' . e($startIso . '/' . $endIso) . '</g:sale_price_effective_date>' . "\n");
                        } catch (\Throwable $e) {
                        }
                    }
                }

                fwrite($handle, '<g:brand>' . e($brand) . '</g:brand>' . "\n");

                if ($identifierExists === 'yes' && $mpn !== '') {
                    fwrite($handle, '<g:mpn>' . e($mpn) . '</g:mpn>' . "\n");
                }
                fwrite($handle, '<g:identifier_exists>' . e($identifierExists) . '</g:identifier_exists>' . "\n");

                if ($googleCategory !== '') {
                    fwrite($handle, '<g:google_product_category>' . e($googleCategory) . '</g:google_product_category>' . "\n");
                }

                if ($productType !== '') {
                    fwrite($handle, '<g:product_type>' . e($productType) . '</g:product_type>' . "\n");
                }

                if ($vatRate > 0) {
                    fwrite($handle, "<g:tax>\n");
                    fwrite($handle, '<g:country>' . e($shippingCountry) . '</g:country>' . "\n");
                    fwrite($handle, '<g:rate>' . e((string) $vatRate) . '</g:rate>' . "\n");
                    fwrite($handle, "</g:tax>\n");
                }

                $shippingOut = $shippingPrice;
                try {
                    if (! is_null($freeShippingThreshold) && (float) $freeShippingThreshold > 0) {
                        if ((float) $priceOut >= (float) $freeShippingThreshold) {
                            $shippingOut = 0.0;
                        }
                    }
                } catch (\Throwable $e) {
                }

                fwrite($handle, "<g:shipping>\n");
                fwrite($handle, '<g:country>' . e($shippingCountry) . '</g:country>' . "\n");
                fwrite($handle, '<g:service>' . e($shippingService) . '</g:service>' . "\n");
                fwrite($handle, '<g:price>' . e(number_format((float) $shippingOut, 2, '.', '') . ' ' . $currency) . '</g:price>' . "\n");
                fwrite($handle, "</g:shipping>\n");

                fwrite($handle, "</item>\n");
            });

            fwrite($handle, "</channel>\n");
            fwrite($handle, "</rss>\n");
        }, $meta);
    }
}
