<?php

namespace Modules\Translation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationDatabaseSeeder extends Seeder
{
    public function run()
    {
        $baseTranslations = [
            'admin::sidebar.dashboard' => ['tr' => 'Gösterge Paneli', 'en' => 'Dashboard'],
            'product::sidebar.products' => ['tr' => 'Ürünler', 'en' => 'Products'],
            'category::sidebar.categories' => ['tr' => 'Kategoriler', 'en' => 'Categories'],
            'setting::sidebar.settings' => ['tr' => 'Ayarlar', 'en' => 'Settings'],
            'order::sidebar.orders' => ['tr' => 'Siparişler', 'en' => 'Orders'],
            'user::sidebar.users' => ['tr' => 'Kullanıcılar', 'en' => 'Users'],
            'media::sidebar.media' => ['tr' => 'Medya', 'en' => 'Media'],
            'page::sidebar.pages' => ['tr' => 'Sayfalar', 'en' => 'Pages'],
            'slider::sidebar.sliders' => ['tr' => 'Sliderlar', 'en' => 'Sliders'],
            'menu::sidebar.menus' => ['tr' => 'Menüler', 'en' => 'Menus'],
            'attribute::sidebar.attributes' => ['tr' => 'Özellikler', 'en' => 'Attributes'],
            'brand::sidebar.brands' => ['tr' => 'Markalar', 'en' => 'Brands'],
        ];

        foreach ($baseTranslations as $key => $locales) {
            $transId = DB::table('translations')->where('key', $key)->value('id');
            if (!$transId) {
                $transId = DB::table('translations')->insertGetId([
                    'key' => $key, 
                    'created_at' => now(), 
                    'updated_at' => now()
                ]);
            }

            foreach ($locales as $locale => $value) {
                DB::table('translation_translations')->updateOrInsert(
                    ['translation_id' => $transId, 'locale' => $locale],
                    ['value' => $value]
                );
            }
        }
    }
}
