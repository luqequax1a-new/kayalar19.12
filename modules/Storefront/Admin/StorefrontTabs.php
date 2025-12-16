<?php

namespace Modules\Storefront\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\Tag\Entities\Tag;
use Modules\Storefront\Banner;
use Modules\Menu\Entities\Menu;
use Modules\Page\Entities\Page;
use Modules\Media\Entities\File;
use Modules\Brand\Entities\Brand;
use Modules\Slider\Entities\Slider;
use Illuminate\Support\Facades\Cache;
use Modules\FlashSale\Entities\FlashSale;
use Modules\Product\Repositories\ProductRepository;

class StorefrontTabs extends Tabs
{
    /**
     * Make new tabs with groups.
     *
     * @return void
     */
    public function make()
    {
        $homeWeights = $this->homePageSectionsWeights();

        $this->group('general_settings', trans('storefront::storefront.tabs.group.general_settings'))
            ->active()
            ->add($this->general())
            ->add($this->logo())
            ->add($this->headerText())
            ->add($this->headerCustomText())
            ->add($this->menus())
            ->add($this->footer())
            ->add($this->newsletter())
            ->add($this->features())
            ->add($this->productPage())
            ->add($this->socialLinks());


        $this->group('home_page_sections', trans('storefront::storefront.tabs.group.home_page_sections'))
            ->add($this->prepareHomeTab($this->sliderBanners(), $homeWeights, $this->sliderBannersStatusSource()))
            ->add($this->prepareHomeTab($this->marqueeSection(), $homeWeights, 'storefront_home_marquee_enabled'))
            ->add($this->prepareHomeTab($this->threeColumnFullWidthBanners(), $homeWeights, 'storefront_three_column_full_width_banners_enabled'))
            ->add($this->prepareHomeTab($this->threeColumnBanners2(), $homeWeights, 'storefront_three_column_banners_2_enabled'))
            ->add($this->prepareHomeTab($this->koleysiyonGridSection(), $homeWeights, 'storefront_koleysiyon_grid_enabled'))
            ->add($this->prepareHomeTab($this->categoryGridBanners(), $homeWeights, 'storefront_category_grid_banners_enabled'))
            ->add($this->prepareHomeTab($this->infoIconsSection(), $homeWeights, 'storefront_info_icons_enabled'))
            ->add($this->prepareHomeTab($this->contentBannerSection(), $homeWeights, 'storefront_buldan_promo_enabled'))
            ->add($this->prepareHomeTab($this->contentBannerSection2(), $homeWeights, 'storefront_content_banner_2_enabled'))
            ->add($this->prepareHomeTab($this->featuredCategories(), $homeWeights, 'storefront_featured_categories_section_enabled'))
            ->add($this->prepareHomeTab($this->productTabsOne(), $homeWeights, 'storefront_product_tabs_1_section_enabled'))
            ->add($this->prepareHomeTab($this->topBrands(), $homeWeights, 'storefront_top_brands_section_enabled'))
            ->add($this->prepareHomeTab($this->flashSaleAndVerticalProducts(), $homeWeights, 'storefront_flash_sale_and_vertical_products_section_enabled'))
            ->add($this->prepareHomeTab($this->twoColumnBanners(), $homeWeights, 'storefront_two_column_banners_enabled'))
            ->add($this->prepareHomeTab($this->productGrid(), $homeWeights, 'storefront_product_grid_section_enabled'))
            ->add($this->prepareHomeTab($this->carouselProducts(), $homeWeights, 'storefront_carousel_section_enabled'))
            ->add($this->prepareHomeTab($this->carouselProducts2(), $homeWeights, 'storefront_carousel_section_2_enabled'))
            ->add($this->prepareHomeTab($this->threeColumnBanners(), $homeWeights, 'storefront_three_column_banners_enabled'))
            ->add($this->prepareHomeTab($this->productTabsTwo(), $homeWeights, 'storefront_product_tabs_2_section_enabled'))
            ->add($this->prepareHomeTab($this->oneColumnBanner(), $homeWeights, 'storefront_one_column_banner_enabled'))
            ->add($this->prepareHomeTab($this->infoIconsSection2(), $homeWeights, 'storefront_info_icons_2_enabled'))
            ->add($this->prepareHomeTab($this->faqSection(), $homeWeights, 'storefront_faq_enabled'))
            ->add($this->prepareHomeTab($this->blogs(), $homeWeights, 'storefront_blogs_section_enabled'))
            ->add($this->prepareHomeTab($this->htmlBlogSection(), $homeWeights, 'storefront_html_blog_enabled'));
    }


