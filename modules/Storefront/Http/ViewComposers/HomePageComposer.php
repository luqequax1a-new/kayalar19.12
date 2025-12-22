<?php

namespace Modules\Storefront\Http\ViewComposers;

use Illuminate\View\View;
use Modules\Storefront\Banner;
use Modules\Storefront\Feature;
use Modules\Brand\Entities\Brand;
use Illuminate\Support\Collection;
use Modules\Blog\Entities\BlogPost;
use Modules\Slider\Entities\Slider;
use Illuminate\Support\Facades\Cache;
use Modules\Category\Entities\Category;
use Modules\Media\Entities\File;
use Modules\Storefront\Http\Controllers\CarouselProductController;
use Modules\Review\Entities\Review;

class HomePageComposer
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose($view)
    {
        $view->with([
            'slider' => Cache::rememberForever(
                'storefront_home_slider:' . setting('storefront_slider'),
                fn () => Slider::findWithSlides(setting('storefront_slider')),
            ),
            'sliderBanners' => Cache::rememberForever(
                'storefront_home_slider_banners',
                fn () => Banner::getSliderBanners(),
            ),
            'features' => Feature::all(),
            'featuredCategories' => $this->featuredCategoriesSection(),
            'threeColumnFullWidthBanners' => $this->threeColumnFullWidthBanners(),
            'threeColumnBanners2' => $this->threeColumnBanners2(),
            'categoryGridBanners' => $this->categoryGridBanners(),
            'koleysiyonGrid' => $this->koleysiyonGrid(),
            'productTabsOne' => $this->productTabsOne(),
            'topBrands' => $this->topBrands(),
            'flashSale' => $this->flashSale(),
            'twoColumnBanners' => $this->twoColumnBanners(),
            'gridProducts' => $this->gridProducts(),
            'carouselProducts' => $this->carouselProducts(),
            'carouselProducts2' => $this->carouselProducts2(),
            'threeColumnBanners' => $this->threeColumnBanners(),
            'productTabsTwo' => $this->productTabsTwo(),
            'oneColumnBanner' => $this->oneColumnBanner(),
            'blog' => $this->blog(),
            'infoIcons' => $this->infoIcons(),
            'infoIcons2' => $this->infoIcons2(),
            'htmlBlog' => $this->htmlBlog(),
            'faq' => $this->faq(),
            'contentBanner' => $this->contentBanner(),
            'contentBanner2' => $this->contentBanner2(),
        ]);
    }


    private function featuredCategoriesSection()
    {
        if (!setting('storefront_featured_categories_section_enabled')) {
            return;
        }

        return [
            'title' => setting('storefront_featured_categories_section_title'),
            'subtitle' => setting('storefront_featured_categories_section_subtitle'),
            'categories' => $this->getFeaturedCategories(),
        ];
    }


    private function getFeaturedCategories()
    {
        $categoryIds = Collection::times(6, function ($number) {
            if (!is_null(setting("storefront_featured_categories_section_category_{$number}_product_type"))) {
                return setting("storefront_featured_categories_section_category_{$number}_category_id");
            }
        })->filter();

        return Category::with('files')
            ->whereIn('id', $categoryIds)
            ->when($categoryIds->isNotEmpty(), function ($query) use ($categoryIds) {
                $query->orderByRaw("FIELD(id, {$categoryIds->filter()->implode(',')})");
            })
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'logo' => $category->logo,
                ];
            });
    }


    private function threeColumnFullWidthBanners()
    {
        if (setting('storefront_three_column_full_width_banners_enabled')) {
            return Banner::getThreeColumnFullWidthBanners();
        }
    }


    private function productTabsOne()
    {
        if (!setting('storefront_product_tabs_1_section_enabled')) {
            return;
        }

        return Collection::times(4, function ($number) {
            if (!is_null(setting("storefront_product_tabs_1_section_tab_{$number}_product_type"))) {
                return setting("storefront_product_tabs_1_section_tab_{$number}_title");
            }
        })->filter();
    }


    private function topBrands()
    {
        if (!setting('storefront_top_brands_section_enabled')) {
            return collect();
        }

        $topBrandIds = setting('storefront_top_brands', []);

        return Cache::rememberForever(md5('storefront_top_brands:' . serialize($topBrandIds)), function () use ($topBrandIds) {
            return Brand::with('files')
                ->whereIn('id', $topBrandIds)
                ->when(!empty($topBrandIds), function ($query) use ($topBrandIds) {
                    $topBrandIdsString = collect($topBrandIds)->filter()->implode(',');

                    $query->orderByRaw("FIELD(id, {$topBrandIdsString})");
                })
                ->get()
                ->map(function (Brand $brand) {
                    return [
                        'url' => $brand->url(),
                        'logo' => $brand->getLogoAttribute(),
                    ];
                });
        });
    }


    private function flashSale()
    {
        return [
            'title' => setting('storefront_flash_sale_title'),
            'vertical_products_1_title' => setting('storefront_vertical_products_1_title'),
            'vertical_products_2_title' => setting('storefront_vertical_products_2_title'),
            'vertical_products_3_title' => setting('storefront_vertical_products_3_title'),
        ];
    }


    private function twoColumnBanners()
    {
        if (setting('storefront_two_column_banners_enabled')) {
            return Banner::getTwoColumnBanners();
        }
    }


    private function gridProducts()
    {
        if (!setting('storefront_product_grid_section_enabled')) {
            return;
        }

        return Collection::times(4, function ($number) {
            if (!is_null(setting("storefront_product_grid_section_tab_{$number}_product_type"))) {
                return setting("storefront_product_grid_section_tab_{$number}_title");
            }
        })->filter();
    }


    private function carouselProducts()
    {
        if (!setting('storefront_carousel_section_enabled')) {
            return;
        }

        /** @var CarouselProductController $controller */
        $controller = app(CarouselProductController::class);
        $response = $controller->index();

        $products = collect(json_decode($response->getContent(), true));

        return [
            'title' => setting('storefront_carousel_section_title'),
            'products' => $products,
        ];
    }


    private function carouselProducts2()
    {
        if (!setting('storefront_carousel_section_2_enabled')) {
            return;
        }

        /** @var \Modules\Storefront\Http\Controllers\CarouselProduct2Controller $controller */
        $controller = app(\Modules\Storefront\Http\Controllers\CarouselProduct2Controller::class);
        $response = $controller->index();

        $products = collect(json_decode($response->getContent(), true));

        return [
            'title' => setting('storefront_carousel_section_2_title'),
            'products' => $products,
        ];
    }


    private function threeColumnBanners()
    {
        if (setting('storefront_three_column_banners_enabled')) {
            return Banner::getThreeColumnBanners();
        }
    }


    private function threeColumnBanners2()
    {
        if (setting('storefront_three_column_banners_2_enabled')) {
            return Banner::getThreeColumnBanners2();
        }
    }


    private function categoryGridBanners()
    {
        if (setting('storefront_category_grid_banners_enabled')) {
            return Banner::getCategoryGridBanners();
        }
    }


    private function koleysiyonGrid()
    {
        if (! setting('storefront_koleysiyon_grid_enabled')) {
            return;
        }

        $items = collect(range(1, 4))->map(function ($number) {
            $title = setting("storefront_koleysiyon_grid_card_{$number}_title");
            $text = setting("storefront_koleysiyon_grid_card_{$number}_text");
            $buttonText = setting("storefront_koleysiyon_grid_card_{$number}_button_text");
            $buttonUrl = setting("storefront_koleysiyon_grid_card_{$number}_button_url");

            if (empty($title) && empty($text) && empty($buttonText) && empty($buttonUrl)) {
                return null;
            }

            return [
                'image' => $this->getMedia(setting("storefront_koleysiyon_grid_card_{$number}_image")),
                'title' => $title,
                'text' => $text,
                'button_text' => $buttonText,
                'button_url' => $buttonUrl,
            ];
        })->filter()->values();

        if ($items->isEmpty()) {
            return;
        }

        return [
            'title' => setting('storefront_koleysiyon_grid_title'),
            'subtitle' => setting('storefront_koleysiyon_grid_subtitle'),
            'items' => $items,
        ];
    }


    private function productTabsTwo()
    {
        if (!setting('storefront_product_tabs_2_section_enabled')) {
            return;
        }

        $tabs = Collection::times(4, function ($number) {
            if (!is_null(setting("storefront_product_tabs_2_section_tab_{$number}_product_type"))) {
                return setting("storefront_product_tabs_2_section_tab_{$number}_title");
            }
        })->filter();

        return [
            'title' => setting('storefront_product_tabs_2_section_title'),
            'tabs' => $tabs,
        ];
    }


    private function oneColumnBanner()
    {
        if (setting('storefront_one_column_banner_enabled')) {
            return Banner::getOneColumnBanner();
        }
    }


    private function infoIcons()
    {
        if (!setting('storefront_info_icons_enabled')) {
            return;
        }

        return [
            'items' => collect([1, 2, 3, 4])->map(function ($number) {
                return [
                    'image' => $this->getMedia(setting("storefront_info_icons_icon_{$number}_image")),
                    'title' => setting("storefront_info_icons_icon_{$number}_title"),
                    'text' => setting("storefront_info_icons_icon_{$number}_text"),
                ];
            }),
        ];
    }


    private function infoIcons2()
    {
        if (!setting('storefront_info_icons_2_enabled')) {
            return;
        }

        return [
            'items' => collect([1, 2, 3])->map(function ($number) {
                return [
                    'image' => $this->getMedia(setting("storefront_info_icons_2_icon_{$number}_image")),
                    'title' => setting("storefront_info_icons_2_icon_{$number}_title"),
                    'text' => setting("storefront_info_icons_2_icon_{$number}_text"),
                ];
            }),
        ];
    }


    private function htmlBlog()
    {
        if (!setting('storefront_html_blog_enabled')) {
            return;
        }

        return [
            'content' => setting('storefront_html_blog_content'),
        ];
    }


    private function faq()
    {
        if (!setting('storefront_faq_enabled')) {
            return;
        }

        $rawItems = setting('storefront_faq_items');
        $itemsSetting = is_array($rawItems) ? $rawItems : json_decode($rawItems ?? '[]', true);

        $items = collect($itemsSetting ?: [])->map(function ($item) {
            $question = $item['question'] ?? null;
            $answer = $item['answer'] ?? null;

            if (empty($question) || empty($answer)) {
                return null;
            }

            return [
                'question' => $question,
                'answer' => $answer,
            ];
        })->filter()->values();

        if ($items->isEmpty()) {
            return;
        }

        return [
            'title' => setting('storefront_faq_title') ?: 'Sıkça Sorulan Sorular',
            'items' => $items,
        ];
    }


    private function contentBanner()
    {
        if (!setting('storefront_buldan_promo_enabled')) {
            return;
        }

        return [
            'image' => $this->getMedia(setting('storefront_buldan_promo_image')),
            'title' => setting('storefront_buldan_promo_title'),
            'content' => setting('storefront_buldan_promo_content'),
            'button_text' => setting('storefront_buldan_promo_button_text'),
            'button_url' => setting('storefront_buldan_promo_button_url'),
        ];
    }


    private function contentBanner2()
    {
        if (!setting('storefront_content_banner_2_enabled')) {
            return;
        }

        return [
            'image' => $this->getMedia(setting('storefront_content_banner_2_image')),
            'title' => setting('storefront_content_banner_2_title'),
            'content' => setting('storefront_content_banner_2_content'),
            'button_text' => setting('storefront_content_banner_2_button_text'),
            'button_url' => setting('storefront_content_banner_2_button_url'),
        ];
    }


    private function getMedia($fileId)
    {
        return Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });
    }


    private function blog()
    {
        if (setting('storefront_blogs_section_enabled')) {
            $blogPosts = BlogPost::published()
                ->latest()
                ->take(setting('storefront_recent_blogs') ?? 10)
                ->get();

            return [
                'title' => setting('storefront_blogs_section_title'),
                'blogPosts' => $blogPosts,
            ];
        }
    }
}
