<?php

return [
    'title' => 'Product Feeds',

    'sections' => [
        'global'   => 'Global Feed Settings',
        'google'   => 'Google Merchant Center',
        'meta'     => 'Meta / Facebook / Instagram',
        'cache'    => 'Feed Cache & Cron Management',
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

        'meta_currency'            => 'Currency',

        'regenerate'               => 'Regenerate',
        'google_missing_behavior_empty' => 'Leave empty',
        'google_missing_behavior_mpn_from_id' => 'Use product ID as MPN',
        'google_taxonomy_help'     => 'Google taxonomy path, e.g. Apparel & Accessories > Clothing',

        'cache_enabled'           => 'Enable feed caching',
        'cache_google'            => 'Google feed cache duration (minutes)',
        'cache_meta'              => 'Meta feed cache duration (minutes)',
        'cache_token'             => 'Secure cron token',
        'cache_info'              => 'Feeds will be served from cached static files under storage/app/feeds. Cron jobs or Artisan commands will regenerate these files automatically.',
        'cache_cron_help'         => 'Use these URLs in shared hosting cron jobs (every 30–60 minutes).',
        'cache_refresh_google'    => 'Refresh Google feed cache',
        'cache_refresh_meta'      => 'Refresh Meta feed cache',
        'cache_cron_url'          => 'Cron URL',
    ],

    'cache_refreshed' => 'Feed cache refreshed.',
];