    private function marqueeSection()
    {
        return tap(new Tab('marquee', 'Marquee'), function (Tab $tab) {
            $tab->view('storefront::admin.storefront.tabs.marquee');

            $tab->fields([
                'storefront_home_marquee_enabled',
                'storefront_home_marquee_text',
                'storefront_home_marquee_separator',
                'storefront_home_marquee_font_size',
                'storefront_home_marquee_speed',
                'storefront_home_marquee_bg_color',
                'storefront_home_marquee_text_color',
                'storefront_home_marquee_margin_top',
                'storefront_home_marquee_margin_bottom',
            ]);
        });
    }


    private function homePageSectionsWeights()
    {
        $defaultOrder = [
            'slider_banners',
            'marquee',
            'three_column_full_width_banners',
            'three_column_banners_2',
            'koleysiyon_grid',
            'category_grid_banners',
            'info_icons',
            'content_banner',
            'content_banner_2',
            'featured_categories',
            'product_tabs_one',
            'top_brands',
            'flash_sale_and_vertical_products',
            'two_column_banners',
            'product_grid',
            'carousel_products',
            'carousel_products_2',
            'three_column_banners',
            'product_tabs_two',
            'one_column_banner',
            'info_icons_2',
            'faq',
            'blogs',
            'html_blog',
        ];

        $raw = setting('storefront_home_page_sections_order');
        $savedOrder = is_array($raw) ? $raw : json_decode($raw ?: '[]', true);
        $savedOrder = is_array($savedOrder) ? $savedOrder : [];

        $merged = collect($savedOrder)
            ->filter(fn ($name) => in_array($name, $defaultOrder, true))
            ->merge(collect($defaultOrder)->diff($savedOrder))
            ->values();

        return $merged
            ->flip()
            ->map(fn ($index) => (int) $index)
            ->all();
    }


    private function applyWeight($tab, array $weights)
    {
        if (is_null($tab)) {
            return null;
        }

        if (array_key_exists($tab->name, $weights)) {
            $tab->weight($weights[$tab->name]);
        }

        return $tab;
    }


    private function prepareHomeTab($tab, array $weights, $enabledKey = null)
    {
        $tab = $this->applyWeight($tab, $weights);

        if (is_null($tab)) {
            return null;
        }

        $this->decorateHomeTabLabelWithStatus($tab, $enabledKey);

        return $tab;
    }


    private function decorateHomeTabLabelWithStatus(Tab $tab, $enabledKey = null)
    {
        $statusClass = 'is-neutral';
        $statusColor = '#adb5bd';
        $dataAttr = '';

        if (is_array($enabledKey)) {
            $enabled = (bool) ($enabledKey['enabled'] ?? false);
            $statusClass = $enabled ? 'is-active' : 'is-inactive';
            $statusColor = $enabled ? '#28a745' : '#dc3545';

            if (!empty($enabledKey['dataKey'])) {
                $dataAttr = " data-enabled-key=\"{$enabledKey['dataKey']}\"";
            }
        }

        if (!is_null($enabledKey) && !is_array($enabledKey)) {
            $enabled = (bool) setting($enabledKey);
            $statusClass = $enabled ? 'is-active' : 'is-inactive';
            $statusColor = $enabled ? '#28a745' : '#dc3545';
            $dataAttr = " data-enabled-key=\"{$enabledKey}\"";
        }

        $tab->label = $tab->label
            . "<span class=\"home-section-status-dot {$statusClass}\"{$dataAttr} style=\"display:inline-block;width:10px;height:10px;border-radius:50%;margin-left:8px;vertical-align:middle;background:{$statusColor};\"></span>";
    }


