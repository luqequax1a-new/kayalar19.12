@extends('storefront::public.layout')

@section('title')
    @if (request()->has('query'))
        {{ trans('storefront::products.search_results_for') }}: "{{ request('query') }}"
    @else
        {{ isset($categoryMetaTitle) && $categoryMetaTitle ? $categoryMetaTitle : (isset($categoryName) ? $categoryName : trans('storefront::products.shop')) }}
    @endif
@endsection

@push('meta')
    @php
        $listBaseDescription = setting('store_tagline') ?: setting('store_name');

        $hasQuery = request()->filled('query');
        $hasCategory = request()->filled('category');
        $hasBrand = request()->filled('brand');
        $hasTag = request()->filled('tag');
        $hasAttribute = ! empty(request('attribute', []));
        $hasPrice = request()->filled('price') || request()->filled('minPrice') || request()->filled('maxPrice');
        $hasSort = request()->filled('sort');
        $hasViewMode = request()->filled('viewMode');
        $hasPerPage = (int) request('perPage', 20) !== 20;
        $isPaginated = (int) request('page', 1) > 1;

        $hasFacets = $hasAttribute || $hasPrice || $hasSort || $hasViewMode || $hasPerPage || $isPaginated;
    @endphp

    @if (isset($categoryName))
        @php
            $listTitle = $categoryMetaTitle ?: $categoryName;

            $listDescription = $categoryMetaDescription ?: $listBaseDescription;

            $listLogo = ($categoryBanner ?? $brandBanner ?? null)
                ?: setting('store_logo')
                ?: asset('build/assets/image-placeholder.png');
        @endphp

        <meta name="title" content="{{ $listTitle }}">
        <meta name="description" content="{{ $listDescription }}">

        @if ($hasFacets)
            <meta name="robots" content="noindex,follow">
        @endif

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $listTitle }}">
        <meta name="twitter:description" content="{{ $listDescription }}">

        <meta property="og:title" content="{{ $listTitle }}">
        <meta property="og:description" content="{{ $listDescription }}">
        <meta property="og:url" content="{{ route('categories.products.index', ['category' => request('category')]) }}">
        <meta property="og:image" content="{{ $listLogo }}">
        <meta property="og:locale" content="{{ locale() }}">
        @foreach (supported_locale_keys() as $code)
            <meta property="og:locale:alternate" content="{{ $code }}">
        @endforeach

        <meta name="twitter:image" content="{{ $listLogo }}">

        @php
            $faqEntities = [];

            if (isset($category) && is_array($category->faq_items)) {
                foreach ($category->faq_items as $faq) {
                    $q = isset($faq['question']) ? trim(strip_tags($faq['question'])) : '';
                    $a = isset($faq['answer']) ? trim(strip_tags($faq['answer'])) : '';

                    if ($q === '' || $a === '') {
                        continue;
                    }

                    $faqEntities[] = [
                        '@type' => 'Question',
                        'name' => $q,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $a,
                        ],
                    ];
                }

                // İstersen burada ilk 5 kayıtla sınırlayabilirsin
                // $faqEntities = array_slice($faqEntities, 0, 5);
            }

            if (! empty($faqEntities)) {
                echo '<script type="application/ld+json">' . PHP_EOL;
                echo json_encode([
                    '@context'   => 'https://schema.org',
                    '@type'      => 'FAQPage',
                    'mainEntity' => $faqEntities,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                echo PHP_EOL . '</script>' . PHP_EOL;
            }
        @endphp
    @endif

    @if (request()->has('query'))
        @php
            $searchQuery = request('query');

            $searchTitle = trans('storefront::products.search_results_for') . ': "' . $searchQuery . '"';

            $searchDescription = ($listBaseDescription ?: setting('store_name'))
                . ' '
                . trans('storefront::products.search_results_for')
                . ' "'
                . $searchQuery
                . '"';

            $searchLogo = setting('store_logo') ?: asset('build/assets/image-placeholder.png');
        @endphp

        <meta name="description" content="{{ $searchDescription }}">
        <meta name="robots" content="noindex,follow">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $searchTitle }}">
        <meta name="twitter:description" content="{{ $searchDescription }}">
        <meta name="twitter:image" content="{{ $searchLogo }}">

        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $searchTitle }}">
        <meta property="og:description" content="{{ $searchDescription }}">
        <meta property="og:url" content="{{ $hasCategory ? route('categories.products.index', ['category' => request('category')]) : url()->current() }}">
        <meta property="og:image" content="{{ $searchLogo }}">
        <meta property="og:locale" content="{{ locale() }}">
        @foreach (supported_locale_keys() as $code)
            <meta property="og:locale:alternate" content="{{ $code }}">
        @endforeach
    @endif
@endpush

