@extends('storefront::public.layout')

@section('title', setting('store_tagline'))

@section('canonical')
    <link rel="canonical" href="{{ \Illuminate\Support\Str::before(route('home'), '?') }}">
@endsection

@section('content')
    @php(
        $defaultHomeSectionsOrder = [
            // home_page_sections tab names (admin-side ordering)
            'slider_banners',
            'marquee',
            'featured_categories',
            'info_icons',
            'koleysiyon_grid',
            'category_grid_banners',
            'content_banner',
            'content_banner_2',
            'three_column_full_width_banners',
            'product_tabs_one',
            'top_brands',
            'flash_sale_and_vertical_products',
            'two_column_banners',
            'product_grid',
            'carousel_products',
            'carousel_products_2',
            'three_column_banners',
            'three_column_banners_2',
            'product_tabs_two',
            'one_column_banner',
            'info_icons_2',
            'faq',
            'blogs',
            'html_blog',
        ]
    )

    @php(
        $rawHomeSectionsOrder = setting('storefront_home_page_sections_order')
    )

    @php(
        $savedHomeSectionsOrder = is_array($rawHomeSectionsOrder)
            ? $rawHomeSectionsOrder
            : json_decode($rawHomeSectionsOrder ?: '[]', true)
    )

    @php(
        $savedHomeSectionsOrder = is_array($savedHomeSectionsOrder)
            ? $savedHomeSectionsOrder
            : []
    )

    @php(
        $homeSectionsOrder = collect($savedHomeSectionsOrder)
            ->filter(fn ($name) => in_array($name, $defaultHomeSectionsOrder, true))
            ->merge(collect($defaultHomeSectionsOrder)->diff($savedHomeSectionsOrder))
            ->values()
    )

    @foreach ($homeSectionsOrder as $section)
        @if ($section === 'slider_banners')
            @includeUnless(is_null($slider), 'storefront::public.home.sections.hero')
            @if (setting('storefront_features_section_enabled'))
                @include('storefront::public.home.sections.home_features')
            @endif
        @elseif ($section === 'marquee')
            @if (setting('storefront_home_marquee_enabled'))
                @include('storefront::public.home.sections.marquee')
            @endif
        @elseif ($section === 'featured_categories')
            @if (setting('storefront_featured_categories_section_enabled'))
                @include('storefront::public.home.sections.featured_categories')
            @endif
        @elseif ($section === 'info_icons')
            @if (setting('storefront_info_icons_enabled'))
                @include('storefront::public.home.sections.info_icons')
            @endif
        @elseif ($section === 'koleysiyon_grid')
            @if (setting('storefront_koleysiyon_grid_enabled'))
                @include('storefront::public.home.sections.koleysiyon_grid')
            @endif
        @elseif ($section === 'category_grid_banners')
            @if (setting('storefront_category_grid_banners_enabled'))
                @include('storefront::public.home.sections.category_grid_banners')
            @endif
        @elseif ($section === 'content_banner')
            @if (setting('storefront_buldan_promo_enabled'))
                @include('storefront::public.home.sections.content_banner')
            @endif
        @elseif ($section === 'content_banner_2')
            @if (setting('storefront_content_banner_2_enabled'))
                @include('storefront::public.home.sections.content_banner_2')
            @endif
        @elseif ($section === 'three_column_full_width_banners')
            @if (setting('storefront_three_column_full_width_banners_enabled'))
                @include('storefront::public.home.sections.three_column_full_width_banner')
            @endif
        @elseif ($section === 'product_tabs_one')
            @if (setting('storefront_product_tabs_1_section_enabled'))
                @include('storefront::public.home.sections.product_tabs_one')
            @endif
        @elseif ($section === 'top_brands')
            @if (setting('storefront_top_brands_section_enabled') && $topBrands->isNotEmpty())
                @include('storefront::public.home.sections.top_brands')
            @endif
        @elseif ($section === 'flash_sale_and_vertical_products')
            @if (setting('storefront_flash_sale_and_vertical_products_section_enabled'))
                @include('storefront::public.home.sections.flash_sale', [
                    'flashSaleEnabled' => setting('storefront_active_flash_sale_campaign')
                ])
            @endif
        @elseif ($section === 'two_column_banners')
            @if (setting('storefront_two_column_banners_enabled'))
                @include('storefront::public.home.sections.two_column_banner')
            @endif
        @elseif ($section === 'product_grid')
            @if (setting('storefront_product_grid_section_enabled'))
                @include('storefront::public.home.sections.grid_products')
            @endif
        @elseif ($section === 'carousel_products')
            @if (setting('storefront_carousel_section_enabled'))
                @include('storefront::public.home.sections.carousel_products')
            @endif
        @elseif ($section === 'carousel_products_2')
            @if (setting('storefront_carousel_section_2_enabled'))
                @include('storefront::public.home.sections.carousel_products_2')
            @endif
        @elseif ($section === 'three_column_banners')
            @if (setting('storefront_three_column_banners_enabled'))
                @include('storefront::public.home.sections.three_column_banner')
            @endif
        @elseif ($section === 'three_column_banners_2')
            @if (setting('storefront_three_column_banners_2_enabled'))
                @include('storefront::public.home.sections.three_column_banner_2')
            @endif
        @elseif ($section === 'product_tabs_two')
            @if (setting('storefront_product_tabs_2_section_enabled'))
                @include('storefront::public.home.sections.product_tabs_two')
            @endif
        @elseif ($section === 'one_column_banner')
            @if (setting('storefront_one_column_banner_enabled'))
                @include('storefront::public.home.sections.one_column_banner')
            @endif
        @elseif ($section === 'info_icons_2')
            @if (setting('storefront_info_icons_2_enabled'))
                @include('storefront::public.home.sections.info_icons_2')
            @endif
        @elseif ($section === 'faq')
            @if (setting('storefront_faq_enabled'))
                @include('storefront::public.home.sections.faq')
            @endif
        @elseif ($section === 'blogs')
            @if (setting('storefront_blogs_section_enabled'))
                @include('storefront::public.home.sections.blog')
            @endif
        @elseif ($section === 'html_blog')
            @if (setting('storefront_html_blog_enabled'))
                @include('storefront::public.home.sections.html_blog')
            @endif
        @endif
    @endforeach
@endsection

@push('meta')
    @php(
        $homeDescription = setting('store_description')
            ?: (setting('store_tagline') ?: setting('store_name'))
    )

    @php(
        $homeTitle = setting('store_tagline')
            ?: setting('store_name')
    )

    @php(
        $homeLogo = ($logo ?? null)
            ?: setting('store_logo')
    )

    <meta name="description" content="{{ $homeDescription }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $homeTitle }}">
    <meta property="og:description" content="{{ $homeDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if (! empty($homeLogo))
        <meta property="og:image" content="{{ $homeLogo }}">
    @endif
    <meta property="og:locale" content="{{ locale() }}">

    @foreach (supported_locale_keys() as $code)
        <meta property="og:locale:alternate" content="{{ $code }}">
    @endforeach

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $homeTitle }}">
    <meta name="twitter:description" content="{{ $homeDescription }}">
    @if (! empty($homeLogo))
        <meta name="twitter:image" content="{{ $homeLogo }}">
    @endif
@endpush

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/home/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/home/main.js',
    ])
@endpush
