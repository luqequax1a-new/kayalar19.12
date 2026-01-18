@extends('storefront::public.layout')

@section('title')
    @if (request()->has('query'))
        {{ trans('storefront::products.search_results_for') }}: "{{ request('query') }}"
    @elseif (isset($categoryMetaTitle) && $categoryMetaTitle)
        {{ $categoryMetaTitle }}
    @elseif (isset($categoryName))
        {{ $categoryName }}
    @elseif (setting('products_page_meta_title'))
        {{ setting('products_page_meta_title') }}
    @else
        {{ trans('storefront::products.shop') }}
    @endif
@endsection

@push('meta')
    @php
        $listBaseDescription = setting('store_tagline') ?: setting('store_name');
        
        // Base canonical: standardized clean URL
        $canonical = url()->current();
        if (request()->filled('query')) {
             $canonical = url()->full(); // Search results should use full URL
        } elseif (request()->filled('category')) {
             $canonical = url('/' . request('category'));
        } elseif (request()->filled('brand')) {
             $canonical = url('/' . request('brand'));
        }

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
        $canonical = \Illuminate\Support\Str::before($canonical, '?');
    @endphp

    @if (isset($categoryName))
        @php
            $listTitle = $categoryMetaTitle ?: $categoryName;

            $listDescription = $categoryMetaDescription;
            if (empty($listDescription) || mb_strlen($listDescription) < 15) {
                $listDescription = "En kaliteli " . $categoryName . " ürünlerini uygun fiyatlarla Kayalar Manifatura'da keşfedin. Güvenli alışveriş ve hızlı kargo seçenekleriyle hemen satın alın.";
            }

            $listLogo = ($categoryBanner ?? $brandBanner ?? null)
                ?: setting('store_logo')
                ?: asset('build/assets/image-placeholder.png');
        @endphp

        <meta name="description" content="{{ $listDescription }}">

        @if ($hasFacets)
            <meta name="robots" content="noindex,follow">
        @endif

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $listTitle }}">
        <meta name="twitter:description" content="{{ $listDescription }}">

        <meta property="og:title" content="{{ $listTitle }}">
        <meta property="og:description" content="{{ $listDescription }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $listLogo }}">
        <meta property="og:locale" content="{{ locale() }}">
        @foreach (supported_locale_keys() as $code)
            @if($code !== locale())
                <meta property="og:locale:alternate" content="{{ $code }}">
            @endif
        @endforeach

        <meta name="twitter:image" content="{{ $listLogo }}">

        {{-- Category Breadcrumb Schema --}}
        @if (isset($category) && $category && $category->id)
            <script type="application/ld+json">
            {
              "@context": "https://schema.org",
              "@type": "BreadcrumbList",
              "itemListElement": [
                {
                  "@type": "ListItem",
                  "position": 1,
                  "name": "{{ trans('storefront::layouts.home') }}",
                  "item": "{{ route('home') }}"
                }
                @php
                    $trail = [];
                    $curr = $category;
                    while($curr) {
                        $trail[] = $curr;
                        $curr = $curr->parent; // Assumes relationship 'parent' exists
                    }
                    $trail = array_reverse($trail);
                    $pos = 2;
                @endphp
                @foreach($trail as $t)
                ,{
                  "@type": "ListItem",
                  "position": {{ $pos++ }},
                  "name": "{{ addslashes($t->name) }}",
                  "item": "{{ $t->url() }}"
                }
                @endforeach
              ]
            }
            </script>
        @endif

    @elseif (isset($brandName))
        @php
            $listTitle = $brandName;
            $listDescription = '';
            
            if (isset($brand)) {
                 $listTitle = $brand->meta->meta_title ?: $brandName;
                 $listDescription = $brand->meta->meta_description;
            }

            if (empty($listDescription) || mb_strlen($listDescription) < 15) {
                $listDescription = $brandName . " ürünlerini uygun fiyatlarla Kayalar Manifatura'da keşfedin. Güvenli alışveriş ve hızlı kargo seçenekleriyle hemen satın alın.";
            }

            $listLogo = ($brandBanner ?? null)
                ?: setting('store_logo')
                ?: asset('build/assets/image-placeholder.png');
        @endphp

        <meta name="description" content="{{ $listDescription }}">

        @if ($hasFacets)
            <meta name="robots" content="noindex,follow">
        @endif

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $listTitle }}">
        <meta name="twitter:description" content="{{ $listDescription }}">

        <meta property="og:title" content="{{ $listTitle }}">
        <meta property="og:description" content="{{ $listDescription }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $listLogo }}">
        <meta property="og:locale" content="{{ locale() }}">
        @foreach (supported_locale_keys() as $code)
            @if($code !== locale())
                <meta property="og:locale:alternate" content="{{ $code }}">
            @endif
        @endforeach

        <meta name="twitter:image" content="{{ $listLogo }}">
        
        {{-- Brand Breadcrumb Schema --}}
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "BreadcrumbList",
          "itemListElement": [
            {
              "@type": "ListItem",
              "position": 1,
              "name": "{{ trans('storefront::layouts.home') }}",
              "item": "{{ route('home') }}"
            },
            {
              "@type": "ListItem",
              "position": 2,
              "name": "{{ addslashes($brandName) }}",
              "item": "{{ $canonical }}"
            }
          ]
        }
        </script>
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
            $searchLogo = logo_url() ?: asset('build/assets/image-placeholder.png');
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
        <meta property="og:url" content="{{ request()->filled('category') ? route('categories.products.index', ['category' => request('category')]) : url()->current() }}">
        <meta property="og:image" content="{{ $searchLogo }}">
        <meta property="og:locale" content="{{ locale() }}">
        @foreach (supported_locale_keys() as $code)
            <meta property="og:locale:alternate" content="{{ $code }}">
        @endforeach
    @endif

    @if (!request()->has('query') && !isset($categoryName) && !isset($brandName) && !isset($tagName))
        @php
            $productsPageTitle = setting('products_page_meta_title') ?: trans('storefront::products.shop');
            $productsPageDescription = setting('products_page_meta_description') ?: (setting('store_tagline') ?: setting('store_name'));
            $productsPageLogo = logo_url() ?: asset('build/assets/image-placeholder.png');
        @endphp

        <meta name="description" content="{{ $productsPageDescription }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $productsPageTitle }}">
        <meta name="twitter:description" content="{{ $productsPageDescription }}">
        <meta name="twitter:image" content="{{ $productsPageLogo }}">

        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $productsPageTitle }}">
        <meta property="og:description" content="{{ $productsPageDescription }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $productsPageLogo }}">
        <meta property="og:locale" content="{{ locale() }}">
        @foreach (supported_locale_keys() as $code)
            @if($code !== locale())
                <meta property="og:locale:alternate" content="{{ $code }}">
            @endif
        @endforeach
    @endif
