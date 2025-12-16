<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;

class MetaFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): Response
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.meta.enabled', true)) {
            abort(404);
        }

        $channel = 'meta';

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
        $currency = (string) setting('product_feeds.meta.currency', setting('product_feeds.global.currency', 'TRY'));

        return new StreamedResponse(function () use ($currency) {
            echo '{"data":[';

            $first = true;

            $this->feeds->streamNormalizedItemsForFeed('meta', function (array $row) use (&$first, $currency) {
                $item = [
                    'id' => (string) ($row['id'] ?? ''),
                    'item_group_id' => $row['item_group_id'] ?? null,
                    'title' => (string) ($row['title'] ?? ''),
                    'description' => (string) ($row['description'] ?? ''),
                    'availability' => (string) ($row['availability'] ?? 'in stock'),
                    'condition' => 'new',
                    'price' => sprintf('%.2f %s', (float) ($row['price'] ?? 0), $currency),
                    'link' => (string) ($row['url'] ?? ''),
                    'image_link' => (string) ($row['main_image'] ?? ''),
                    'brand' => (string) ($row['brand'] ?? ''),
                    'google_product_category' => $row['google_category'] ?? null,
                    'product_type' => (string) ($row['product_type'] ?? ($row['category_path'] ?? '')),
                ];

                if (! empty($row['additional_images'])) {
                    $item['additional_image_link'] = $row['additional_images'];
                }

                if (! is_null($row['sale_price'] ?? null)) {
                    $item['sale_price'] = sprintf('%.2f %s', (float) ($row['sale_price'] ?? 0), $currency);
                }

                if ($first) {
                    $first = false;
                } else {
                    echo ',';
                }

                echo json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            });

            echo ']}';
        }, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public function regenerateCache(): void
    {
        $channel = 'meta';
        $meta = ['items_count' => 0];

        $appUrl = (string) config('app.url');
        $appHost = strtolower((string) (parse_url($appUrl, PHP_URL_HOST) ?? ''));
        if ($appHost === '127.0.0.1' || $appHost === 'localhost') {
            $meta['warnings'] = array_values(array_unique(array_merge((array) ($meta['warnings'] ?? []), ['APP_URL is localhost'])));
        }

        $currency = (string) setting('product_feeds.meta.currency', setting('product_feeds.global.currency', 'TRY'));

        $this->cache->writeCacheAtomic($channel, function ($handle) use (&$meta, $currency) {
            fwrite($handle, '{"data":[');

            $first = true;

            $this->feeds->streamNormalizedItemsForFeed('meta', function (array $row) use ($handle, &$meta, &$first, $currency) {
                $meta['items_count'] = (int) ($meta['items_count'] ?? 0) + 1;

                $item = [
                    'id' => (string) ($row['id'] ?? ''),
                    'item_group_id' => $row['item_group_id'] ?? null,
                    'title' => (string) ($row['title'] ?? ''),
                    'description' => (string) ($row['description'] ?? ''),
                    'availability' => (string) ($row['availability'] ?? 'in stock'),
                    'condition' => 'new',
                    'price' => sprintf('%.2f %s', (float) ($row['price'] ?? 0), $currency),
                    'link' => (string) ($row['url'] ?? ''),
                    'image_link' => (string) ($row['main_image'] ?? ''),
                    'brand' => (string) ($row['brand'] ?? ''),
                    'google_product_category' => $row['google_category'] ?? null,
                    'product_type' => (string) ($row['product_type'] ?? ($row['category_path'] ?? '')),
                ];

                if (! empty($row['additional_images'])) {
                    $item['additional_image_link'] = $row['additional_images'];
                }

                if (! is_null($row['sale_price'] ?? null)) {
                    $item['sale_price'] = sprintf('%.2f %s', (float) ($row['sale_price'] ?? 0), $currency);
                }

                if ($first) {
                    $first = false;
                } else {
                    fwrite($handle, ',');
                }

                fwrite($handle, (string) json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            });

            fwrite($handle, ']}');
        }, $meta);
    }
}
