<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HepsiburadaFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): Response
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.hepsiburada.enabled', true)) {
            abort(404);
        }

        $channel = 'hepsiburada';

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
        return new StreamedResponse(function () {
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<products>' . "\n";

            $this->feeds->streamNormalizedItemsForFeed('hepsiburada', function (array $row) {
                $id = (string) $row['id'];
                $sku = $row['sku'] ?: $id;
                
                echo '<product>' . "\n";
                echo '<id>' . e($id) . '</id>' . "\n";
                echo '<sku>' . e($sku) . '</sku>' . "\n";
                echo '<name>' . e($row['title']) . '</name>' . "\n";
                echo '<brand>' . e($row['brand']) . '</brand>' . "\n";
                echo '<category>' . e($row['category_path']) . '</category>' . "\n";
                echo '<price>' . number_format((float) $row['price'], 2, '.', '') . '</price>' . "\n";
                
                if (! is_null($row['sale_price'])) {
                    echo '<sale_price>' . number_format((float) $row['sale_price'], 2, '.', '') . '</sale_price>' . "\n";
                }
                
                echo '<currency>' . e($row['currency']) . '</currency>' . "\n";
                echo '<stock>' . (int) ($row['stock'] ?? 0) . '</stock>' . "\n";
                echo '<url>' . e($row['url']) . '</url>' . "\n";
                echo '<description><![CDATA[' . $row['description'] . ']]></description>' . "\n";
                
                echo '<images>' . "\n";
                if (! empty($row['main_image'])) {
                    echo '<image>' . e($row['main_image']) . '</image>' . "\n";
                }
                foreach ($row['additional_images'] as $image) {
                    echo '<image>' . e($image) . '</image>' . "\n";
                }
                echo '</images>' . "\n";
                
                echo '</product>' . "\n";
            });

            echo '</products>' . "\n";
        }, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function regenerateCache(): void
    {
        $channel = 'hepsiburada';
        $meta = ['items_count' => 0];

        $this->cache->writeCacheAtomic($channel, function ($handle) use (&$meta) {
            fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
            fwrite($handle, '<products>' . "\n");

            $this->feeds->streamNormalizedItemsForFeed('hepsiburada', function (array $row) use ($handle, &$meta) {
                $meta['items_count']++;

                $id = (string) $row['id'];
                $sku = $row['sku'] ?: $id;
                
                fwrite($handle, '<product>' . "\n");
                fwrite($handle, '<id>' . e($id) . '</id>' . "\n");
                fwrite($handle, '<sku>' . e($sku) . '</sku>' . "\n");
                fwrite($handle, '<name>' . e($row['title']) . '</name>' . "\n");
                fwrite($handle, '<brand>' . e($row['brand'] ?? '') . '</brand>' . "\n");
                fwrite($handle, '<category>' . e($row['category_path']) . '</category>' . "\n");
                fwrite($handle, '<price>' . number_format((float) $row['price'], 2, '.', '') . '</price>' . "\n");
                
                if (! is_null($row['sale_price'])) {
                    fwrite($handle, '<sale_price>' . number_format((float) $row['sale_price'], 2, '.', '') . '</sale_price>' . "\n");
                }
                
                fwrite($handle, '<currency>' . e($row['currency']) . '</currency>' . "\n");
                fwrite($handle, '<stock>' . (int) ($row['stock'] ?? 0) . '</stock>' . "\n");
                fwrite($handle, '<url>' . e($row['url']) . '</url>' . "\n");
                fwrite($handle, '<description><![CDATA[' . $row['description'] . ']]></description>' . "\n");
                
                fwrite($handle, '<images>' . "\n");
                if (! empty($row['main_image'])) {
                    fwrite($handle, '<image>' . e($row['main_image']) . '</image>' . "\n");
                }
                foreach ($row['additional_images'] as $image) {
                    fwrite($handle, '<image>' . e($image) . '</image>' . "\n");
                }
                fwrite($handle, '</images>' . "\n");
                
                fwrite($handle, '</product>' . "\n");
            });

            fwrite($handle, '</products>' . "\n");
        }, $meta);
    }
}
