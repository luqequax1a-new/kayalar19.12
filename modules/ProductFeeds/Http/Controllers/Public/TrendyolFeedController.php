<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrendyolFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): Response
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.trendyol.enabled', true)) {
            abort(404);
        }

        $channel = 'trendyol';

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
        $supplierId = (string) setting('product_feeds.trendyol.supplier_id', '');
        $defaultBrand = (string) setting('product_feeds.trendyol.brand', setting('product_feeds.global.brand_name', setting('store_name')));
        $cargoCompany = (string) setting('product_feeds.trendyol.cargo_company', '');
        $vatRate = (string) setting('product_feeds.trendyol.vat_rate', '');
        $shipmentTime = (string) setting('product_feeds.trendyol.shipment_time', '1-3');

        return new StreamedResponse(function () use ($supplierId, $defaultBrand, $cargoCompany, $vatRate, $shipmentTime) {
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<products>' . "\n";

            $this->feeds->streamNormalizedItemsForFeed('trendyol', function (array $row) use ($supplierId, $defaultBrand, $cargoCompany, $vatRate, $shipmentTime) {
                $id = (string) $row['id'];
                $sku = $row['sku'] ?: $id;
                $brand = $row['brand'] ?: $defaultBrand;
                $price = (float) ($row['sale_price'] ?? $row['price']);
                $listPrice = (float) $row['price'];
                $stockQty = $row['stock'] ?? 0;

                echo '<product>' . "\n";
                echo '<id>' . e($id) . '</id>' . "\n";
                echo '<name>' . e($row['title']) . '</name>' . "\n";
                echo '<barcode>' . e($sku) . '</barcode>' . "\n";
                echo '<brand>' . e($brand) . '</brand>' . "\n";

                if (! empty($row['category_path'])) {
                    echo '<category>' . e($row['category_path']) . '</category>' . "\n";
                }

                echo '<price>' . number_format($price, 2, '.', '') . '</price>' . "\n";
                echo '<listPrice>' . number_format($listPrice, 2, '.', '') . '</listPrice>' . "\n";

                if ($vatRate !== '') {
                    echo '<vatRate>' . e($vatRate) . '</vatRate>' . "\n";
                }

                echo '<stockCode>' . e($sku) . '</stockCode>' . "\n";
                echo '<stockQuantity>' . (int) $stockQty . '</stockQuantity>' . "\n";
                echo '<description>' . e($row['description']) . '</description>' . "\n";

                echo '<images>' . "\n";
                if (! empty($row['main_image'])) {
                    echo '<image>' . e($row['main_image']) . '</image>' . "\n";
                }
                foreach ($row['additional_images'] as $image) {
                    echo '<image>' . e($image) . '</image>' . "\n";
                }
                echo '</images>' . "\n";

                if ($cargoCompany !== '') {
                    echo '<cargoCompanyName>' . e($cargoCompany) . '</cargoCompanyName>' . "\n";
                }

                echo '<shipmentTime>' . e($shipmentTime) . '</shipmentTime>' . "\n";

                if ($supplierId !== '') {
                    echo '<supplierId>' . e($supplierId) . '</supplierId>' . "\n";
                }

                echo '</product>' . "\n";
            });

            echo '</products>' . "\n";
        }, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function regenerateCache(): void
    {
        $channel = 'trendyol';
        $supplierId = (string) setting('product_feeds.trendyol.supplier_id', '');
        $defaultBrand = (string) setting('product_feeds.trendyol.brand', setting('product_feeds.global.brand_name', setting('store_name')));
        $cargoCompany = (string) setting('product_feeds.trendyol.cargo_company', '');
        $vatRate = (string) setting('product_feeds.trendyol.vat_rate', '');
        $shipmentTime = (string) setting('product_feeds.trendyol.shipment_time', '1-3');

        $meta = ['items_count' => 0];

        $this->cache->writeCacheAtomic($channel, function ($handle) use (&$meta, $supplierId, $defaultBrand, $cargoCompany, $vatRate, $shipmentTime) {
            fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
            fwrite($handle, '<products>' . "\n");

            $this->feeds->streamNormalizedItemsForFeed('trendyol', function (array $row) use ($handle, &$meta, $supplierId, $defaultBrand, $cargoCompany, $vatRate, $shipmentTime) {
                $meta['items_count']++;

                $id = (string) $row['id'];
                $sku = $row['sku'] ?: $id;
                $brand = $row['brand'] ?: $defaultBrand;
                $price = (float) ($row['sale_price'] ?? $row['price']);
                $listPrice = (float) $row['price'];
                $stockQty = $row['stock'] ?? 0;

                fwrite($handle, '<product>' . "\n");
                fwrite($handle, '<id>' . e($id) . '</id>' . "\n");
                fwrite($handle, '<name>' . e($row['title']) . '</name>' . "\n");
                fwrite($handle, '<barcode>' . e($sku) . '</barcode>' . "\n");
                fwrite($handle, '<brand>' . e($brand) . '</brand>' . "\n");

                if (! empty($row['category_path'])) {
                    fwrite($handle, '<category>' . e($row['category_path']) . '</category>' . "\n");
                }

                fwrite($handle, '<price>' . number_format($price, 2, '.', '') . '</price>' . "\n");
                fwrite($handle, '<listPrice>' . number_format($listPrice, 2, '.', '') . '</listPrice>' . "\n");

                if ($vatRate !== '') {
                    fwrite($handle, '<vatRate>' . e($vatRate) . '</vatRate>' . "\n");
                }

                fwrite($handle, '<stockCode>' . e($sku) . '</stockCode>' . "\n");
                fwrite($handle, '<stockQuantity>' . (int) $stockQty . '</stockQuantity>' . "\n");
                fwrite($handle, '<description>' . e($row['description']) . '</description>' . "\n");

                fwrite($handle, '<images>' . "\n");
                if (! empty($row['main_image'])) {
                    fwrite($handle, '<image>' . e($row['main_image']) . '</image>' . "\n");
                }
                foreach ($row['additional_images'] as $image) {
                    fwrite($handle, '<image>' . e($image) . '</image>' . "\n");
                }
                fwrite($handle, '</images>' . "\n");

                if ($cargoCompany !== '') {
                    fwrite($handle, '<cargoCompanyName>' . e($cargoCompany) . '</cargoCompanyName>' . "\n");
                }

                fwrite($handle, '<shipmentTime>' . e($shipmentTime) . '</shipmentTime>' . "\n");

                if ($supplierId !== '') {
                    fwrite($handle, '<supplierId>' . e($supplierId) . '</supplierId>' . "\n");
                }

                fwrite($handle, '</product>' . "\n");
            });

            fwrite($handle, '</products>' . "\n");
        }, $meta);
    }
}
