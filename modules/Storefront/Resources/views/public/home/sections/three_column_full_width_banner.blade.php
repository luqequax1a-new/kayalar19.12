<section
    class="banner-wrap three-column-full-width-banner"
    style="background-image: url({{ $threeColumnFullWidthBanners['background']->image->path }})"
>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <a
                    href="{{ $threeColumnFullWidthBanners['banner_1']->call_to_action_url }}"
                    class="banner"
                    target="{{ $threeColumnFullWidthBanners['banner_1']->open_in_new_window ? '_blank' : '_self' }}"
                >
                    @php(
                        $banner1Avif = $threeColumnFullWidthBanners['banner_1']->image->detail_avif_url
                            ?? $threeColumnFullWidthBanners['banner_1']->image->grid_avif_url
                            ?? null
                    )
                    @php(
                        $banner1Webp = $threeColumnFullWidthBanners['banner_1']->image->detail_webp_url
                            ?? $threeColumnFullWidthBanners['banner_1']->image->grid_webp_url
                            ?? null
                    )
                    @php(
                        $banner1Jpeg = $threeColumnFullWidthBanners['banner_1']->image->path
                            ?? $threeColumnFullWidthBanners['banner_1']->image->detail_jpeg_url
                            ?? $threeColumnFullWidthBanners['banner_1']->image->grid_jpeg_url
                    )

                    <picture>
                        @if ($banner1Avif)
                            <source srcset="{{ $banner1Avif }}" type="image/avif">
                        @endif

                        @if ($banner1Webp)
                            <source srcset="{{ $banner1Webp }}" type="image/webp">
                        @endif

                        <img src="{{ $banner1Jpeg }}" alt="Banner" loading="lazy" />
                    </picture>
                </a>
            </div>

            <div class="col-md-10">
                <a
                    href="{{ $threeColumnFullWidthBanners['banner_2']->call_to_action_url }}"
                    class="banner"
                    target="{{ $threeColumnFullWidthBanners['banner_2']->open_in_new_window ? '_blank' : '_self' }}"
                >
                    @php(
                        $banner2Avif = $threeColumnFullWidthBanners['banner_2']->image->detail_avif_url
                            ?? $threeColumnFullWidthBanners['banner_2']->image->grid_avif_url
                            ?? null
                    )
                    @php(
                        $banner2Webp = $threeColumnFullWidthBanners['banner_2']->image->detail_webp_url
                            ?? $threeColumnFullWidthBanners['banner_2']->image->grid_webp_url
                            ?? null
                    )
                    @php(
                        $banner2Jpeg = $threeColumnFullWidthBanners['banner_2']->image->path
                            ?? $threeColumnFullWidthBanners['banner_2']->image->detail_jpeg_url
                            ?? $threeColumnFullWidthBanners['banner_2']->image->grid_jpeg_url
                    )

                    <picture>
                        @if ($banner2Avif)
                            <source srcset="{{ $banner2Avif }}" type="image/avif">
                        @endif

                        @if ($banner2Webp)
                            <source srcset="{{ $banner2Webp }}" type="image/webp">
                        @endif

                        <img src="{{ $banner2Jpeg }}" alt="Banner" loading="lazy" />
                    </picture>
                </a>
            </div>

            <div class="col-md-4">
                <a
                    href="{{ $threeColumnFullWidthBanners['banner_3']->call_to_action_url }}"
                    class="banner"
                    target="{{ $threeColumnFullWidthBanners['banner_3']->open_in_new_window ? '_blank' : '_self' }}"
                >
                    @php(
                        $banner3Avif = $threeColumnFullWidthBanners['banner_3']->image->detail_avif_url
                            ?? $threeColumnFullWidthBanners['banner_3']->image->grid_avif_url
                            ?? null
                    )
                    @php(
                        $banner3Webp = $threeColumnFullWidthBanners['banner_3']->image->detail_webp_url
                            ?? $threeColumnFullWidthBanners['banner_3']->image->grid_webp_url
                            ?? null
                    )
                    @php(
                        $banner3Jpeg = $threeColumnFullWidthBanners['banner_3']->image->path
                            ?? $threeColumnFullWidthBanners['banner_3']->image->detail_jpeg_url
                            ?? $threeColumnFullWidthBanners['banner_3']->image->grid_jpeg_url
                    )

                    <picture>
                        @if ($banner3Avif)
                            <source srcset="{{ $banner3Avif }}" type="image/avif">
                        @endif

                        @if ($banner3Webp)
                            <source srcset="{{ $banner3Webp }}" type="image/webp">
                        @endif

                        <img src="{{ $banner3Jpeg }}" alt="Banner" loading="lazy" />
                    </picture>
                </a>
            </div>
        </div>
    </div>
</section>