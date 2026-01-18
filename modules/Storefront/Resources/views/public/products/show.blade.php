@extends('storefront::public.layout')

@section('title', $product->meta->meta_title ?: $product->name)

@php
    // Clean canonical URL without query parameters
    $canonical = $product->url();
    $canonical = \Illuminate\Support\Str::before($canonical, '?');
    
    $lcpImage = optional($product->variant)->base_image ?: $product->base_image;
    $lcpSizes = '(max-width: 576px) 92vw, (max-width: 992px) 50vw, 720px';

    $questionsCount = $product->questions()->where('is_approved', true)->count();
@endphp

@push('lcp_preload')
    {{-- LCP preload removed to avoid console warnings --}}
@endpush

@push('meta')
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="description" content="{{ $product->seo_meta_description }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $product->meta->meta_title ?: $product->name }}">
    <meta name="twitter:description" content="{{ $product->seo_meta_description }}">
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $product->meta->meta_title ?: $product->name }}">
    <meta property="og:description" content="{{ $product->seo_meta_description }}">
    @php
        $productOgImage = ($product->variant && optional($product->variant->base_image)->id)
            ? optional($product->variant->base_image)->path
            : ($product->base_image?->path ?? asset('build/assets/image-placeholder.png'));
    @endphp

    <meta property="og:image" content="{{ $productOgImage }}">
    <meta property="og:locale" content="{{ locale() }}">

    @foreach (supported_locale_keys() as $code)
        @if($code !== locale())
            <meta property="og:locale:alternate" content="{{ $code }}">
        @endif
    @endforeach

    <meta property="product:price:amount" content="{{ $product->variant?->selling_price->convertToCurrentCurrency()->amount() ?? $product->selling_price->convertToCurrentCurrency()->amount() }}">
    <meta property="product:price:currency" content="{{ currency() }}">
    <meta name="twitter:image" content="{{ $productOgImage }}">

    {{-- Professional Schema.org Implementation (Product + Breadcrumbs) --}}
    {!! $productSchemaMarkupScript !!}
    {!! $breadcrumbSchemaMarkupScript !!}
    @if (!empty($gallery))
        @foreach ($gallery as $galleryItem)
            @if (($galleryItem['type'] ?? '') === 'video' && !empty($galleryItem['src']))
                <script type="application/ld+json">
                {
                  "@context": "https://schema.org",
                  "@type": "VideoObject",
                  "name": "{{ addslashes($product->name) }}",
                  "description": "{{ addslashes(\Illuminate\Support\Str::limit(strip_tags($product->description), 200)) }}",
                  "thumbnailUrl": "{{ $galleryItem['thumb'] ?? ($product->base_image?->path ?? asset('build/assets/image-placeholder.png')) }}",
                  "uploadDate": "{{ optional($product->created_at)->toIso8601String() }}",
                  "contentUrl": "{{ $galleryItem['src'] }}",
                  "embedUrl": "{{ $galleryItem['src'] }}",
                  "publisher": {
                    "@type": "Organization",
                    "name": "{{ config('app.name') }}",
                    "logo": {
                      "@type": "ImageObject",
                      "url": "{{ asset('images/logo.png') }}"
                    }
                  }
                }
                </script>
            @endif
        @endforeach
    @endif
@endpush

@section('canonical')
    <link rel="canonical" href="{{ $canonical }}">
@endsection

@section('breadcrumb')
    @if (!$categoryBreadcrumb)
        <li><a href="{{ route('products.index') }}">{{ trans('storefront::products.shop') }}</a></li>
    @endif

    {!! $categoryBreadcrumb !!}

    <li class="active">{{ $product->name }}</li>
@endsection

