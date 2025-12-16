<section class="buldan-promo-section">
    <div class="container">
        <div class="buldan-promo-card">
            @if (!empty($contentBanner['image']) && $contentBanner['image']->exists)
                @php(
                    $bannerAvif = $contentBanner['image']->detail_avif_url
                        ?? $contentBanner['image']->grid_avif_url
                        ?? null
                )
                @php(
                    $bannerWebp = $contentBanner['image']->detail_webp_url
                        ?? $contentBanner['image']->grid_webp_url
                        ?? null
                )
                @php(
                    $bannerJpeg = $contentBanner['image']->detail_jpeg_url
                        ?? $contentBanner['image']->grid_jpeg_url
                        ?? $contentBanner['image']->path
                )

                <div class="buldan-promo-image">
                    <picture>
                        @if ($bannerAvif)
                            <source srcset="{{ $bannerAvif }}" type="image/avif">
                        @endif

                        @if ($bannerWebp)
                            <source srcset="{{ $bannerWebp }}" type="image/webp">
                        @endif

                        <img src="{{ $bannerJpeg }}" alt="{{ $contentBanner['title'] }}" loading="lazy" />
                    </picture>
                </div>
            @endif

            <div class="buldan-promo-content">
                @if (!empty($contentBanner['title']))
                    <h2 class="buldan-promo-title">{!! $contentBanner['title'] !!}</h2>
                @endif

                @if (!empty($contentBanner['content']))
                    <p class="buldan-promo-text">{!! nl2br(e($contentBanner['content'])) !!}</p>
                @endif

                @if (!empty($contentBanner['button_text']) && !empty($contentBanner['button_url']))
                    <a href="{{ $contentBanner['button_url'] }}" class="buldan-promo-button">
                        {{ $contentBanner['button_text'] }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
