<section x-data="CarouselProducts()" class="grid-products-wrap carousel-products-wrap">
    <div class="container">
        <div class="grid-products-wrap-inner carousel-products-inner">
            @if (! empty($carouselProducts['title']))
                @php
                    $tag = setting('storefront_carousel_section_title_tag', 'h3');
                    $align = setting('storefront_carousel_section_title_align', 'left');
                    $weight = setting('storefront_carousel_section_title_weight');
                    $size = setting('storefront_carousel_section_title_size');
                    $color = setting('storefront_carousel_section_title_color');
                    $decoration = setting('storefront_carousel_section_title_decoration');
                    $style = setting('storefront_carousel_section_title_style');
                    $family = setting('storefront_carousel_section_title_family');
                    $marginTop = setting('storefront_carousel_section_title_margin_top');
                    $marginBottom = setting('storefront_carousel_section_title_margin_bottom');
                    $divider = (bool) setting('storefront_carousel_section_title_divider', false);

                    $styles = [];
                    if ($weight) $styles[] = "font-weight: {$weight}";
                    if ($size) $styles[] = "font-size: {$size}px";
                    if ($color) $styles[] = "color: {$color}";
                    if ($decoration) $styles[] = "text-decoration: {$decoration}";
                    if ($style) $styles[] = "font-style: {$style}";
                    if ($family) $styles[] = "font-family: {$family}";
                    if ($marginTop !== null && $marginTop !== '') $styles[] = "margin-top: {$marginTop}px";
                    if ($marginBottom !== null && $marginBottom !== '') $styles[] = "margin-bottom: {$marginBottom}px";

                    if (in_array($align, ['left', 'center', 'right'], true)) {
                        $styles[] = "text-align: {$align}";
                    }

                    $styleAttr = empty($styles) ? '' : ' style="' . implode('; ', $styles) . '"';

                    $headerStyles = [];
                    if (! $divider) {
                        $headerStyles[] = 'border-bottom: none';
                    }
                    $headerStyleAttr = empty($headerStyles) ? '' : ' style="' . implode('; ', $headerStyles) . '"';
                @endphp

                <div class="tab-products-header text-{{ $align }}"{!! $headerStyleAttr !!}>
                    {!! "<{$tag} class=\"section-title" . (! $divider ? ' section-title--no-divider' : '') . "\"{$styleAttr}>" . e($carouselProducts['title']) . "</{$tag}>" !!}

                    @if ($divider)
                        <hr>
                    @endif
                </div>

                @if (! $divider)
                    <style>
                        .carousel-products-wrap .section-title--no-divider::after {
                            display: none !important;
                        }
                    </style>
                @endif
            @endif

            <div class="tab-content">
                @php
                    $autoplay = setting('storefront_carousel_section_autoplay');
                    $autoplaySpeed = (int) (setting('storefront_carousel_section_autoplay_speed') ?: 3000);

                    $perRowMobile = (int) (setting('storefront_carousel_section_per_row_mobile') ?: 2);
                    $perRowTablet = (int) (setting('storefront_carousel_section_per_row_tablet') ?: 3);
                    $perRowDesktop = (int) (setting('storefront_carousel_section_per_row_desktop') ?: 5);

                    $showDots = (bool) setting('storefront_carousel_section_show_dots', true);
                    $showArrows = (bool) setting('storefront_carousel_section_show_arrows', true);
                @endphp

                <div
                    class="grid-products products-slider swiper carousel-products grid-view-products"
                    data-autoplay="{{ $autoplay ? 'true' : 'false' }}"
                    data-autoplay-speed="{{ $autoplaySpeed }}"
                    data-per-row-mobile="{{ $perRowMobile }}"
                    data-per-row-tablet="{{ $perRowTablet }}"
                    data-per-row-desktop="{{ $perRowDesktop }}"
                    data-show-dots="{{ $showDots ? 'true' : 'false' }}"
                    data-show-arrows="{{ $showArrows ? 'true' : 'false' }}"
                >
                    <div class="swiper-wrapper">
                        @foreach(($carouselProducts['products'] ?? []) as $product)
                            <div class="swiper-slide">
                                <div class="grid-view-products-item">
                                    @include('storefront::public.partials.product_card', ['data' => $product])
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($showDots)
                        <div class="swiper-pagination carousel-pagination"></div>
                    @endif

                    @if ($showArrows)
                        <div class="swiper-button-next" aria-label="Next">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div class="swiper-button-prev" aria-label="Previous">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