@endpush

@push('lcp_preload')
    <style>
        @media (max-width: 576px) {
            .product-search-wrap .grid-view-products {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
                padding-left: 12px;
                padding-right: 12px;
            }

            .product-search-wrap .grid-view-products-item {
                min-width: 0;
            }

            .product-search-wrap .product-card,
            .product-search-wrap .product-image,
            .product-search-wrap .product-image-shell {
                width: 100%;
            }

            .product-search-wrap .product-card-middle a.product-name {
                padding-top: 4px;
                padding-bottom: 6px;
            }
        }
    </style>
@endpush

@section('canonical')
    <link rel="canonical" href="{{ $canonical }}">
@endsection

@section('content')
    {{-- SEO: Server Side Rendered FAQ & Description for Bot Crawlers --}}
    @php
        $validFaqs = collect($initialCategoryData['faq_items'] ?? [])->map(function($f) {
             return [
                 'q' => trim($f['question'] ?? $f['q'] ?? ''),
                 'a' => trim($f['answer'] ?? $f['a'] ?? ''),
             ];
        })->filter(function($f) {
            $q = $f['q'];
            $a = $f['a'];
            return mb_strlen($q) > 8 && mb_strlen($a) > 10 && !preg_match('/(denem|test|asdf)/i', $q);
        });
    @endphp

    @if($validFaqs->isNotEmpty())
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "mainEntity": [
            @foreach($validFaqs as $index => $faq)
            {
              "@type": "Question",
              "name": "{{ addslashes($faq['q']) }}",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "{{ addslashes(strip_tags($faq['a'])) }}"
              }
            }{{ $loop->last ? '' : ',' }}
            @endforeach
          ]
        }
        </script>
    @endif

    <div class="sr-only" aria-hidden="true" style="display: none !important;">
        @if(isset($initialCategoryData['description_html']))
            {!! $initialCategoryData['description_html'] !!}
        @endif
        
        @if(isset($validFaqs) && $validFaqs->isNotEmpty())
            @foreach($validFaqs as $faq)
                <h3>{{ $faq['q'] }}</h3>
                <div>{!! $faq['a'] !!}</div>
            @endforeach
        @endif
    </div>

    <section
        x-data="ProductIndex"
        @filter-sort-changed.window="changeSort($event.detail)"
        @filter-per-page-changed.window="changePerPage($event.detail)"
        class="product-search-wrap"
    >
        <div class="container">
            <div class="product-search">
                <div class="product-search-left">
                    <div class="product-filter-wrap" :class="{ active: $store.layout.isOpenSidebarFilter }">
                        <div class="product-filter-header d-lg-none">
                                <h4 class="ikas-title">
                                    Filtrele
                                </h4>

                                <div class="header-actions">
                                    <button @click="$store.layout.closeSidebarFilter()" class="btn-close-filter">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                        </div>

                        <div class="product-filter-content custom-scrollbar">
                            @include('storefront::public.products.index.filter')
                        </div>

                        <div class="filter-apply-footer">
                            <button 
                                type="button" 
                                class="btn btn-primary btn-apply-filters" 
                                @click="applyFilters"
                                :disabled="isSubmitting"
                            >
                                <span x-show="!isSubmitting">
                                    <span x-text="total > 0 ? total + ' ÜRÜNÜ GÖR' : 'ÜRÜNLERİ GÖR'"></span>
                                    <template x-if="isSubmittingCount">
                                        <span class="ms-1 small opacity-50">...</span>
                                    </template>
                                </span>
                                <span x-show="isSubmitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span x-show="isSubmitting" x-text="'YÜKLENİYOR...'"></span>
                            </button>
                        </div>
                    </div>

                    @include('storefront::public.products.index.latest_products')
                </div>


                <div class="product-search-right">
                    <template x-if="brandBanner">
                        <div class="d-none d-lg-block categories-banner">
                            <img :src="brandBanner" :alt="brandName">
                        </div>
                    </template>
                    
                    <template x-if="!brandBanner && categoryBanner">
                        <div class="d-none d-lg-block categories-banner">
                            <img :src="categoryBanner" :alt="categoryName">
                        </div>
                    </template>

                    @include('storefront::public.products.index.search_result')
                </div>
            </div>
        </div>
    </section>
