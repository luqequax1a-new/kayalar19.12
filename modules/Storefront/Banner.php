<?php

namespace Modules\Storefront;

use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Cache;

class Banner
{
    public $image;
    public $call_to_action_url;
    public $open_in_new_window;


    public function __construct($image, $call_to_action_url, $open_in_new_window)
    {
        $this->image = $image;
        $this->call_to_action_url = $call_to_action_url;
        $this->open_in_new_window = (bool)$open_in_new_window;
    }


    public static function getProductPageBanner()
    {
        return self::findByName('storefront_product_page_banner');
    }


    public static function findByName($name)
    {
        return Cache::tags('settings')
            ->rememberForever(md5("storefront_banners.{$name}:" . locale()), function () use ($name) {
                return new self(
                    File::findOrNew(setting("{$name}_file_id")),
                    setting("{$name}_call_to_action_url"),
                    setting("{$name}_open_in_new_window")
                );
            });
    }


    public static function getSliderBanners()
    {
        return [
            'banner_1' => self::findByName('storefront_slider_banner_1'),
            'banner_2' => self::findByName('storefront_slider_banner_2'),
        ];
    }


    public static function getThreeColumnFullWidthBanners()
    {
        return [
            'background' => self::findByName('storefront_three_column_full_width_banners_background'),
            'banner_1' => self::findByName('storefront_three_column_full_width_banners_1'),
            'banner_2' => self::findByName('storefront_three_column_full_width_banners_2'),
            'banner_3' => self::findByName('storefront_three_column_full_width_banners_3'),
        ];
    }


    public static function getTwoColumnBanners()
    {
        return [
            'banner_1' => self::findByName('storefront_two_column_banners_1'),
            'banner_2' => self::findByName('storefront_two_column_banners_2'),
        ];
    }


    public static function getThreeColumnBanners()
    {
        return [
            'banner_1' => self::findByName('storefront_three_column_banners_1'),
            'banner_2' => self::findByName('storefront_three_column_banners_2'),
            'banner_3' => self::findByName('storefront_three_column_banners_3'),
        ];
    }


    public static function getThreeColumnBanners2()
    {
        return [
            'banner_1' => self::findByName('storefront_three_column_banners_2_1'),
            'banner_2' => self::findByName('storefront_three_column_banners_2_2'),
            'banner_3' => self::findByName('storefront_three_column_banners_2_3'),
        ];
    }


    public static function getOneColumnBanner()
    {
        return self::findByName('storefront_one_column_banner');
    }


    public static function getCategoryGridBanners()
    {
        return [
            'banner_1' => self::findByName('storefront_category_grid_banners_1'),
            'banner_2' => self::findByName('storefront_category_grid_banners_2'),
            'banner_3' => self::findByName('storefront_category_grid_banners_3'),
            'banner_4' => self::findByName('storefront_category_grid_banners_4'),
            'banner_5' => self::findByName('storefront_category_grid_banners_5'),
            'banner_6' => self::findByName('storefront_category_grid_banners_6'),
            'banner_7' => self::findByName('storefront_category_grid_banners_7'),
            'banner_8' => self::findByName('storefront_category_grid_banners_8'),
            'banner_9' => self::findByName('storefront_category_grid_banners_9'),
            'banner_10' => self::findByName('storefront_category_grid_banners_10'),
            'banner_11' => self::findByName('storefront_category_grid_banners_11'),
            'banner_12' => self::findByName('storefront_category_grid_banners_12'),
            'banner_13' => self::findByName('storefront_category_grid_banners_13'),
            'banner_14' => self::findByName('storefront_category_grid_banners_14'),
            'banner_15' => self::findByName('storefront_category_grid_banners_15'),
            'banner_16' => self::findByName('storefront_category_grid_banners_16'),
            'banner_17' => self::findByName('storefront_category_grid_banners_17'),
            'banner_18' => self::findByName('storefront_category_grid_banners_18'),
            'banner_19' => self::findByName('storefront_category_grid_banners_19'),
            'banner_20' => self::findByName('storefront_category_grid_banners_20'),
        ];
    }
}
