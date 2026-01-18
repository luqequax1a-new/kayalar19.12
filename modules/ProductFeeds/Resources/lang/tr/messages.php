<?php

return [
    'title' => 'Ürün Feed\'leri',

    'sections' => [
        'global'      => 'Genel Feed Ayarları',
        'google'      => 'Google Merchant Center',
        'meta'        => 'Meta / Facebook / Instagram',
        'trendyol'    => 'Trendyol XML',
        'hepsiburada' => 'Hepsiburada XML',
        'pinterest'   => 'Pinterest Feed',
        'tiktok'      => 'TikTok Feed',
        'cache'       => 'Feed Cache & Cron Yönetimi',
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

        'trendyol_enabled'        => 'Etkin',
        'trendyol_feed_url'       => 'Trendyol XML URL',
        'hepsiburada_enabled'     => 'Etkin',
        'hepsiburada_feed_url'    => 'Hepsiburada XML URL',
        'pinterest_enabled'       => 'Etkin',
        'pinterest_feed_url'      => 'Pinterest Feed URL',
        'tiktok_enabled'          => 'Etkin',
        'tiktok_feed_url'         => 'TikTok Feed URL',

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

        'meta_currency'               => 'Para birimi',

        'trendyol_supplier_id'        => 'Tedarikçi ID',
        'trendyol_brand'              => 'Özel Marka Adı',
        'trendyol_cargo_company'      => 'Kargo Firması',
        'trendyol_vat_rate'           => 'KDV Oranı',
        'trendyol_shipment_time'      => 'Tahmini Teslimat (Gün)',

        'pinterest_format'            => 'Dosya Formatı',
        'tiktok_in_stock_only'        => 'Sadece stokta olanları dahil et',
        'tiktok_shipping_profile'     => 'TikTok Kargo Profili ID',

        'regenerate'                  => 'Yenile',
        'google_missing_behavior_empty' => 'Boş bırak',
        'google_missing_behavior_mpn_from_id' => 'Ürün ID\'sini MPN olarak kullan',
        'google_taxonomy_help'        => 'Google taxonomy yolu, örn: Apparel & Accessories > Clothing',

        'cache_enabled'               => 'Feed cache\'i etkinleştir',
        'cache_google'                => 'Google cache süresi (dakika)',
        'cache_meta'                  => 'Meta cache süresi (dakika)',
        'cache_trendyol'              => 'Trendyol cache süresi (dakika)',
        'cache_hepsiburada'           => 'Hepsiburada cache süresi (dakika)',
        'cache_pinterest'             => 'Pinterest cache süresi (dakika)',
        'cache_tiktok'                => 'TikTok cache süresi (dakika)',
        'cache_token'                 => 'Güvenli cron token',
        'cache_info'                  => 'Feed\'ler storage/app/feeds altında statik dosyalardan servis edilir. Cron job veya Artisan komutu ile otomatik yenilenebilir.',
        'cache_cron_help'             => 'Paylaşımlı hosting cron job\'larında (30–60 dk) bu URL\'leri kullanın.',
        'cache_refresh_google'        => 'Google feed cache\'ini yenile',
        'cache_refresh_meta'          => 'Meta feed cache\'ini yenile',
        'cache_refresh_trendyol'      => 'Trendyol feed cache\'ini yenile',
        'cache_refresh_hepsiburada'   => 'Hepsiburada feed cache\'ini yenile',
        'cache_refresh_pinterest'     => 'Pinterest feed cache\'ini yenile',
        'cache_refresh_tiktok'        => 'TikTok feed cache\'ini yenile',
        'cache_cron_url'              => 'Cron URL',
    ],

    'cache_refreshed' => 'Feed cache yenilendi.',
];
