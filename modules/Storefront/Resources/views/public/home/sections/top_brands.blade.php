<section x-data="TopBrands" class="top-brands-wrap clearfix">
    <div class="container">
        @if (setting('storefront_top_brands_section_title'))
            <h3 class="section-title">{{ setting('storefront_top_brands_section_title') }}</h3>
        @endif

        <div x-ref="topBrands" class="top-brands swiper clearfix">
            <div class="top-brand-list swiper-wrapper">
                @foreach ($topBrands as $topBrand)
                    <a
                        href="{{ $topBrand['url'] }}"
                        class="swiper-slide top-brand-item d-inline-flex align-items-center justify-content-center overflow-hidden"
                    >
                        @php(
                            $logo = $topBrand['logo']
                        )
                        @php(
                            $logoAvif = $logo['thumb_avif_url']
                                ?? $logo['grid_avif_url']
                                ?? null
                        )
                        @php(
                            $logoWebp = $logo['thumb_webp_url']
                                ?? $logo['grid_webp_url']
                                ?? null
                        )
                        @php(
                            $logoJpeg = $logo['thumb_jpeg_url']
                                ?? $logo['grid_jpeg_url']
                                ?? $logo['path']
                        )

                        <picture>
                            @if ($logoAvif)
                                <source srcset="{{ $logoAvif }}" type="image/avif">
                            @endif

                            @if ($logoWebp)
                                <source srcset="{{ $logoWebp }}" type="image/webp">
                            @endif

                            <img src="{{ $logoJpeg }}" alt="Brand logo" loading="lazy" />
                        </picture>
                    </a>
                @endforeach
            </div>

            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
</section> 