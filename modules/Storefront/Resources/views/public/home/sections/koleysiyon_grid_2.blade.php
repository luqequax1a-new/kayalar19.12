@if (! empty($koleysiyonGrid2['items']))
    <section x-data="KoleysiyonGrid" class="koleysiyon-grid-section">
        <div class="container">
            <div class="koleysiyon-grid-inner">
                <div class="koleysiyon-grid-header">
                    @if (! empty($koleysiyonGrid2['title']))
                        <h2 class="koleysiyon-grid-title">{{ $koleysiyonGrid2['title'] }}</h2>
                    @endif

                    @if (! empty($koleysiyonGrid2['subtitle']))
                        <p class="koleysiyon-grid-subtitle">{{ $koleysiyonGrid2['subtitle'] }}</p>
                    @endif
                </div>

                <div
                    x-ref="koleysiyonGrid"
                    class="koleysiyon-grid swiper"
                >
                    <div class="koleysiyon-grid-list swiper-wrapper">
                        @foreach ($koleysiyonGrid2['items'] as $item)
                            <a
                                href="{{ $item['button_url'] ?: '#' }}"
                                class="swiper-slide koleysiyon-card"
                            >
                                @if ($item['image'] && $item['image']->path)
                                    <div class="koleysiyon-card-image">
                                        <img src="{{ $item['image']->path }}" alt="{{ $item['title'] }}" loading="lazy">
                                    </div>
                                @endif

                                <div class="koleysiyon-card-body">
                                    @if (! empty($item['title']))
                                        <h3 class="koleysiyon-card-title">{{ $item['title'] }}</h3>
                                    @endif

                                    @if (! empty($item['text']))
                                        <p class="koleysiyon-card-text">{{ $item['text'] }}</p>
                                    @endif

                                    @if (! empty($item['button_text']))
                                        <span class="koleysiyon-card-button">{{ $item['button_text'] }}</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="swiper-button-prev" x-ref="koleysiyonPrev"></div>
                    <div class="swiper-button-next" x-ref="koleysiyonNext"></div>
                    <div class="swiper-pagination" x-ref="koleysiyonPagination"></div>
                </div>
            </div>
        </div>
    </section>
@endif
