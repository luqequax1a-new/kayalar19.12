<div class="home-banner-wrap">
    @php(
        $b1AvifSrcset = $sliderBanners['banner_1']->image->ikas_avif_srcset
            ?? null
    )
    @php(
        $b1WebpSrcset = $sliderBanners['banner_1']->image->ikas_webp_srcset
            ?? null
    )
    @php(
        $b1Jpeg = $sliderBanners['banner_1']->image->detail_jpeg_url
            ?? $sliderBanners['banner_1']->image->path
    )

    <a href="{{ $sliderBanners['banner_1']->call_to_action_url }}"
        class="banner"
        target="{{ $sliderBanners['banner_1']->open_in_new_window ? '_blank' : '_self' }}"
    >
        <picture>
            @if ($b1AvifSrcset)
                <source srcset="{{ $b1AvifSrcset }}" sizes="100vw" type="image/avif">
            @endif

            @if ($b1WebpSrcset)
                <source srcset="{{ $b1WebpSrcset }}" sizes="100vw" type="image/webp">
            @endif

            <img
                src="{{ $b1Jpeg }}"
                @if ($sliderBanners['banner_1']->image->ikas_jpeg_srcset)
                    srcset="{{ $sliderBanners['banner_1']->image->ikas_jpeg_srcset }}"
                @endif
                sizes="100vw"
                alt="Banner"
                loading="lazy"
                decoding="async"
            >
        </picture>
    </a>

    @php(
        $b2AvifSrcset = $sliderBanners['banner_2']->image->ikas_avif_srcset
            ?? null
    )
    @php(
        $b2WebpSrcset = $sliderBanners['banner_2']->image->ikas_webp_srcset
            ?? null
    )
    @php(
        $b2Jpeg = $sliderBanners['banner_2']->image->detail_jpeg_url
            ?? $sliderBanners['banner_2']->image->path
    )

    <a href="{{ $sliderBanners['banner_2']->call_to_action_url }}"
        class="banner"
        target="{{ $sliderBanners['banner_2']->open_in_new_window ? '_blank' : '_self' }}"
    >
        <picture>
            @if ($b2AvifSrcset)
                <source srcset="{{ $b2AvifSrcset }}" sizes="100vw" type="image/avif">
            @endif

            @if ($b2WebpSrcset)
                <source srcset="{{ $b2WebpSrcset }}" sizes="100vw" type="image/webp">
            @endif

            <img
                src="{{ $b2Jpeg }}"
                @if ($sliderBanners['banner_2']->image->ikas_jpeg_srcset)
                    srcset="{{ $sliderBanners['banner_2']->image->ikas_jpeg_srcset }}"
                @endif
                sizes="100vw"
                alt="Banner"
                loading="lazy"
                decoding="async"
            >
        </picture>
    </a>
</div>
