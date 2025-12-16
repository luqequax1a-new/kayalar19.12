<section class="info-icons-section">
    <div class="container">
        <div class="info-icons-wrapper">
            @foreach ($infoIcons['items'] as $item)
                <div class="info-icon-item">
                    @php($icon = $item['image'] ?? null)

                    @if ($icon && $icon->exists)
                        @php($iconAvif = $icon->detail_avif_url ?? $icon->grid_avif_url ?? null)
                        @php($iconWebp = $icon->detail_webp_url ?? $icon->grid_webp_url ?? null)
                        @php($iconDefault = $icon->detail_jpeg_url ?? $icon->grid_jpeg_url ?? $icon->path)

                        <div class="info-icon-image">
                            <picture>
                                @if ($iconAvif)
                                    <source srcset="{{ $iconAvif }}" type="image/avif">
                                @endif

                                @if ($iconWebp)
                                    <source srcset="{{ $iconWebp }}" type="image/webp">
                                @endif

                                <img src="{{ $iconDefault }}" alt="{{ $item['title'] }}" loading="lazy" />
                            </picture>
                        </div>
                    @endif

                    @if (!empty($item['title']))
                        <h3 class="info-icon-title">{{ $item['title'] }}</h3>
                    @endif

                    @if (!empty($item['text']))
                        <p class="info-icon-text">{{ $item['text'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
