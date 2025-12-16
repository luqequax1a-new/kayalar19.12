<div class="home-banner-wrap">
    @php(
        $b1Avif = $sliderBanners['banner_1']->image->detail_avif_url
            ?? null
    )
    @php(
        $b1Webp = $sliderBanners['banner_1']->image->detail_webp_url
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
            @if ($b1Avif)
                <source srcset="{{ $b1Avif }}" type="image/avif">
            @endif

            @if ($b1Webp)
                <source srcset="{{ $b1Webp }}" type="image/webp">
            @endif

            <img
                src="{{ $b1Jpeg }}"
                alt="Banner"
                loading="lazy"
                decoding="async"
            >
        </picture>
    </a>

    @php(
        $b2Avif = $sliderBanners['banner_2']->image->detail_avif_url
            ?? null
    )
    @php(
        $b2Webp = $sliderBanners['banner_2']->image->detail_webp_url
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
            @if ($b2Avif)
                <source srcset="{{ $b2Avif }}" type="image/avif">
            @endif

            @if ($b2Webp)
                <source srcset="{{ $b2Webp }}" type="image/webp">
            @endif

            <img
                src="{{ $b2Jpeg }}"
                alt="Banner"
                loading="lazy"
                decoding="async"
            >
        </picture>
    </a>
</div>
