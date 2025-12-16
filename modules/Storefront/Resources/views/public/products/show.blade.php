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

    @php($lcpImage = $product->variant?->base_image ?? $product->base_image)
    @php($lcpJpeg = $lcpImage?->detail_jpeg_url ?? $lcpImage?->grid_jpeg_url ?? $lcpImage?->path)
    @php($lcpWebp = $lcpImage?->detail_webp_url ?? $lcpImage?->grid_webp_url)
    @php($lcpAvif = $lcpImage?->detail_avif_url ?? $lcpImage?->grid_avif_url)
    @php($lcpGridJpeg = $lcpImage?->grid_jpeg_url)
    @php($lcpGridWebp = $lcpImage?->grid_webp_url)
    @php($lcpGridAvif = $lcpImage?->grid_avif_url)
    @php($lcpCard2xWebp = $lcpImage?->card_2x_webp_url)
    @php($lcpCard2xAvif = $lcpImage?->card_2x_avif_url)
    @php($lcpCard2xJpeg = $lcpImage?->card_2x_jpeg_url)
    @php($lcpCard3xWebp = $lcpImage?->card_3x_webp_url)
    @php($lcpCard3xAvif = $lcpImage?->card_3x_avif_url)
    @php($lcpCard3xJpeg = $lcpImage?->card_3x_jpeg_url)
    @php($lcpPreloadHref = $lcpGridAvif ?? $lcpCard3xAvif ?? $lcpCard2xAvif ?? $lcpAvif ?? null)
    @php($lcpSrcset = trim(collect([
        ($lcpGridAvif ? $lcpGridAvif.' 400w' : null),
        ($lcpCard2xAvif ? $lcpCard2xAvif.' 520w' : null),
        ($lcpCard3xAvif ? $lcpCard3xAvif.' 780w' : null),
        ($lcpAvif ? $lcpAvif.' 1000w' : null),
    ])->filter()->unique()->values()->implode(', ')))
    @php($lcpSizes = '(max-width: 576px) 92vw, (max-width: 992px) 50vw, 540px')

    @if (!empty($lcpPreloadHref))
        @php($lcpHost = parse_url($lcpPreloadHref, PHP_URL_HOST))
        @php($appHost = parse_url(url('/'), PHP_URL_HOST))
        @if ($lcpHost && $appHost && $lcpHost !== $appHost)
            <link rel="preconnect" href="{{ (parse_url($lcpPreloadHref, PHP_URL_SCHEME) ?: 'https') . '://' . $lcpHost }}" crossorigin>
        @endif

        <link
            rel="preload"
            as="image"
            href="{{ $lcpPreloadHref }}"
            @if (!empty($lcpSrcset))
                imagesrcset="{{ $lcpSrcset }}"
                imagesizes="{{ $lcpSizes }}"
            @endif
            fetchpriority="high"
        >
    @endif

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
                            </ul>

                            <hr>
                        </div>

                        <div class="tab-content">
                            @include('storefront::public.products.show.tab_description')
                            @include('storefront::public.products.show.tab_specification')
                            @include('storefront::public.products.show.tab_reviews')
                        </div>
                    </div>

                    @include('storefront::public.products.show.related_products')
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

    

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/products/show/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/products/show/main.js',
        'modules/Storefront/Resources/assets/public/js/vendors/flatpickr.js'
    ])
@endpush
