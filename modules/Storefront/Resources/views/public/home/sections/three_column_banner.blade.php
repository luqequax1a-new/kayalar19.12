<section class="banner-wrap three-column-banner">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <a
                    href="{{ $threeColumnBanners['banner_1']->call_to_action_url }}"
                    class="banner"
                    target="{{ $threeColumnBanners['banner_1']->open_in_new_window ? '_blank' : '_self' }}"
                >
                    @php(
                        $banner1Avif = $threeColumnBanners['banner_1']->image->detail_avif_url
                            ?? $threeColumnBanners['banner_1']->image->grid_avif_url
                            ?? null
                    )
                    @php(
                        $banner1Webp = $threeColumnBanners['banner_1']->image->detail_webp_url
                            ?? $threeColumnBanners['banner_1']->image->grid_webp_url
                            ?? null
                    )
                    @php(
                        $banner1Jpeg = $threeColumnBanners['banner_1']->image->path
                            ?? $threeColumnBanners['banner_1']->image->detail_jpeg_url
                            ?? $threeColumnBanners['banner_1']->image->grid_jpeg_url
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

            <div class="col-md-6">
                <a
                    href="{{ $threeColumnBanners['banner_2']->call_to_action_url }}"
                    class="banner"
                    target="{{ $threeColumnBanners['banner_2']->open_in_new_window ? '_blank' : '_self' }}"
                >
                    @php(
                        $banner2Avif = $threeColumnBanners['banner_2']->image->detail_avif_url
                            ?? $threeColumnBanners['banner_2']->image->grid_avif_url
                            ?? null
                    )
                    @php(
                        $banner2Webp = $threeColumnBanners['banner_2']->image->detail_webp_url
                            ?? $threeColumnBanners['banner_2']->image->grid_webp_url
                            ?? null
                    )
                    @php(
                        $banner2Jpeg = $threeColumnBanners['banner_2']->image->path
                            ?? $threeColumnBanners['banner_2']->image->detail_jpeg_url
                            ?? $threeColumnBanners['banner_2']->image->grid_jpeg_url
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

            <div class="col-md-6">
                <a
                    href="{{ $threeColumnBanners['banner_3']->call_to_action_url }}"
                    class="banner"
                    target="{{ $threeColumnBanners['banner_3']->open_in_new_window ? '_blank' : '_self' }}"
                >
                    @php(
                        $banner3Avif = $threeColumnBanners['banner_3']->image->detail_avif_url
                            ?? $threeColumnBanners['banner_3']->image->grid_avif_url
                            ?? null
                    )
                    @php(
                        $banner3Webp = $threeColumnBanners['banner_3']->image->detail_webp_url
                            ?? $threeColumnBanners['banner_3']->image->grid_webp_url
                            ?? null
                    )
                    @php(
                        $banner3Jpeg = $threeColumnBanners['banner_3']->image->path
                            ?? $threeColumnBanners['banner_3']->image->detail_jpeg_url
                            ?? $threeColumnBanners['banner_3']->image->grid_jpeg_url
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