@section('canonical')
    @php
        $baseUrl = url()->current();

        if ($hasCategory) {
            $baseUrl = route('categories.products.index', ['category' => request('category')]);
        } elseif ($hasBrand) {
            try {
                $baseUrl = route('brands.products.index', ['brand' => request('brand')]);
            } catch (\InvalidArgumentException $e) {
                $baseUrl = url()->current();
            }
        } elseif ($hasTag) {
            try {
                $baseUrl = route('tags.products.index', ['tag' => request('tag')]);
            } catch (\InvalidArgumentException $e) {
                $baseUrl = url()->current();
            }
        } elseif ($hasQuery) {
            $baseUrl = request()->url();
        }

        $canonicalUrl = \Illuminate\Support\Str::before($baseUrl, '?');
    @endphp

    <link rel="canonical" href="{{ $canonicalUrl }}">
@endsection

@section('content')
    <section
        x-data="ProductIndex"
        class="product-search-wrap"
    >
        <div class="container">
            <div class="product-search">
                <div class="product-search-left">
                    <div class="product-filter-wrap" :class="{ active: $store.layout.isOpenSidebarFilter }">
                        <div class="product-filter-header d-lg-none">
                            <h4>
                                {{ trans('storefront::products.filters') }}
                            </h4>

                            <svg @click="$store.layout.closeSidebarFilter()" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M15.8338 4.16663L4.16705 15.8333M4.16705 4.16663L15.8338 15.8333" stroke="#0E1E3E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>

                        <div class="product-filter-content custom-scrollbar">
                            @if ($categories->isNotEmpty())
                                <div class="browse-categories-wrap">
                                    <h4 class="section-title">
                                        {{ trans('storefront::products.browse_categories') }}
                                    </h4>

                                    <h6 class="d-block d-lg-none">{{ trans('storefront::products.categories') }}</h6>

                                    @include('storefront::public.products.index.browse_categories')
                                </div>
                            @endif
                            
                            @include('storefront::public.products.index.filter')
                        </div>
                    </div>

                    @include('storefront::public.products.index.latest_products')
                </div>


                <div class="product-search-right">
                    <template x-if="brandBanner">
                        <div class="d-none d-lg-block categories-banner">
                            <img :src="brandBanner" alt="Brand banner">
                        </div>
                    </template>
                    
                    <template x-if="!brandBanner && categoryBanner">
                        <div class="d-none d-lg-block categories-banner">
                            <img :src="categoryBanner" alt="Category banner">
                        </div>
                    </template>

                    @include('storefront::public.products.index.search_result')
                </div>
            </div>
        </div>
    </section>
@endsection

@push('globals')
    <script>
        window.FleetCart = window.FleetCart || { data: {}, langs: {} };

        FleetCart.data['initialQuery'] = '{{ addslashes(request('query')) }}';
        FleetCart.data['initialBrandName'] = '{{ addslashes($brandName ?? '') }}';
        FleetCart.data['initialBrandBanner'] = '{{ addslashes($brandBanner ?? '') }}';
        FleetCart.data['initialBrandSlug'] = '{{ addslashes(request('brand')) }}';
        FleetCart.data['initialCategoryName'] = '{{ addslashes($categoryName ?? '') }}';
        FleetCart.data['initialCategoryBanner'] = '{{ addslashes($categoryBanner ?? '') }}';
        FleetCart.data['initialCategorySlug'] = '{{ addslashes(request('category')) }}';
        FleetCart.data['initialCategoryDescriptionHtml'] = @json(isset($category) ? ($category->description ?? '') : '');
        FleetCart.data['initialCategoryFaqItems'] = @json(isset($category) && is_array($category->faq_items) ? $category->faq_items : []);
        FleetCart.data['initialTagName'] = '{{ addslashes($tagName ?? '') }}';
        FleetCart.data['initialTagSlug'] = '{{ addslashes(request('tag')) }}';
        FleetCart.data['initialAttribute'] = @json((object) request('attribute', []));
        FleetCart.data['minPrice'] = {{ $minPrice }};
        FleetCart.data['maxPrice'] = {{ $maxPrice }};
        FleetCart.data['initialSort'] = '{{ addslashes(request('sort', 'latest')) }}';
        FleetCart.data['initialPage'] = {{ addslashes(request('page', 1)) }};
        FleetCart.data['initialPerPage'] = {{ addslashes(request('perPage', 20)) }};
        FleetCart.data['initialViewMode'] = '{{ addslashes(request('viewMode', 'grid')) }}';
        FleetCart.data['initialProducts'] = @json($initialProducts ?? null);
        FleetCart.data['initialAttributes'] = @json($initialAttributes ?? null);
        FleetCart.data['initialCategoryData'] = @json($initialCategoryData ?? null);
        FleetCart.langs['storefront::products.showing_results'] = '{{ trans("storefront::products.showing_results") }}';
    </script>

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/products/index/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/products/index/main.js',
    ])
@endpush
