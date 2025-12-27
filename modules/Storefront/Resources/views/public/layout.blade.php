<!DOCTYPE html>
<html lang="{{ locale() }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <meta name="theme-color" content="#000000">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">

        @stack('lcp_preload')

        <title>
            @hasSection('title')
                @yield('title')
            @else
                @if (setting('store_tagline'))
                    {{ setting('store_tagline') }} -
                @endif

                {{ setting('store_name') }}
            @endif
        </title>

        <script>
            window.FleetcartSEO = {
                baseTitle: document.title,
            };
        </script>

        @stack('meta')
        @yield('canonical')
        @PWA

        <link rel="shortcut icon" href="{{ $favicon }}" type="image/x-icon">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="{{ font_url(setting('storefront_display_font', 'Poppins')) }}" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="{{ font_url(setting('storefront_display_font', 'Poppins')) }}"></noscript>

        @include('storefront::public.partials.variables')

        <style>
            .header{min-height:60px}
            .product-card{min-height:260px}
        </style>

        @vite([
            'modules/Storefront/Resources/assets/public/sass/vendors/_bootstrap.scss',
            'modules/Storefront/Resources/assets/public/sass/vendors/_line-awesome.scss',
            'modules/Storefront/Resources/assets/public/sass/vendors/_swiper.scss',
            'modules/Storefront/Resources/assets/public/sass/vendors/_toastify.scss',
            'modules/Storefront/Resources/assets/public/sass/app.scss',
            'modules/Storefront/Resources/assets/public/js/app.js',
            'modules/Storefront/Resources/assets/public/js/main.js'
        ])

        @stack('styles')

        {!! setting('custom_header_assets') !!}

        <script>
            window.FleetCart = {
                baseUrl: '{{ localized_url(locale(), url('/')) }}',
                rtl: {{ is_rtl() ? 'true' : 'false' }},
                storeName: '{{ setting('store_name') }}',
                storeLogo: '{{ $logo }}',
                currency: '{{ currency() }}',
                locale: '{{ locale() }}',
                supportedLocales: @json(supported_locales()),
                loggedIn: {{ auth()->check() ? 'true' : 'false' }},
                compareCount: {{ $compareCount }},
                cartQuantity: {{ $cartQuantity }},
                wishlistCount: {{ $wishlistCount }},
                csrfToken: '{{ csrf_token() }}',
                freeShippingEnabled: {{ setting('smart_shipping_enabled') === '1' && setting('smart_shipping_show_progress_bar') === '1' ? 'true' : 'false' }},
                freeShippingMinAmount: {{ (float) setting('smart_shipping_free_threshold', 0) }},
                smartShippingButton1Text: '{{ setting('smart_shipping_button_1_text', 'Yeni Gelenler') }}',
                smartShippingButton1Link: '{{ setting('smart_shipping_button_1_link', url('products?sort=latest')) }}',
                smartShippingButton2Text: '{{ setting('smart_shipping_button_2_text', 'Fırsat Ürünleri') }}',
                smartShippingButton2Link: '{{ setting('smart_shipping_button_2_link', url('products?sort=sales')) }}',
                data: {},
                langs: {
                    'storefront::storefront.something_went_wrong': '{{ trans('storefront::storefront.something_went_wrong') }}',
                    'storefront::layouts.more_results': '{{ trans('storefront::layouts.more_results') }}'
                },
            };
        </script>

        {!! $schemaMarkup->toScript() !!}

        @stack('globals')
    </head>

    <body
        dir="{{ is_rtl() ? 'rtl' : 'ltr' }}"
        class="page-template {{ is_rtl() ? 'rtl' : 'ltr' }}"
        data-theme-color="{{ $themeColor->toHexString() }}"
    >
        <div x-data="App" class="wrapper">
            @include('storefront::public.layouts.top_nav')
            @include('storefront::public.layouts.header_custom_text')
            @include('storefront::public.layouts.header')
            @include('storefront::public.layouts.navigation')
            @include('storefront::public.layouts.breadcrumb')

            <main role="main">
                @yield('content')
            </main>

            @include('storefront::public.home.sections.newsletter_subscription')
            @include('storefront::public.layouts.footer')

            <div
                class="overlay"
                :class="{ active: $store.layout.overlay }"
                @click="hideOverlay"
            >
            </div>

            @include('storefront::public.layouts.sidebar_menu')
            @include('storefront::public.layouts.localization')

            @if (!request()->routeIs('checkout.create'))
                @include('storefront::public.layouts.sidebar_cart')
            @endif

            @include('storefront::public.layouts.alert')
            @include('storefront::public.layouts.newsletter_popup')
            @includeIf('popup::public.layouts.popup')
            @include('storefront::public.layouts.cookie_bar')
            @include('storefront::public.layouts.scroll_to_top')
        </div>

        @stack('pre-scripts')
        @stack('scripts')

        {!! setting('custom_footer_assets') !!}

        @if (app()->environment('local') && app()->bound('debugbar'))
            {!! app('debugbar')->render() !!}
        @endif
    </body>
</html>