@section('content')
    @php
        $defaultProductSectionsOrder = [
            'product_page_custom_text',
            'product_page_custom_html',
            'product_page_image_banner',
            'product_page_info_icons',
        ];

        $rawProductSectionsOrder = setting('storefront_product_page_sections_order');

        $savedProductSectionsOrder = is_array($rawProductSectionsOrder)
            ? $rawProductSectionsOrder
            : json_decode($rawProductSectionsOrder ?: '[]', true);

        $savedProductSectionsOrder = is_array($savedProductSectionsOrder)
            ? $savedProductSectionsOrder
            : [];

        $productSectionsOrder = collect($savedProductSectionsOrder)
            ->filter(fn ($name) => in_array($name, $defaultProductSectionsOrder, true))
            ->merge(collect($defaultProductSectionsOrder)->diff($savedProductSectionsOrder))
            ->values();

        // Optimized data for Alpine.js to reduce HTML weight
        $alpineProduct = $product->clean();
        $alpineVariant = $product->variant ? $product->variant->clean() : null;
    @endphp

    <section
        x-data="ProductShow({
            product: {{ json_encode($alpineProduct) }},
            variant: {{ json_encode($alpineVariant) }},
            reviewCount: {{ (int) ($review->count ?? 0) }},
            avgRating: {{ (float) ($review->avg_rating ?? 0) }},
            ratingBreakdown: {{ json_encode([
                5 => (int) ($review->count_5 ?? 0),
                4 => (int) ($review->count_4 ?? 0),
                3 => (int) ($review->count_3 ?? 0),
                2 => (int) ($review->count_2 ?? 0),
                1 => (int) ($review->count_1 ?? 0),
            ]) }},
            flashSalePrice: '{{ $flashSalePrice }}'
        })"
        class="product-details-wrap"
    >
        <div class="container">
            <div class="product-details-top">
                <div class="d-flex flex-column flex-lg-row flex-lg-nowrap ">
                    @if ($product->variant)
                        @include('storefront::public.products.show.variant_gallery')
                    @else
                        @include('storefront::public.products.show.gallery')
                    @endif

                    @include('storefront::public.products.show.details', ['item' => $product->variant ?? $product])

                    @if (setting('storefront_features_section_enabled'))
                        @include('storefront::public.products.show.right_sidebar')
                    @endif
                </div>
            </div>

            <div class="product-details-bottom flex-column-reverse flex-lg-row">
                @include('storefront::public.products.show.left_sidebar')

                <div class="product-details-bottom-inner">
                    <div class="product-details-tab clearfix">
                        <div class="product-details-tab-overflow">
                            <ul class="nav nav-tabs tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a href="#description" data-bs-toggle="tab" class="nav-link active">
                                        {{ trans('storefront::product.description') }}
                                    </a>
                                </li>

                                @if ($product->hasAnyAttribute())
                                    <li class="nav-item" role="presentation">
                                        <a href="#specification" data-bs-toggle="tab" class="nav-link">
                                            {{ trans('storefront::product.specification') }}
                                        </a>
                                    </li>
                                @endif

                                @if (setting('reviews_enabled'))
                                    <li class="nav-item" role="presentation">
                                        <a
                                            href="#reviews"
                                            data-bs-toggle="tab"
                                            class="nav-link"
                                            x-text="totalReviews > 0 ? trans('storefront::product.reviews', { count: totalReviews }) : 'Değerlendirme'"
                                        >
                                            @php
                                                $reviewCount = $review->count ?? ($product->reviews_count ?? 0);
                                            @endphp
                                            {{ $reviewCount > 0 ? trans('storefront::product.reviews', ['count' => $reviewCount]) : 'Değerlendirme' }}
                                        </a>
                                    </li>
                                @endif

                                <li class="nav-item" role="presentation">
                                    <a href="#questions" data-bs-toggle="tab" class="nav-link">
                                        {{ trans('question::questions.storefront.questions', ['count' => $questionsCount]) }}
                                    </a>
                                </li>

                                @if (setting('storefront_product_page_custom_tab_enabled') && setting('storefront_product_page_custom_tab_content'))
                                    <li class="nav-item" role="presentation">
                                        <a href="#custom_tab" data-bs-toggle="tab" class="nav-link">
                                            {{ setting('storefront_product_page_custom_tab_title') ?: trans('storefront::storefront.tabs.product_page_custom_tab') }}
                                        </a>
                                    </li>
                                @endif

                                @if (setting('storefront_product_page_custom_tab_2_enabled') && setting('storefront_product_page_custom_tab_2_content'))
                                    <li class="nav-item" role="presentation">
                                        <a href="#custom_tab_2" data-bs-toggle="tab" class="nav-link">
                                            {{ setting('storefront_product_page_custom_tab_2_title') ?: trans('storefront::storefront.tabs.product_page_custom_tab_2') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>

                            <hr>
                        </div>

                        <div class="tab-content">
                            @include('storefront::public.products.show.tab_description')
                            @include('storefront::public.products.show.tab_specification')
                            @include('storefront::public.products.show.tab_reviews')
                            @include('question::public.questions.tab_questions')

                            @if (setting('storefront_product_page_custom_tab_enabled') && setting('storefront_product_page_custom_tab_content'))
                                @include('storefront::public.products.show.tab_custom_tab')
                            @endif

                            @if (setting('storefront_product_page_custom_tab_2_enabled') && setting('storefront_product_page_custom_tab_2_content'))
                                @include('storefront::public.products.show.tab_custom_tab_2')
                            @endif
                        </div>
                    </div>

                    @if (setting('storefront_product_page_related_products_enabled'))
                        @include('storefront::public.products.show.related_products')
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('globals')
    <script>
        FleetCart.langs['storefront::product.left_in_stock'] = '{{ trans('storefront::product.left_in_stock') }}';
        FleetCart.langs['storefront::product.reviews'] = '{{ trans("storefront::product.reviews") }}';
        FleetCart.langs['storefront::product.review_submitted'] = '{{ trans("storefront::product.review_submitted") }}';
        FleetCart.langs['question::questions.storefront.questions'] = '{{ trans("question::questions.storefront.questions") }}';
        FleetCart.langs['question::questions.storefront.ask_a_question'] = '{{ trans("question::questions.storefront.ask_a_question") }}';
        FleetCart.langs['question::questions.storefront.write_your_question'] = '{{ trans("question::questions.storefront.write_your_question") }}';
        FleetCart.langs['question::questions.storefront.answered_at'] = '{{ trans("question::questions.storefront.answered_at") }}';
        FleetCart.langs['question::questions.storefront.be_the_first'] = '{{ trans("question::questions.storefront.be_the_first") }}';
        FleetCart.langs['question::questions.storefront.no_questions'] = '{{ trans("question::questions.storefront.no_questions") }}';
        FleetCart.langs['question::questions.storefront.submit'] = '{{ trans("question::questions.storefront.submit") }}';

        // Track Product View
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.FleetCartAnalytics !== 'undefined') {
                var productData = {
                    id: '{{ $product->id }}',
                    name: '{{ addslashes($product->name) }}',
                    price: {{ $product->variant ? $product->variant->selling_price->convertToCurrentCurrency()->amount() : $product->selling_price->convertToCurrentCurrency()->amount() }}
                };
                window.FleetCartAnalytics.trackProductView(productData);
            }
        });
    </script>

    <template data-related-product-card-template>
        <div class="grid-view-products-item">
            @include('storefront::public.partials.product_card', ['data' => '__PRODUCT__'])
        </div>
    </template>

    

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/products/show/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/products/show/main.js',
        'modules/Storefront/Resources/assets/public/js/vendors/flatpickr.js'
    ])

    @include('question::public.questions.script')
@endpush
