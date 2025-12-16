<?php

return [
    'title' => 'Ürün Feed\'leri',

    'sections' => [
        'global'   => 'Genel Feed Ayarları',
        'google'   => 'Google Merchant Center',
        'meta'     => 'Meta / Facebook / Instagram',
        'cache'    => 'Feed Cache & Cron Yönetimi',
    ],

    'fields' => [
        'enable_all'              => 'Tüm feed\'leri etkinleştir',
        'default_brand_name'      => 'Varsayılan marka adı',
        'default_country'         => 'Varsayılan ülke kodu',
        'default_currency'        => 'Varsayılan para birimi kodu',
        'include_out_of_stock'    => 'Stokta olmayan ürünleri dahil et',
        'include_unpublished'     => 'Yayında olmayan ürünleri dahil et',
        'include_variants'        => 'Varyantları ayrı ürün olarak dahil et',
        'feed_locale'             => 'Feed dili (locale)',

        'google_enabled'          => 'Etkin',
        'google_feed_url'         => 'Feed URL',
        'google_default_category' => 'Varsayılan google_product_category',
        'google_missing_behavior' => 'GTIN/MPN eksikse davranış',
        'google_use_store_tax'    => 'Mağaza vergi ayarlarını kullan',
        'google_shipping_price'   => 'Sabit kargo ücreti',

        'meta_enabled'            => 'Etkin',
        'meta_feed_url'           => 'Feed URL',
        'meta_use_variants'       => 'Varyant bazlı kayıtları kullan',

        'feed_url'                => 'Feed URL',
        'enabled'                 => 'Etkin',
        'last_generated'          => 'Son oluşturulma',
        'items'                   => 'Ürün sayısı',
        'currency'                => 'Para birimi',

        'google_currency'             => 'Para birimi',
        'google_price_includes_vat'   => 'Fiyat KDV dahil',
        'google_default_vat_rate'     => 'Varsayılan KDV oranı (%)',
        'google_shipping_country'     => 'Kargo ülkesi',
        'google_shipping_service'     => 'Kargo hizmeti',
        'google_free_shipping_threshold' => 'Ücretsiz kargo limiti',

        'meta_currency'            => 'Para birimi',

        'regenerate'               => 'Yenile',
        'google_missing_behavior_empty' => 'Boş bırak',
        'google_missing_behavior_mpn_from_id' => 'Ürün ID\'sini MPN olarak kullan',
        'google_taxonomy_help'     => 'Google taxonomy yolu, örn: Apparel & Accessories > Clothing',

        'cache_enabled'           => 'Feed cache\'i etkinleştir',
        'cache_google'            => 'Google cache süresi (dakika)',
        'cache_meta'              => 'Meta cache süresi (dakika)',
        'cache_token'             => 'Güvenli cron token',
        'cache_info'              => 'Feed\'ler storage/app/feeds altında statik dosyalardan servis edilir. Cron job veya Artisan komutu ile otomatik yenilenebilir.',
        'cache_cron_help'         => 'Paylaşımlı hosting cron job\'larında (30–60 dk) bu URL\'leri kullanın.',
        'cache_refresh_google'    => 'Google feed cache\'ini yenile',
        'cache_refresh_meta'      => 'Meta feed cache\'ini yenile',
        'cache_cron_url'          => 'Cron URL',
    ],

    'cache_refreshed' => 'Feed cache yenilendi.',
];
