<section class="buldan-promo-section">
    <div class="container">
        <div class="buldan-promo-card">
            @if (!empty($buldanPromo['image']) && $buldanPromo['image']->exists)
                @php(
                    $bannerAvif = $buldanPromo['image']->detail_avif_url
                        ?? $buldanPromo['image']->grid_avif_url
                        ?? null
                )
                @php(
                    $bannerWebp = $buldanPromo['image']->detail_webp_url
                        ?? $buldanPromo['image']->grid_webp_url
                        ?? null
                )
                @php(
                    $bannerJpeg = $buldanPromo['image']->detail_jpeg_url
                        ?? $buldanPromo['image']->grid_jpeg_url
                        ?? $buldanPromo['image']->path
                )

                <div class="buldan-promo-image">
                    <picture>
                        @if ($bannerAvif)
                            <source srcset="{{ $bannerAvif }}" type="image/avif">
                        @endif

                        @if ($bannerWebp)
                            <source srcset="{{ $bannerWebp }}" type="image/webp">
                        @endif

                        <img src="{{ $bannerJpeg }}" alt="{{ $buldanPromo['title'] }}" loading="lazy" />
                    </picture>
                </div>
            @endif

            <div class="buldan-promo-content">
                @if (!empty($buldanPromo['title']))
                    <h2 class="buldan-promo-title">{!! $buldanPromo['title'] !!}</h2>
                @endif

                @if (!empty($buldanPromo['content']))
                    <p class="buldan-promo-text">{!! nl2br(e($buldanPromo['content'])) !!}</p>
                @endif

                @if (!empty($buldanPromo['button_text']) && !empty($buldanPromo['button_url']))
                    <a href="{{ $buldanPromo['button_url'] }}" class="buldan-promo-button">
                        {{ $buldanPromo['button_text'] }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
