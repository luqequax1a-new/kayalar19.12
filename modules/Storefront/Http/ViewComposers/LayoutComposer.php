<?php

namespace Modules\Storefront\Http\ViewComposers;

use Exception;
use Illuminate\View\View;
use Spatie\SchemaOrg\Schema;
use Modules\Compare\Compare;
use Modules\Tag\Entities\Tag;
use Modules\Cart\Facades\Cart;
use Modules\Menu\Entities\Menu;
use Modules\Page\Entities\Page;
use Modules\Media\Entities\File;
use Modules\Menu\MegaMenu\MegaMenu;
use Illuminate\Support\Facades\Cache;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\SearchTerm;
use Modules\Address\StoreAddress;
use Modules\Brand\Entities\Brand;

class LayoutComposer
{
    /**
     * @var Compare
     */
    private $compare;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $brands;

    /**
     * Create a new view composer instance.
     *
     * @param Compare $compare
     */
    public function __construct(Compare $compare)
    {
        $this->compare = $compare;
    }


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
            'themeColor' => $this->getThemeColor(),
            'compareCount' => $this->compare->count(),
            'wishlistCount' => $this->getWishlistCount(),
            'cartQuantity' => $this->getCartQuantity(),
            'favicon' => $this->getFavicon(),
            'logo' => $this->getHeaderLogo(),
            'newsletterBgImage' => $this->getNewsletterBgImage(),
            'privacyPageUrl' => $this->getPrivacyPageUrl(),
            'categories' => $this->getCategories(),
            'mostSearchedKeywords' => $this->getMostSearchedKeywords(),
            'primaryMenu' => $this->getPrimaryMenu(),
            'categoryMenu' => $this->getCategoryMenu(),
            'footerMenuOne' => $this->getFooterMenuOne(),
            'footerMenuTwo' => $this->getFooterMenuTwo(),
            'footerTags' => $this->getFooterTags(),
            'copyrightText' => $this->getCopyrightText(),
            'acceptedPaymentMethodsImage' => $this->getAcceptedPaymentMethodsImage(),
            'schemaMarkup' => $this->getSchemaMarkup(),
            'brands' => $this->getBrands(),
        ]);
    }


    private function getWishlistCount()
    {
        return auth()->check() ? auth()->user()->wishlist()->get()->count() : 0;
    }


    private function getCartQuantity()
    {
        return Cart::instance()->count();
    }


    public function footerTagsCallback($tagIds)
    {
        return function () use ($tagIds) {
            return Tag::whereIn('id', $tagIds)
                ->when(!empty($tagIds), function ($query) use ($tagIds) {
                    $tagIdsString = collect($tagIds)->filter()->implode(',');

                    $query->orderByRaw("FIELD(id, {$tagIdsString})");
                })
                ->get();
        };
    }


    private function getThemeColor()
    {
        try {
            return tinycolor(storefront_theme_color());
        } catch (Exception $e) {
            return tinycolor('#0068e1');
        }
    }


    private function getFavicon()
    {
        return $this->getMedia(setting('storefront_favicon'))->path;
    }


    private function getMedia($fileId)
    {
        $id = (int) ($fileId ?? 0);

        if ($id <= 0) {
            return new File();
        }

        return Cache::rememberForever(md5("files.{$id}"), function () use ($id) {
            return File::findOrNew($id);
        });
    }


    private function getHeaderLogo()
    {
        return $this->getMedia(setting('storefront_header_logo'))->path;
    }


    private function getNewsletterBgImage()
    {
        return $this->getMedia(setting('storefront_newsletter_bg_image'))->path;
    }


    private function getPrivacyPageUrl()
    {
        return Cache::tags('settings')->rememberForever('privacy_page_url', function () {
            return Page::urlForPage(setting('storefront_privacy_page'));
        });
    }


    private function getCategories()
    {
        return Category::searchable();
    }


    private function getMostSearchedKeywords()
    {
        return Cache::remember('most_searched_keywords', now()->addHour(), function () {
            return SearchTerm::select('term')->orderByDesc('hits')->take(5)->pluck('term');
        });
    }


    private function getPrimaryMenu()
    {
        return new MegaMenu(setting('storefront_primary_menu'));
    }


    private function getCategoryMenu()
    {
        return new MegaMenu(setting('storefront_category_menu'));
    }


    private function getFooterMenuOne()
    {
        return $this->getFooterMenu(setting('storefront_footer_menu_one'));
    }


    private function getFooterMenu($menuId)
    {
        $key = 'storefront:globals:' . locale() . ':footer_menu:' . $menuId . ':v1';

        return Cache::store('file')->remember($key, now()->addMinutes(10), function () use ($menuId) {
            return Menu::for($menuId);
        });
    }


    private function getFooterMenuTwo()
    {
        return $this->getFooterMenu(setting('storefront_footer_menu_two'));
    }


    private function getFooterTags()
    {
        $tagIds = setting('storefront_footer_tags', []);

        return Cache::tags(['tags', 'settings'])
            ->rememberForever(
                md5('storefront_footer_tags:' . serialize($tagIds) . ':' . locale()),
                $this->footerTagsCallback($tagIds)
            );
    }


    private function getCopyrightText()
    {
        $text = (string) (setting('storefront_copyright_text') ?? '');

        return strtr($text, [
            '{{ store_url }}' => route('home'),
            '{{ store_name }}' => (string) (setting('store_name') ?? ''),
            '{{ year }}' => date('Y'),
        ]);
    }


    private function getAcceptedPaymentMethodsImage()
    {
        return $this->getMedia(setting('storefront_accepted_payment_methods_image'));
    }


    private function getSchemaMarkup()
    {
        $homeUrl = route('home');

        $webSite = Schema::webSite()
            ->name(setting('store_name') ?: config('app.name'))
            ->url($homeUrl)
            ->potentialAction($this->searchActionSchema());

        $address = new StoreAddress();

        $org = Schema::store()
            ->name('Kayalar Manifatura')
            ->url($homeUrl)
            ->email('kayalarmanifatura@gmail.com');

        try {
            $street = '873 Sokak No:21';
            $city = 'Konak';
            $region = 'İzmir';
            $country = (string) (setting('store_country') ?: 'TR');

            $postal = $address->getZip();

            $postalAddress = Schema::postalAddress()
                ->streetAddress($street)
                ->addressLocality($city)
                ->addressRegion($region)
                ->addressCountry($country);

            if ($postal) {
                $postalAddress->postalCode($postal);
            }

            $org->address($postalAddress);
        } catch (\Throwable $e) {
        }

        try {
            $phone = setting('store_phone');
            if ($phone) {
                $org->telephone($phone);
            }
        } catch (\Throwable $e) {
        }

        try {
            $logoPath = $this->getHeaderLogo();
            if ($logoPath) {
                $logoUrl = url($logoPath);
                $org->logo($logoUrl);

                // Local Business için image alanını da doldur (logo ile aynı görsel)
                $org->image($logoUrl);
            }
        } catch (\Throwable $e) {
        }

        // Çalışma saatleri: Pazartesi-Cumartesi 08:00-18:00, Pazar 11:00-17:00
        try {
            $weekDays = [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
            ];

            $monSat = Schema::openingHoursSpecification()
                ->dayOfWeek($weekDays)
                ->opens('08:00')
                ->closes('18:00');

            $sun = Schema::openingHoursSpecification()
                ->dayOfWeek('Sunday')
                ->opens('11:00')
                ->closes('17:00');

            $org->openingHoursSpecification([$monSat, $sun]);
        } catch (\Throwable $e) {
        }

        // Fiyat aralığı (isteğe bağlı) - orta seviye fiyatlandırma için sembolik ifade
        try {
            $org->priceRange('₺₺');
        } catch (\Throwable $e) {
        }

        // Sosyal medya bağlantıları (sameAs)
        try {
            $org->sameAs([
                'https://www.instagram.com/kayalarmanifatura',
                'https://www.facebook.com/kayalarmanifatura',
            ]);
        } catch (\Throwable $e) {
        }

        // Bu Spatie sürümünde Schema::graph() yok; WebSite içine Organization'ı publisher olarak ekleyelim.
        $webSite->publisher($org);

        return $webSite;
    }


    private function searchActionSchema()
    {
        return Schema::searchAction()
            ->target(route('products.index') . '?query={search_term_string}')
            ->setProperty('query-input', 'required name=search_term_string');
    }

    private function getBrands()
    {
        return Cache::rememberForever('storefront_brands', function () {
            return Brand::withCount('products')
                ->whereHas('products') // Only brands with products
                ->having('products_count', '>', 0)
                ->get();
        });
    }
}
