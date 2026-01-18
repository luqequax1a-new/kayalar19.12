<?php

namespace Modules\ProductFeeds\Http\Controllers\Public;

use Illuminate\Http\Response;
use Modules\ProductFeeds\Services\FeedCacheService;
use Modules\ProductFeeds\Services\ProductFeedBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PinterestFeedController
{
    public function __construct(
        private readonly ProductFeedBuilder $feeds,
        private readonly FeedCacheService $cache,
    )
    {
    }

    public function index(): Response
    {
        if (! setting('product_feeds.global.enabled', true) || ! setting('product_feeds.pinterest.enabled', true)) {
            abort(404);
        }

        $channel = 'pinterest';

        $format = setting('product_feeds.pinterest.format', 'tsv');
        $contentType = $format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'text/tab-separated-values; charset=UTF-8';

        if ($this->cache->isEnabled() && ! $this->cache->shouldRegenerate($channel)) {
            $cached = $this->cache->readCache($channel);

            if ($cached !== null) {
                return new Response($cached, 200, ['Content-Type' => $contentType]);
            }
        }

        if ($this->cache->isEnabled()) {
            $this->regenerateCache();
            $cached = $this->cache->readCache($channel);

            return new Response((string) $cached, 200, ['Content-Type' => $contentType]);
        }

        return $this->generate();
    }

    public function generate(): Response
    {
        $format = setting('product_feeds.pinterest.format', 'tsv');
        $delimiter = $format === 'csv' ? ',' : "\t";
        $contentType = $format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'text/tab-separated-values; charset=UTF-8';

        $columns = [
            'id',
            'title',
            'description',
            'link',
            'image_link',
            'availability',
            'price',
            'sale_price',
            'brand',
            'condition',
            'google_product_category',
            'product_type',
            'item_group_id',
        ];

        return new StreamedResponse(function () use ($format, $delimiter, $columns) {
            echo implode($delimiter, $columns) . "\n";

            $this->feeds->streamNormalizedItemsForFeed('pinterest', function (array $row) use ($format, $delimiter) {
                $price = sprintf('%.2f %s', (float) $row['price'], $row['currency']);
                $salePrice = '';

                if (! is_null($row['sale_price'])) {
                    $salePrice = sprintf('%.2f %s', (float) $row['sale_price'], $row['currency']);
                }

                $fields = [
                    $row['id'],
                    $row['title'],
                    $row['description'],
                    $row['url'],
                    $row['main_image'],
                    $row['availability'],
                    $price,
                    $salePrice,
                    $row['brand'],
                    'new',
                    $row['google_category'] ?? '',
                    $row['category_path'] ?? '',
                    $row['item_group_id'],
                ];

                $sanitized = array_map(function ($value) use ($delimiter, $format) {
                    $value = (string) ($value ?? '');
                    $value = str_replace(["\r", "\n", "\t"], ' ', $value);

                    if ($format === 'csv' && str_contains($value, $delimiter)) {
                        $value = '"' . str_replace('"', '""', $value) . '"';
                    }

                    return $value;
                }, $fields);

                echo implode($delimiter, $sanitized) . "\n";
            });
        }, 200, ['Content-Type' => $contentType]);
    }

    public function regenerateCache(): void
    {
        $channel = 'pinterest';
        $format = setting('product_feeds.pinterest.format', 'tsv');
        $delimiter = $format === 'csv' ? ',' : "\t";

        $columns = [
            'id',
            'title',
            'description',
            'link',
            'image_link',
            'availability',
            'price',
            'sale_price',
            'brand',
            'condition',
            'google_product_category',
            'product_type',
            'item_group_id',
        ];

        $meta = ['items_count' => 0];

        $this->cache->writeCacheAtomic($channel, function ($handle) use (&$meta, $format, $delimiter, $columns) {
            fwrite($handle, implode($delimiter, $columns) . "\n");

            $this->feeds->streamNormalizedItemsForFeed('pinterest', function (array $row) use ($handle, &$meta, $format, $delimiter) {
                $meta['items_count']++;

                $price = sprintf('%.2f %s', (float) $row['price'], $row['currency']);
                $salePrice = '';

                if (! is_null($row['sale_price'])) {
                    $salePrice = sprintf('%.2f %s', (float) $row['sale_price'], $row['currency']);
                }

                $fields = [
                    $row['id'],
                    $row['title'],
                    $row['description'],
                    $row['url'],
                    $row['main_image'],
                    $row['availability'],
                    $price,
                    $salePrice,
                    $row['brand'],
                    'new',
                    $row['google_category'] ?? '',
                    $row['category_path'] ?? '',
                    $row['item_group_id'],
                ];

                $sanitized = array_map(function ($value) use ($delimiter, $format) {
                    $value = (string) ($value ?? '');
                    $value = str_replace(["\r", "\n", "\t"], ' ', $value);

                    if ($format === 'csv' && str_contains($value, $delimiter)) {
                        $value = '"' . str_replace('"', '""', $value) . '"';
                    }

                    return $value;
                }, $fields);

                fwrite($handle, implode($delimiter, $sanitized) . "\n");
            });
        }, $meta);
    }
}
