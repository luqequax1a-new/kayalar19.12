<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TikTokFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): Response
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.tiktok.enabled', true)) {
            abort(404);
        }

        $channel = 'tiktok';

        if ($this->cache->isEnabled() && ! $this->cache->shouldRegenerate($channel)) {
            $cached = $this->cache->readCache($channel);

            if ($cached !== null) {
                return new Response($cached, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
            }
        }

        if ($this->cache->isEnabled()) {
            $this->regenerateCache();
            $cached = $this->cache->readCache($channel);

            return new Response((string) $cached, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        }

        return $this->generate();
    }

    public function generate(): Response
    {
        $inStockOnly = (bool) setting('product_feeds.tiktok.in_stock_only', true);
        $shippingProfile = (string) setting('product_feeds.tiktok.shipping_profile', '');

        return new StreamedResponse(function () use ($inStockOnly, $shippingProfile) {
            echo '{ "items": [' . "\n";
            $first = true;

            $this->feeds->streamNormalizedItemsForFeed('tiktok', function (array $row) use (&$first, $inStockOnly, $shippingProfile) {
                if ($inStockOnly && $row['availability'] !== 'in stock') {
                    return;
                }

                if (! $first) {
                    echo ',' . "\n";
                }
                $first = false;

                $images = [];
                if (! empty($row['main_image'])) {
                    $images[] = $row['main_image'];
                }
                if (! empty($row['additional_images'])) {
                    $images = array_merge($images, $row['additional_images']);
                }

                $item = [
                    'id' => (string) $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'price' => (float) ($row['sale_price'] ?? $row['price']),
                    'currency' => $row['currency'],
                    'stock' => (int) ($row['stock'] ?? 0),
                    'brand' => $row['brand'],
                    'sku' => (string) ($row['sku'] ?: $row['id']),
                    'url' => $row['url'],
                    'images' => $images,
                    'category' => $row['category_path'],
                    'weight' => (float) ($row['weight'] ?? 0),
                    'shipping' => [
                        'price' => null,
                        'currency' => $row['currency'],
                        'profile' => $shippingProfile ?: null,
                    ],
                ];

                echo json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            });

            echo "\n" . '] }';
        }, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function regenerateCache(): void
    {
        $channel = 'tiktok';
        $inStockOnly = (bool) setting('product_feeds.tiktok.in_stock_only', true);
        $shippingProfile = (string) setting('product_feeds.tiktok.shipping_profile', '');

        $meta = ['items_count' => 0];

        $this->cache->writeCacheAtomic($channel, function ($handle) use (&$meta, $inStockOnly, $shippingProfile) {
            fwrite($handle, '{ "items": [' . "\n");
            $first = true;

            $this->feeds->streamNormalizedItemsForFeed('tiktok', function (array $row) use ($handle, &$meta, &$first, $inStockOnly, $shippingProfile) {
                if ($inStockOnly && $row['availability'] !== 'in stock') {
                    return;
                }

                $meta['items_count']++;

                if (! $first) {
                    fwrite($handle, ',' . "\n");
                }
                $first = false;

                $images = [];
                if (! empty($row['main_image'])) {
                    $images[] = $row['main_image'];
                }
                if (! empty($row['additional_images'])) {
                    $images = array_merge($images, $row['additional_images']);
                }

                $item = [
                    'id' => (string) $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'price' => (float) ($row['sale_price'] ?? $row['price']),
                    'currency' => $row['currency'],
                    'stock' => (int) ($row['stock'] ?? 0),
                    'brand' => $row['brand'],
                    'sku' => (string) ($row['sku'] ?: $row['id']),
                    'url' => $row['url'],
                    'images' => $images,
                    'category' => $row['category_path'],
                    'weight' => (float) ($row['weight'] ?? 0),
                    'shipping' => [
                        'price' => null,
                        'currency' => $row['currency'],
                        'profile' => $shippingProfile ?: null,
                    ],
                ];

                fwrite($handle, json_encode($item, JSON_UNESCAPED_UNICODE));
            });

            fwrite($handle, "\n" . '] }');
        }, $meta);
    }
}
