<section class="buldan-promo-section">
    <div class="container">
        <div class="buldan-promo-card">
            @if (!empty($contentBanner2['image']) && $contentBanner2['image']->exists)
                @php(
                    $bannerAvif = $contentBanner2['image']->detail_avif_url
                        ?? $contentBanner2['image']->grid_avif_url
                        ?? null
                )
                @php(
                    $bannerWebp = $contentBanner2['image']->detail_webp_url
                        ?? $contentBanner2['image']->grid_webp_url
                        ?? null
                )
                @php(
                    $bannerJpeg = $contentBanner2['image']->detail_jpeg_url
                        ?? $contentBanner2['image']->grid_jpeg_url
                        ?? $contentBanner2['image']->path
                )

                <div class="buldan-promo-image">
                    <picture>
                        @if ($bannerAvif)
                            <source srcset="{{ $bannerAvif }}" type="image/avif">
                        @endif

                        @if ($bannerWebp)
                            <source srcset="{{ $bannerWebp }}" type="image/webp">
                        @endif

                        <img src="{{ $bannerJpeg }}" alt="{{ $contentBanner2['title'] }}" loading="lazy" />
                    </picture>
                </div>
            @endif

            <div class="buldan-promo-content">
                @if (!empty($contentBanner2['title']))
                    <h2 class="buldan-promo-title">{!! $contentBanner2['title'] !!}</h2>
                @endif

                @if (!empty($contentBanner2['content']))
                    <p class="buldan-promo-text">{!! nl2br(e($contentBanner2['content'])) !!}</p>
                @endif

                @if (!empty($contentBanner2['button_text']) && !empty($contentBanner2['button_url']))
                    <a href="{{ $contentBanner2['button_url'] }}" class="buldan-promo-button">
                        {{ $contentBanner2['button_text'] }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
