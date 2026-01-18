<?php

return [
    'title' => 'Product Feeds',

    'sections' => [
        'global'      => 'Global Feed Settings',
        'google'      => 'Google Merchant Center',
        'meta'        => 'Meta / Facebook / Instagram',
        'trendyol'    => 'Trendyol XML',
        'hepsiburada' => 'Hepsiburada XML',
        'pinterest'   => 'Pinterest Feed',
        'tiktok'      => 'TikTok Feed',
        'cache'       => 'Feed Cache & Cron Management',
    ],

    'fields' => [
        'enable_all'              => 'Enable all feeds',
        'default_brand_name'      => 'Default brand name',
        'default_country'         => 'Default country code',
        'default_currency'        => 'Default currency code',
        'include_out_of_stock'    => 'Include out of stock products',
        'include_unpublished'     => 'Include unpublished products',
        'include_variants'        => 'Include variants as separate items',
        'feed_locale'             => 'Feed locale',

        'google_enabled'          => 'Enabled',
        'google_feed_url'         => 'Feed URL',
        'google_default_category' => 'Default google_product_category',
        'google_missing_behavior' => 'Missing GTIN/MPN behavior',
        'google_use_store_tax'    => 'Use storefront tax configuration',
        'google_shipping_price'   => 'Flat shipping price',

        'meta_enabled'            => 'Enabled',
        'meta_feed_url'           => 'Feed URL',
        'meta_use_variants'       => 'Use variant-level records',

        'trendyol_enabled'        => 'Enabled',
        'trendyol_feed_url'       => 'Trendyol XML URL',
        'hepsiburada_enabled'     => 'Enabled',
        'hepsiburada_feed_url'    => 'Hepsiburada XML URL',
        'pinterest_enabled'       => 'Enabled',
        'pinterest_feed_url'      => 'Pinterest Feed URL',
        'tiktok_enabled'          => 'Enabled',
        'tiktok_feed_url'         => 'TikTok Feed URL',

        'feed_url'                => 'Feed URL',
        'enabled'                 => 'Enabled',
        'last_generated'          => 'Last generated',
        'items'                   => 'Items',
        'currency'                => 'Currency',

        'google_currency'             => 'Currency',
        'google_price_includes_vat'   => 'Price includes VAT',
        'google_default_vat_rate'     => 'Default VAT rate (%)',
        'google_shipping_country'     => 'Shipping country',
        'google_shipping_service'     => 'Shipping service',
        'google_free_shipping_threshold' => 'Free shipping threshold',

        'meta_currency'               => 'Currency',

        'trendyol_supplier_id'        => 'Supplier ID',
        'trendyol_brand'              => 'Custom Brand Name',
        'trendyol_cargo_company'      => 'Cargo Company',
        'trendyol_vat_rate'           => 'VAT Rate',
        'trendyol_shipment_time'      => 'Estimated Shipment (Days)',

        'pinterest_format'            => 'File Format',
        'tiktok_in_stock_only'        => 'Include in stock only',
        'tiktok_shipping_profile'     => 'TikTok Shipping Profile ID',

        'regenerate'                  => 'Regenerate',
        'google_missing_behavior_empty' => 'Leave empty',
        'google_missing_behavior_mpn_from_id' => 'Use product ID as MPN',
        'google_taxonomy_help'        => 'Google taxonomy path, e.g. Apparel & Accessories > Clothing',

        'cache_enabled'               => 'Enable feed caching',
        'cache_google'                => 'Google feed cache duration (minutes)',
        'cache_meta'                  => 'Meta feed cache duration (minutes)',
        'cache_trendyol'              => 'Trendyol feed cache duration (minutes)',
        'cache_hepsiburada'           => 'Hepsiburada feed cache duration (minutes)',
        'cache_pinterest'             => 'Pinterest feed cache duration (minutes)',
        'cache_tiktok'                => 'TikTok feed cache duration (minutes)',
        'cache_token'                 => 'Secure cron token',
        'cache_info'                  => 'Feeds will be served from cached static files under storage/app/feeds. Cron jobs or Artisan commands will regenerate these files automatically.',
        'cache_cron_help'             => 'Use these URLs in shared hosting cron jobs (every 30–60 minutes).',
        'cache_refresh_google'        => 'Refresh Google feed cache',
        'cache_refresh_meta'          => 'Refresh Meta feed cache',
        'cache_refresh_trendyol'      => 'Refresh Trendyol feed cache',
        'cache_refresh_hepsiburada'   => 'Refresh Hepsiburada feed cache',
        'cache_refresh_pinterest'     => 'Refresh Pinterest feed cache',
        'cache_refresh_tiktok'        => 'Refresh TikTok feed cache',
        'cache_cron_url'              => 'Cron URL',
    ],

    'cache_refreshed' => 'Feed cache refreshed.',
];
