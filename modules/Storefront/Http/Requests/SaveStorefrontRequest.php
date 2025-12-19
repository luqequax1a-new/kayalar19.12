<?php

namespace Modules\Storefront\Http\Requests;

use Modules\Core\Http\Requests\Request;

class SaveStorefrontRequest extends Request
{
    /**
     * Array of attributes that should be merged with null
     * if attribute is not found in the current request.
     *
     * @var array
     */
    private $shouldCheck = [
        'storefront_footer_tags',
        'storefront_carousel_section_tags',
        'storefront_home_page_sections_order',
        'storefront_product_page_sections_order',
        'storefront_home_marquee_enabled',
        'storefront_product_page_custom_tab_enabled',
        'storefront_product_page_custom_tab_2_enabled',
        'storefront_product_page_custom_text_enabled',
        'storefront_product_page_custom_html_enabled',
        'storefront_product_page_image_banner_enabled',
        'storefront_product_page_info_icons_enabled',
        'storefront_announcement_bar_enabled',
        'storefront_announcement_bar_show_mobile',
        'storefront_announcement_bar_show_tablet',
        'storefront_announcement_bar_show_desktop',
        'storefront_carousel_section_show_dots',
        'storefront_carousel_section_show_arrows',
        'storefront_carousel_section_2_show_dots',
        'storefront_carousel_section_2_show_arrows',
        'storefront_header_custom_text_enabled',
        'storefront_header_custom_text_show_mobile',
        'storefront_header_custom_text_show_tablet',
        'storefront_header_custom_text_show_desktop',
        'storefront_featured_categories_section_category_1_products',
        'storefront_featured_categories_section_category_2_products',
        'storefront_featured_categories_section_category_3_products',
        'storefront_featured_categories_section_category_4_products',
        'storefront_featured_categories_section_category_5_products',
        'storefront_featured_categories_section_category_6_products',
        'storefront_product_tabs_1_section_tab_1_products',
        'storefront_product_tabs_1_section_tab_2_products',
        'storefront_product_tabs_1_section_tab_3_products',
        'storefront_product_tabs_1_section_tab_4_products',
        'storefront_top_brands',
        'storefront_vertical_products_1_products',
        'storefront_vertical_products_2_products',
        'storefront_vertical_products_3_products',
        'storefront_product_grid_section_tab_1_products',
        'storefront_product_grid_section_tab_2_products',
        'storefront_product_grid_section_tab_3_products',
        'storefront_product_grid_section_tab_4_products',
        'storefront_product_tabs_2_section_tab_1_products',
        'storefront_product_tabs_2_section_tab_2_products',
        'storefront_product_tabs_2_section_tab_3_products',
        'storefront_product_tabs_2_section_tab_4_products',
    ];


    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        foreach ($this->shouldCheck as $attribute) {
            if (!$this->has($attribute)) {
                if (in_array($attribute, [
                    'storefront_home_marquee_enabled',
                    'storefront_product_page_custom_tab_enabled',
                    'storefront_product_page_custom_tab_2_enabled',
                    'storefront_product_page_custom_text_enabled',
                    'storefront_product_page_custom_html_enabled',
                    'storefront_product_page_image_banner_enabled',
                    'storefront_product_page_info_icons_enabled',
                    'storefront_announcement_bar_enabled',
                    'storefront_announcement_bar_show_mobile',
                    'storefront_announcement_bar_show_tablet',
                    'storefront_announcement_bar_show_desktop',
                    'storefront_carousel_section_show_dots',
                    'storefront_carousel_section_show_arrows',
                    'storefront_carousel_section_2_show_dots',
                    'storefront_carousel_section_2_show_arrows',
                    'storefront_header_custom_text_enabled',
                    'storefront_header_custom_text_show_mobile',
                    'storefront_header_custom_text_show_tablet',
                    'storefront_header_custom_text_show_desktop',
                ], true)) {
                    $this->merge([$attribute => 0]);
                } else {
                    $this->merge([$attribute => null]);
                }
            }
        }

        return $this->all();
    }
}