@endsection

@push('globals')
    @php
        $initialProductsCollection = null;
        try {
            if (isset($initialProducts) && $initialProducts) {
                $initialProductsCollection = method_exists($initialProducts, 'getCollection')
                    ? $initialProducts->getCollection()
                    : $initialProducts;
            }
        } catch (\Throwable $e) {
            $initialProductsCollection = null;
        }

        $initialTotal = 0;
        $initialFrom = null;
        $initialTo = null;
        try {
            if (isset($initialProducts) && $initialProducts && method_exists($initialProducts, 'total')) {
                $initialTotal = (int) $initialProducts->total();
                $initialFrom = $initialProducts->firstItem();
                $initialTo = $initialProducts->lastItem();
            }
        } catch (\Throwable $e) {
            $initialTotal = 0;
            $initialFrom = null;
            $initialTo = null;
        }

        $initialProductsHtml = '';
        $initialPaginationHtml = '';
        $initialShowingText = '';

        try {
            if ($initialProductsCollection && $initialProductsCollection->count()) {
                $initialProductsHtml = view(
                    request('viewMode', 'grid') === 'list'
                        ? 'storefront::public.products.index.list_view_products'
                        : 'storefront::public.partials.products.grid',
                    [
                        'products' => $initialProductsCollection,
                        'productsPaginator' => $initialProducts ?? null,
                    ]
                )->render();
            }
        } catch (\Throwable $e) {
            $initialProductsHtml = '';
        }

        try {
            if (isset($initialProducts) && $initialProducts && method_exists($initialProducts, 'total')) {
                if ((int) $initialProducts->total() > (int) request('perPage', 20)) {
                    $initialPaginationHtml = view('storefront::public.partials.pagination')->render();
                }
            }
        } catch (\Throwable $e) {
            $initialPaginationHtml = '';
        }

        try {
            if ($initialTotal > 0) {
                $initialShowingText = trans('storefront::products.showing_results', [
                    'from' => $initialFrom,
                    'to' => $initialTo,
                    'total' => $initialTotal,
                ]);
            }
        } catch (\Throwable $e) {
            $initialShowingText = '';
        }
    @endphp

    <script>
        window.FleetCart = window.FleetCart || { data: {}, langs: {} };

        FleetCart.data['productsPageSlug'] = '{{ setting('products_page_slug', 'products') }}';
        FleetCart.data['initialQuery'] = '{{ addslashes((string) request('query', '')) }}';
        FleetCart.data['initialBrandName'] = '{{ addslashes((string) ($brandName ?? '')) }}';
        FleetCart.data['initialBrandBanner'] = '{{ addslashes((string) ($brandBanner ?? '')) }}';
        FleetCart.data['initialBrandSlug'] = '{{ addslashes((string) request('brand', '')) }}';
        FleetCart.data['initialCategoryName'] = '{{ addslashes((string) ($initialCategoryData['name'] ?? '')) }}';
        FleetCart.data['initialCategoryBanner'] = '{{ addslashes((string) ($categoryBanner ?? '')) }}';
        FleetCart.data['initialCategorySlug'] = '{{ addslashes((string) ($initialCategoryData['slug'] ?? '')) }}';
        FleetCart.data['initialCategoryDescriptionHtml'] = @json($initialCategoryData['description_html'] ?? '');
        FleetCart.data['initialCategoryFaqItems'] = @json($initialCategoryData['faq_items'] ?? []);
        FleetCart.data['initialTagName'] = '{{ addslashes((string) ($tagName ?? '')) }}';
        FleetCart.data['initialTagSlug'] = '{{ addslashes((string) request('tag', '')) }}';
        FleetCart.data['initialAttribute'] = @json((object) request('attribute', []));
        FleetCart.data['minPrice'] = {{ $minPrice }};
        FleetCart.data['maxPrice'] = {{ $maxPrice }};
        FleetCart.data['initialSort'] = '{{ addslashes((string) request('sort', '')) }}';
        FleetCart.data['initialPage'] = {{ (int) request('page', 1) }};
        FleetCart.data['initialPerPage'] = {{ (int) request('perPage', 20) }};
        FleetCart.data['initialProducts'] = @json($initialProducts ?? null);
        FleetCart.data['initialAttributes'] = @json($initialAttributes ?? null);
        FleetCart.data['initialBrands'] = @json($initialBrands ?? null);
        FleetCart.data['initialCategoryData'] = @json($initialCategoryData ?? null);
        FleetCart.data['initialTotal'] = {{ (int) ($initialTotal ?? 0) }};
        FleetCart.data['initialShowingText'] = @json($initialShowingText ?? '');
        FleetCart.data['initialProductsHtml'] = @json($initialProductsHtml ?? '');
        FleetCart.data['initialPaginationHtml'] = @json($initialPaginationHtml ?? '');
        FleetCart.langs['storefront::products.showing_results'] = '{{ trans("storefront::products.showing_results") }}';
    </script>

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/products/index/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/products/index/main.js',
    ])
@endpush