    private function sliderBannersStatusSource()
    {
        $sliderSelected = !empty(setting('storefront_slider'));

        $banner1 = setting('storefront_slider_banner_1_file_id');
        $banner2 = setting('storefront_slider_banner_2_file_id');
        $hasAnyBannerImage = !empty($banner1) || !empty($banner2);

        // Slider is the primary dependency; banners being empty shouldn't mark the whole block as inactive.
        $enabled = $sliderSelected;

        // If slider selected, show active (green). If not, inactive (red).
        // Optionally keep this information for future UI updates.
        return [
            'enabled' => $enabled,
            'dataKey' => 'storefront_slider',
            'hasAnyBannerImage' => $hasAnyBannerImage,
        ];
    }


    private function general()
    {
        return tap(new Tab('general', trans('storefront::storefront.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['storefront_slider', 'storefront_copyright_text']);
            $tab->view('storefront::admin.storefront.tabs.general', [
                'display_fonts' => [
                    'Poppins' => 'Poppins',
                    'Rubik' => 'Rubik',
                    'Roboto' => 'Roboto',
                    'Open Sans' => 'Open Sans',
                    'Montserrat' => 'Montserrat',
                    'Nunito' => 'Nunito',
                    'Raleway' => 'Raleway',
                    'Oswald' => 'Oswald',
                    'Quicksand' => 'Quicksand',
                    'Hind' => 'Hind',
                    'Fira Sans' => 'Fira Sans',
                    'Mukta' => 'Mukta',
                    'Karla' => 'Karla',
                    'Barlow' => 'Barlow',
                    'Source Sans 3' => 'Source Sans 3',
                    'IBM Plex Sans' => 'IBM Plex Sans',
                    'Work Sans' => 'Work Sans',
                ],
                'pages' => $this->getPages(),
                'sliders' => $this->getSliders(),
            ]);
        });
    }


    private function customizations()
    {
        return tap(new Tab('customizations', 'Özelleştirmeler'), function (Tab $tab) {
            $tab->weight(27);

            $tab->fields([
                'storefront_grid_variant_badge_enabled',
            ]);

            $tab->view('storefront::admin.storefront.tabs.customizations');
        });
    }


    private function getPages()
    {
        return Page::all()->pluck('name', 'id')
            ->prepend(trans('storefront::storefront.form.please_select'), '');
    }


    private function getSliders()
    {
        return Slider::all()->sortBy('name')->pluck('name', 'id')
            ->prepend(trans('storefront::storefront.form.please_select'), '');
    }


    private function logo()
    {
        return tap(new Tab('logo', trans('storefront::storefront.tabs.logo')), function (Tab $tab) {
            $tab->weight(10);
            $tab->view('storefront::admin.storefront.tabs.logo', [
                'favicon' => $this->getMedia(setting('storefront_favicon')),
                'headerLogo' => $this->getMedia(setting('storefront_header_logo')),
                'footerLogo' => $this->getMedia(setting('storefront_footer_logo')),
                'mailLogo' => $this->getMedia(setting('storefront_mail_logo')),
            ]);
        });
    }


    private function headerText()
    {
        return tap(new Tab('header_text', trans('storefront::storefront.tabs.header_text')), function (Tab $tab) {
            $tab->weight(12);

            $tab->fields([
                'storefront_announcement_bar_enabled',
                'storefront_announcement_bar_items',
                'storefront_announcement_bar_separator',
                'storefront_announcement_bar_font_size',
                'storefront_announcement_bar_speed',
                'storefront_announcement_bar_bg_color',
                'storefront_announcement_bar_text_color',
                'storefront_announcement_bar_show_mobile',
                'storefront_announcement_bar_show_tablet',
                'storefront_announcement_bar_show_desktop',
            ]);

            $tab->view('storefront::admin.storefront.tabs.header_text');
        });
    }


    private function headerCustomText()
    {
        return tap(new Tab('header_custom_text', trans('storefront::storefront.tabs.header_custom_text')), function (Tab $tab) {
            $tab->weight(13);

            $tab->fields([
                'storefront_header_custom_text_enabled',
                'storefront_header_custom_text_content',
                'storefront_header_custom_text_font_size',
                'storefront_header_custom_text_bg_color',
                'storefront_header_custom_text_text_color',
                'storefront_header_custom_text_show_mobile',
                'storefront_header_custom_text_show_tablet',
                'storefront_header_custom_text_show_desktop',
            ]);

            $tab->view('storefront::admin.storefront.tabs.header_custom_text');
        });
    }


    private function getMedia($fileId)
    {
        return Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });
    }


    private function menus()
    {
        return tap(new Tab('menus', trans('storefront::storefront.tabs.menus')), function (Tab $tab) {
            $tab->weight(15);

            $tab->fields([
                'storefront_primary_menu',
                'storefront_category_menu',
                'storefront_footer_menu',
                'storefront_footer_menu_title',
            ]);

            $tab->view('storefront::admin.storefront.tabs.menus', [
                'menus' => $this->getMenus(),
            ]);
        });
    }


    private function getMenus()
    {
        return Menu::all()->pluck('name', 'id')
            ->prepend(trans('storefront::storefront.form.please_select'), '');
    }


    private function footer()
    {
        return tap(new Tab('footer', trans('storefront::storefront.tabs.footer')), function (Tab $tab) {
            $tab->weight(17);
            $tab->view('storefront::admin.storefront.tabs.footer', [
                'tags' => Tag::list(),
                'acceptedPaymentMethodsImage' => $this->getMedia(setting('storefront_accepted_payment_methods_image')),
            ]);
        });
    }


    private function newsletter()
    {
        if (!setting('newsletter_enabled')) {
            return;
        }

        return tap(new Tab('newsletter', trans('storefront::storefront.tabs.newsletter')), function (Tab $tab) {
            $tab->weight(18);
            $tab->view('storefront::admin.storefront.tabs.newsletter', [
                'newsletterBgImage' => $this->getMedia(setting('storefront_newsletter_bg_image')),
            ]);
        });
    }


    private function features()
    {
        return tap(new Tab('features', trans('storefront::storefront.tabs.features')), function (Tab $tab) {
            $tab->weight(20);
            $tab->view('storefront::admin.storefront.tabs.features');
        });
    }


    private function productPage()
    {
        return tap(new Tab('product_page', trans('storefront::storefront.tabs.product_page')), function (Tab $tab) {
            $tab->weight(22);
            $tab->view('storefront::admin.storefront.tabs.product_page', [
                'banner' => Banner::getProductPageBanner(),
            ]);
        });
    }


    private function socialLinks()
    {
        return tap(new Tab('social_links', trans('storefront::storefront.tabs.social_links')), function (Tab $tab) {
            $tab->weight(25);

            $tab->fields([
                'storefront_fb_link',
                'storefront_twitter_link',
                'storefront_instagram_link',
                'storefront_linkedin_link',
                'storefront_pinterest_link',
                'storefront_gplus_link',
                'storefront_youtube_link',
            ]);

            $tab->view('storefront::admin.storefront.tabs.social_links');
        });
    }


    private function sliderBanners()
    {
        return tap(new Tab('slider_banners', trans('storefront::storefront.tabs.slider_banners')), function (Tab $tab) {
            $tab->weight(30);
            $tab->view('storefront::admin.storefront.tabs.slider_banners', [
                'banners' => Banner::getSliderBanners(),
            ]);
        });
    }


    private function threeColumnFullWidthBanners()
    {
        return tap(new Tab('three_column_full_width_banners', trans('storefront::storefront.tabs.three_column_full_width_banners')), function (Tab $tab) {
            $tab->weight(35);
            $tab->view('storefront::admin.storefront.tabs.three_column_full_width_banners', [
                'banners' => Banner::getThreeColumnFullWidthBanners(),
            ]);
        });
    }


    private function categoryGridBanners()
    {
        return tap(new Tab('category_grid_banners', trans('storefront::storefront.tabs.category_grid_banners')), function (Tab $tab) {
            $tab->weight(45);
            $tab->view('storefront::admin.storefront.tabs.category_grid_banners', [
                'banners' => Banner::getCategoryGridBanners(),
            ]);
        });
    }


    private function koleysiyonGridSection()
    {
        return tap(new Tab('koleysiyon_grid', 'Koleysiyon Grid'), function (Tab $tab) {
            $tab->weight(44);

            $tab->fields([
                'storefront_koleysiyon_grid_enabled',
                'storefront_koleysiyon_grid_title',
                'storefront_koleysiyon_grid_subtitle',
                'storefront_koleysiyon_grid_card_1_image',
                'storefront_koleysiyon_grid_card_1_title',
                'storefront_koleysiyon_grid_card_1_text',
                'storefront_koleysiyon_grid_card_1_button_text',
                'storefront_koleysiyon_grid_card_1_button_url',
                'storefront_koleysiyon_grid_card_2_image',
                'storefront_koleysiyon_grid_card_2_title',
                'storefront_koleysiyon_grid_card_2_text',
                'storefront_koleysiyon_grid_card_2_button_text',
                'storefront_koleysiyon_grid_card_2_button_url',
                'storefront_koleysiyon_grid_card_3_image',
                'storefront_koleysiyon_grid_card_3_title',
                'storefront_koleysiyon_grid_card_3_text',
                'storefront_koleysiyon_grid_card_3_button_text',
                'storefront_koleysiyon_grid_card_3_button_url',
                'storefront_koleysiyon_grid_card_4_image',
                'storefront_koleysiyon_grid_card_4_title',
                'storefront_koleysiyon_grid_card_4_text',
                'storefront_koleysiyon_grid_card_4_button_text',
                'storefront_koleysiyon_grid_card_4_button_url',
            ]);

            $tab->view('storefront::admin.storefront.tabs.koleysiyon_grid', [
                'card1Image' => $this->getMedia(setting('storefront_koleysiyon_grid_card_1_image')),
                'card2Image' => $this->getMedia(setting('storefront_koleysiyon_grid_card_2_image')),
                'card3Image' => $this->getMedia(setting('storefront_koleysiyon_grid_card_3_image')),
                'card4Image' => $this->getMedia(setting('storefront_koleysiyon_grid_card_4_image')),
            ]);
        });
    }


    private function featuredCategories()
    {
        return tap(new Tab('featured_categories', trans('storefront::storefront.tabs.featured_categories')), function (Tab $tab) {
            $tab->weight(48);
            $tab->view('storefront::admin.storefront.tabs.featured_categories', [
                'categoryOneProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_1_products'),
                'categoryTwoProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_2_products'),
                'categoryThreeProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_3_products'),
                'categoryFourProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_4_products'),
                'categoryFiveProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_5_products'),
                'categorySixProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_6_products'),
            ]);
        });
    }


    private function getProductListFromSetting($key)
    {
        return ProductRepository::list(setting($key, []));
    }


    private function productTabsOne()
    {
        return tap(new Tab('product_tabs_one', trans('storefront::storefront.tabs.product_tabs_one')), function (Tab $tab) {
            $tab->weight(47);
            $tab->view('storefront::admin.storefront.tabs.product_tabs_one', [
                'tabOneProducts' => $this->getProductListFromSetting('storefront_product_tabs_1_section_tab_1_products'),
                'tabTwoProducts' => $this->getProductListFromSetting('storefront_product_tabs_1_section_tab_2_products'),
                'tabThreeProducts' => $this->getProductListFromSetting('storefront_product_tabs_1_section_tab_3_products'),
                'tabFourProducts' => $this->getProductListFromSetting('storefront_product_tabs_1_section_tab_4_products'),
            ]);
        });
    }


    private function topBrands()
    {
        if (!auth()->user()->hasAccess(['admin.brands.index'])) {
            return;
        }

        return tap(new Tab('top_brands', trans('storefront::storefront.tabs.top_brands')), function (Tab $tab) {
            $tab->weight(50);
            $tab->view('storefront::admin.storefront.tabs.top_brands', [
                'brands' => Brand::list(),
            ]);
        });
    }


    private function flashSaleAndVerticalProducts()
    {
        return tap(new Tab('flash_sale_and_vertical_products', trans('storefront::storefront.tabs.flash_sale_and_vertical_products')), function (Tab $tab) {
            $tab->weight(60);
            $tab->view('storefront::admin.storefront.tabs.flash_sale_and_vertical_products', [
                'flashSales' => $this->getFlashSales(),
                'verticalProductsOne' => $this->getProductListFromSetting('storefront_vertical_products_1_products'),
                'verticalProductsTwo' => $this->getProductListFromSetting('storefront_vertical_products_2_products'),
                'verticalProductsThree' => $this->getProductListFromSetting('storefront_vertical_products_3_products'),
            ]);
        });
    }


    private function getFlashSales()
    {
        return FlashSale::all()->pluck('campaign_name', 'id')
            ->prepend(trans('admin::admin.form.please_select'), '');
    }


    private function twoColumnBanners()
    {
        return tap(new Tab('two_column_banners', trans('storefront::storefront.tabs.two_column_banners')), function (Tab $tab) {
            $tab->weight(65);
            $tab->view('storefront::admin.storefront.tabs.two_column_banners', [
                'banners' => Banner::getTwoColumnBanners(),
            ]);
        });
    }


    private function productGrid()
    {
        return tap(new Tab('product_grid', trans('storefront::storefront.tabs.product_grid')), function (Tab $tab) {
            $tab->weight(70);

            $tab->fields([
                'storefront_product_grid_section_enabled',
                'storefront_product_grid_section_tab_1_products',
                'storefront_product_grid_section_tab_2_products',
                'storefront_product_grid_section_tab_3_products',
                'storefront_product_grid_section_tab_4_products',
            ]);

            $tab->view('storefront::admin.storefront.tabs.product_grid', [
                'tabOneProducts' => $this->getProductListFromSetting('storefront_product_grid_section_tab_1_products'),
                'tabTwoProducts' => $this->getProductListFromSetting('storefront_product_grid_section_tab_2_products'),
                'tabThreeProducts' => $this->getProductListFromSetting('storefront_product_grid_section_tab_3_products'),
                'tabFourProducts' => $this->getProductListFromSetting('storefront_product_grid_section_tab_4_products'),
            ]);
        });
    }


    private function carouselProducts()
    {
        return tap(new Tab('carousel_products', 'Carousel Product'), function (Tab $tab) {
            $tab->weight(72);
            $tab->view('storefront::admin.storefront.tabs.carousel_products', [
                'products' => $this->getProductListFromSetting('storefront_carousel_section_products'),
                'tags' => Tag::list(),
            ]);

            $tab->fields([
                'storefront_carousel_section_show_dots',
                'storefront_carousel_section_show_arrows',
                'storefront_carousel_section_products',
                'storefront_carousel_section_tags',
                'storefront_carousel_section_enabled',
                'storefront_carousel_section_autoplay',
                'storefront_carousel_section_autoplay_speed',
                'storefront_carousel_section_per_row_mobile',
                'storefront_carousel_section_per_row_tablet',
                'storefront_carousel_section_per_row_desktop',
                'storefront_carousel_section_sort_by',
                'storefront_carousel_section_only_in_stock',
            ]);
        });
    }


    private function carouselProducts2()
    {
        return tap(new Tab('carousel_products_2', 'Carousel Product 2'), function (Tab $tab) {
            $tab->weight(73);
            $tab->view('storefront::admin.storefront.tabs.carousel_products_2', [
                'products' => $this->getProductListFromSetting('storefront_carousel_section_2_products'),
                'tags' => Tag::list(),
            ]);

            $tab->fields([
                'storefront_carousel_section_2_show_dots',
                'storefront_carousel_section_2_show_arrows',
                'storefront_carousel_section_2_products',
                'storefront_carousel_section_2_tags',
                'storefront_carousel_section_2_enabled',
                'storefront_carousel_section_2_autoplay',
                'storefront_carousel_section_2_autoplay_speed',
                'storefront_carousel_section_2_per_row_mobile',
                'storefront_carousel_section_2_per_row_tablet',
                'storefront_carousel_section_2_per_row_desktop',
                'storefront_carousel_section_2_sort_by',
                'storefront_carousel_section_2_only_in_stock',
            ]);
        });
    }


    private function threeColumnBanners()
    {
        return tap(new Tab('three_column_banners', trans('storefront::storefront.tabs.three_column_banners')), function (Tab $tab) {
            $tab->weight(75);
            $tab->view('storefront::admin.storefront.tabs.three_column_banners', [
                'banners' => Banner::getThreeColumnBanners(),
            ]);
        });
    }


    private function threeColumnBanners2()
    {
        return tap(new Tab('three_column_banners_2', trans('storefront::storefront.tabs.three_column_banners_2')), function (Tab $tab) {
            $tab->weight(76);
            $tab->view('storefront::admin.storefront.tabs.three_column_banners_2', [
                'banners' => Banner::getThreeColumnBanners2(),
            ]);
        });
    }


    private function infoIconsSection()
    {
        return tap(new Tab('info_icons', 'Bilgi İkonları'), function (Tab $tab) {
            $tab->weight(46);

            $tab->fields([
                'storefront_info_icons_enabled',
                'storefront_info_icons_icon_1_image',
                'storefront_info_icons_icon_1_title',
                'storefront_info_icons_icon_1_text',
                'storefront_info_icons_icon_2_image',
                'storefront_info_icons_icon_2_title',
                'storefront_info_icons_icon_2_text',
                'storefront_info_icons_icon_3_image',
                'storefront_info_icons_icon_3_title',
                'storefront_info_icons_icon_3_text',
            ]);

            $tab->view('storefront::admin.storefront.tabs.info_icons', [
                'icon1' => $this->getMedia(setting('storefront_info_icons_icon_1_image')),
                'icon2' => $this->getMedia(setting('storefront_info_icons_icon_2_image')),
                'icon3' => $this->getMedia(setting('storefront_info_icons_icon_3_image')),
            ]);
        });
    }


    private function infoIconsSection2()
    {
        return tap(new Tab('info_icons_2', 'Bilgi İkonları 2'), function (Tab $tab) {
            $tab->weight(85);

            $tab->fields([
                'storefront_info_icons_2_enabled',
                'storefront_info_icons_2_icon_1_image',
                'storefront_info_icons_2_icon_1_title',
                'storefront_info_icons_2_icon_1_text',
                'storefront_info_icons_2_icon_2_image',
                'storefront_info_icons_2_icon_2_title',
                'storefront_info_icons_2_icon_2_text',
                'storefront_info_icons_2_icon_3_image',
                'storefront_info_icons_2_icon_3_title',
                'storefront_info_icons_2_icon_3_text',
            ]);

            $tab->view('storefront::admin.storefront.tabs.info_icons_2', [
                'icon1' => $this->getMedia(setting('storefront_info_icons_2_icon_1_image')),
                'icon2' => $this->getMedia(setting('storefront_info_icons_2_icon_2_image')),
                'icon3' => $this->getMedia(setting('storefront_info_icons_2_icon_3_image')),
            ]);
        });
    }


    private function faqSection()
    {
        return tap(new Tab('faq', 'FAQ Page'), function (Tab $tab) {
            $tab->weight(86);

            $tab->fields([
                'storefront_faq_enabled',
                'storefront_faq_title',
                'storefront_faq_items',
            ]);

            $tab->view('storefront::admin.storefront.tabs.faq');
        });
    }


    private function productTabsTwo()
    {
        return tap(new Tab('product_tabs_two', trans('storefront::storefront.tabs.product_tabs_two')), function (Tab $tab) {
            $tab->weight(80);
            $tab->view('storefront::admin.storefront.tabs.product_tabs_two', [
                'tabOneProducts' => $this->getProductListFromSetting('storefront_product_tabs_2_section_tab_1_products'),
                'tabTwoProducts' => $this->getProductListFromSetting('storefront_product_tabs_2_section_tab_2_products'),
                'tabThreeProducts' => $this->getProductListFromSetting('storefront_product_tabs_2_section_tab_3_products'),
                'tabFourProducts' => $this->getProductListFromSetting('storefront_product_tabs_2_section_tab_4_products'),
            ]);
        });
    }


    private function oneColumnBanner()
    {
        return tap(new Tab('one_column_banner', trans('storefront::storefront.tabs.one_column_banner')), function (Tab $tab) {
            $tab->weight(85);
            $tab->view('storefront::admin.storefront.tabs.one_column_banner', [
                'banner' => Banner::getOneColumnBanner(),
            ]);
        });
    }


    private function contentBannerSection()
    {
        return tap(new Tab('content_banner', 'Content Banner'), function (Tab $tab) {
            $tab->weight(46);

            $tab->fields([
                'storefront_buldan_promo_enabled',
                'storefront_buldan_promo_image',
                'storefront_buldan_promo_title',
                'storefront_buldan_promo_content',
                'storefront_buldan_promo_button_text',
                'storefront_buldan_promo_button_url',
            ]);

            $tab->view('storefront::admin.storefront.tabs.content_banner', [
                'image' => $this->getMedia(setting('storefront_buldan_promo_image')),
            ]);
        });
    }


    private function contentBannerSection2()
    {
        return tap(new Tab('content_banner_2', 'Content Banner 2'), function (Tab $tab) {
            $tab->weight(47);

            $tab->fields([
                'storefront_content_banner_2_enabled',
                'storefront_content_banner_2_image',
                'storefront_content_banner_2_title',
                'storefront_content_banner_2_content',
                'storefront_content_banner_2_button_text',
                'storefront_content_banner_2_button_url',
            ]);

            $tab->view('storefront::admin.storefront.tabs.content_banner_2', [
                'image' => $this->getMedia(setting('storefront_content_banner_2_image')),
            ]);
        });
    }


    private function blogs()
    {
        return tap(new Tab('blogs', trans('storefront::storefront.tabs.blogs')), function (Tab $tab) {
            $tab->weight(87);
            $tab->view('storefront::admin.storefront.tabs.blogs');
        });
    }


    private function htmlBlogSection()
    {
        return tap(new Tab('html_blog', 'Html Blog'), function (Tab $tab) {
            $tab->weight(88);

            $tab->fields([
                'storefront_html_blog_enabled',
                'storefront_html_blog_content',
            ]);

            $tab->view('storefront::admin.storefront.tabs.html_blog');
        });
    }
}
