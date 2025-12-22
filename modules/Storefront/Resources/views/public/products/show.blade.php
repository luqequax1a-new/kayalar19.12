@extends('storefront::public.layout')

@section('title', $product->meta->meta_title ?: $product->name)

@php($canonical = $product->variant?->url() ?? $product->url())
@php($canonical = \Illuminate\Support\Str::before($canonical, '?'))

@push('meta')
    <meta name="title" content="{{ $product->meta->meta_title ?: $product->name }}">
    <meta name="description" content="{{ $product->seo_meta_description }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $product->meta->meta_title ?: $product->name }}">
    <meta name="twitter:description" content="{{ $product->seo_meta_description }}">
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $product->meta->meta_title ?: $product->name }}">
    <meta property="og:description" content="{{ $product->seo_meta_description }}">
    @php(
        $productOgImage = ($product->variant && optional($product->variant->base_image)->id)
            ? optional($product->variant->base_image)->path
            : ($product->base_image?->path ?? asset('build/assets/image-placeholder.png'))
    )

    <meta property="og:image" content="{{ $productOgImage }}">
    <meta property="og:locale" content="{{ locale() }}">

    @foreach (supported_locale_keys() as $code)
        <meta property="og:locale:alternate" content="{{ $code }}">
    @endforeach

    <meta property="product:price:amount" content="{{ $product->variant?->selling_price->convertToCurrentCurrency()->amount() ?? $product->selling_price->convertToCurrentCurrency()->amount() }}">
    <meta property="product:price:currency" content="{{ currency() }}">
    <meta name="twitter:image" content="{{ $productOgImage }}">
    {{-- OG video meta removed; keeping only JSON-LD per request --}}
    @if (!empty($hasVideo) && $hasVideo && !empty($videoUrl))
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "VideoObject",
          "name": "{{ addslashes($product->name) }}",
          "description": "{{ addslashes(\Illuminate\Support\Str::limit(strip_tags($product->description), 200)) }}",
          "thumbnailUrl": "{{ $videoThumbnailUrl ?? ($product->base_image?->path ?? asset('build/assets/image-placeholder.png')) }}",
          "uploadDate": "{{ optional($product->created_at)->toIso8601String() }}",
          "contentUrl": "{{ $videoUrl }}",
          "embedUrl": "{{ $videoUrl }}",
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
    @php(
        $defaultProductSectionsOrder = [
            // product_page_sections tab names (admin-side ordering)
            'product_page_custom_text',
            'product_page_custom_html',
            'product_page_image_banner',
            'product_page_info_icons',
        ]
    )

    @php(
        $rawProductSectionsOrder = setting('storefront_product_page_sections_order')
    )

    @php(
        $savedProductSectionsOrder = is_array($rawProductSectionsOrder)
            ? $rawProductSectionsOrder
            : json_decode($rawProductSectionsOrder ?: '[]', true)
    )

    @php(
        $savedProductSectionsOrder = is_array($savedProductSectionsOrder)
            ? $savedProductSectionsOrder
            : []
    )

    @php(
        $productSectionsOrder = collect($savedProductSectionsOrder)
            ->filter(fn ($name) => in_array($name, $defaultProductSectionsOrder, true))
            ->merge(collect($defaultProductSectionsOrder)->diff($savedProductSectionsOrder))
            ->values()
    )

    <section
        x-data="ProductShow({
            product: {{ $product }},

            @if ($product->variant)
                variant: {{ $product->variant }},
            @endif

            reviewCount: {{ $review->count ?? 0 }},
            avgRating: {{ $review->avg_rating ?? 0 }},
            ratingBreakdown: {
                5: {{ $review->count_5 ?? 0 }},
                4: {{ $review->count_4 ?? 0 }},
                3: {{ $review->count_3 ?? 0 }},
                2: {{ $review->count_2 ?? 0 }},
                1: {{ $review->count_1 ?? 0 }},
            },
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
                                            x-text="trans('storefront::product.reviews', { count: totalReviews })"
                                        >
                                            {{ trans('storefront::product.reviews', ['count' => $review->count ?? ($product->reviews_count ?? 0) ]) }}
                                        </a>
                                    </li>
                                @endif

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
    @if (!empty($productSchemaMarkupScript))
        {!! $productSchemaMarkupScript !!}
    @elseif (!empty($productSchemaMarkup) && method_exists($productSchemaMarkup, 'toScript'))
        {!! $productSchemaMarkup->toScript() !!}
    @endif

    @if (!empty($breadcrumbSchemaMarkupScript))
        {!! $breadcrumbSchemaMarkupScript !!}
    @elseif (!empty($breadcrumbSchemaMarkup) && method_exists($breadcrumbSchemaMarkup, 'toScript'))
        {!! $breadcrumbSchemaMarkup->toScript() !!}
    @endif

    <script>
        FleetCart.langs['storefront::product.left_in_stock'] = '{{ trans('storefront::product.left_in_stock') }}';
        FleetCart.langs['storefront::product.reviews'] = '{{ trans("storefront::product.reviews") }}';
        FleetCart.langs['storefront::product.review_submitted'] = '{{ trans("storefront::product.review_submitted") }}';
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
@endpush
