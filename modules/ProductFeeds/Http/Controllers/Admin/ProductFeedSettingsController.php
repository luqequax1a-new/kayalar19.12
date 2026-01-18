<?php

namespace Modules\ProductFeeds\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\ProductFeeds\Services\FeedCacheService;

class ProductFeedSettingsController extends Controller
{
    public function index()
    {
        /** @var FeedCacheService $cache */
        $cache = app(FeedCacheService::class);

        $settings = [
            'global' => [
                'enabled' => (bool) setting('product_feeds.global.enabled', true),
                'brand_name' => (string) setting('product_feeds.global.brand_name', setting('store_name')),
                'country' => (string) setting('product_feeds.global.country', 'TR'),
                'currency' => (string) setting('product_feeds.global.currency', currency()),
                'include_out_of_stock' => (bool) setting('product_feeds.global.include_out_of_stock', false),
                'include_unpublished' => (bool) setting('product_feeds.global.include_unpublished', false),
                'include_variants' => (bool) setting('product_feeds.global.include_variants', true),
                'locale' => (string) setting('product_feeds.global.locale', locale()),
            ],
            'google' => [
                'enabled' => (bool) setting('product_feeds.google.enabled', true),
                'category' => (string) setting('product_feeds.google.category', ''),
                'missing_identifier_behavior' => (string) setting('product_feeds.google.missing_identifier_behavior', 'mpn_from_id'),
                'use_store_tax' => (bool) setting('product_feeds.google.use_store_tax', true),
                'currency' => (string) setting('product_feeds.google.currency', 'TRY'),
                'price_includes_vat' => (bool) setting('product_feeds.google.price_includes_vat', true),
                'shipping_country' => (string) setting('product_feeds.google.shipping_country', 'TR'),
                'shipping_service' => (string) setting('product_feeds.google.shipping_service', 'Standard'),
                'shipping_price' => (string) setting('product_feeds.google.shipping_price', 0),
                'free_shipping_threshold' => setting('product_feeds.google.free_shipping_threshold'),
            ],
            'meta' => [
                'enabled' => (bool) setting('product_feeds.meta.enabled', true),
                'use_variants' => (bool) setting('product_feeds.meta.use_variants', true),
                'currency' => (string) setting('product_feeds.meta.currency', 'TRY'),
            ],
            'trendyol' => [
                'enabled' => (bool) setting('product_feeds.trendyol.enabled', false),
                'supplier_id' => (string) setting('product_feeds.trendyol.supplier_id', ''),
                'brand' => (string) setting('product_feeds.trendyol.brand', ''),
                'cargo_company' => (string) setting('product_feeds.trendyol.cargo_company', ''),
                'vat_rate' => (string) setting('product_feeds.trendyol.vat_rate', ''),
                'shipment_time' => (string) setting('product_feeds.trendyol.shipment_time', '1-3'),
            ],
            'hepsiburada' => [
                'enabled' => (bool) setting('product_feeds.hepsiburada.enabled', false),
            ],
            'pinterest' => [
                'enabled' => (bool) setting('product_feeds.pinterest.enabled', false),
                'format' => (string) setting('product_feeds.pinterest.format', 'tsv'),
            ],
            'tiktok' => [
                'enabled' => (bool) setting('product_feeds.tiktok.enabled', false),
                'in_stock_only' => (bool) setting('product_feeds.tiktok.in_stock_only', true),
                'shipping_profile' => (string) setting('product_feeds.tiktok.shipping_profile', ''),
            ],
            'cache' => [
                'enabled' => (bool) setting('product_feeds.cache.enabled', false),
                'google' => (int) setting('product_feeds.cache.google', 60),
                'meta' => (int) setting('product_feeds.cache.meta', 60),
                'trendyol' => (int) setting('product_feeds.cache.trendyol', 60),
                'hepsiburada' => (int) setting('product_feeds.cache.hepsiburada', 60),
                'pinterest' => (int) setting('product_feeds.cache.pinterest', 60),
                'tiktok' => (int) setting('product_feeds.cache.tiktok', 60),
                'token' => (string) setting('product_feeds.cache.token', ''),
            ],
        ];

        if ($settings['cache']['token'] === '') {
            $token = Str::random(32);
            setting(['product_feeds.cache.token' => $token]);
            $settings['cache']['token'] = $token;
        }

        $feedMeta = [
            'google' => $cache->readMeta('google'),
            'meta' => $cache->readMeta('meta'),
            'trendyol' => $cache->readMeta('trendyol'),
            'hepsiburada' => $cache->readMeta('hepsiburada'),
            'pinterest' => $cache->readMeta('pinterest'),
            'tiktok' => $cache->readMeta('tiktok'),
        ];

        foreach ($feedMeta as $key => $meta) {
            if (isset($meta['generated_at'])) {
                $carbon = \Illuminate\Support\Carbon::parse($meta['generated_at']);
                $feedMeta[$key]['generated_at_formatted'] = sprintf(
                    '%s (%s)',
                    $carbon->diffForHumans(),
                    $carbon->format('d.m.Y H:i')
                );
            }
        }

        return view('product_feeds::admin.settings.index', compact('settings', 'feedMeta'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'global.enabled' => 'sometimes|boolean',
            'global.brand_name' => 'nullable|string|max:255',
            'global.country' => 'nullable|string|max:2',
            'global.currency' => 'nullable|string|max:3',
            'global.include_out_of_stock' => 'sometimes|boolean',
            'global.include_unpublished' => 'sometimes|boolean',
            'global.include_variants' => 'sometimes|boolean',
            'global.locale' => 'nullable|string|max:10',

            'google.enabled' => 'sometimes|boolean',
            'google.category' => 'nullable|string|max:255',
            'google.missing_identifier_behavior' => 'nullable|string|in:empty,mpn_from_id',
            'google.use_store_tax' => 'sometimes|boolean',
            'google.currency' => 'nullable|string|max:3',
            'google.price_includes_vat' => 'sometimes|boolean',
            'google.shipping_country' => 'nullable|string|max:2',
            'google.shipping_service' => 'nullable|string|max:255',
            'google.shipping_price' => 'nullable|numeric|min:0',
            'google.free_shipping_threshold' => 'nullable|numeric|min:0',

            'meta.enabled' => 'sometimes|boolean',
            'meta.use_variants' => 'sometimes|boolean',
            'meta.currency' => 'nullable|string|max:3',

            'trendyol.enabled' => 'sometimes|boolean',
            'trendyol.supplier_id' => 'nullable|string|max:255',
            'trendyol.brand' => 'nullable|string|max:255',
            'trendyol.cargo_company' => 'nullable|string|max:255',
            'trendyol.vat_rate' => 'nullable|string|max:10',
            'trendyol.shipment_time' => 'nullable|string|max:255',

            'hepsiburada.enabled' => 'sometimes|boolean',

            'pinterest.enabled' => 'sometimes|boolean',
            'pinterest.format' => 'nullable|string|in:tsv,csv',

            'tiktok.enabled' => 'sometimes|boolean',
            'tiktok.in_stock_only' => 'sometimes|boolean',
            'tiktok.shipping_profile' => 'nullable|string|max:255',

            'cache.enabled' => 'sometimes|boolean',
            'cache.google' => 'nullable|integer|min:0',
            'cache.meta' => 'nullable|integer|min:0',
            'cache.trendyol' => 'nullable|integer|min:0',
            'cache.hepsiburada' => 'nullable|integer|min:0',
            'cache.pinterest' => 'nullable|integer|min:0',
            'cache.tiktok' => 'nullable|integer|min:0',
        ]);

        $settings = [];

        foreach ($data as $section => $values) {
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    $settings["product_feeds.{$section}.{$key}"] = is_bool($value) ? (int) $value : $value;
                }
            }
        }

        setting($settings);

        return redirect()->back()->with('success', trans('admin::messages.saved')); 
    }
}
