<div class="product-gallery position-relative align-self-start"> 
    <div class="product-gallery-wrapper" style="position: relative;">
    </div>
    <div class="product-gallery-preview-wrap position-relative overflow-hidden">
        @include('storefront::public.partials.products.tag_badges', [
            'product' => $product,
            'context' => 'detail',
        ])
        <div class="product-gallery-preview swiper">
            <div class="swiper-wrapper">
                @php($videoMedia = $product->productMedia->where('type', 'video')->where('is_active', true)->sortBy('position'))
                @php($lcpAssigned = false)

                @if ($product->media->isNotEmpty())
                    @foreach ($product->media as $media)
                        @php($isLcp = !$lcpAssigned)
                        @php($lcpAssigned = true)
                        @php(
                            $detailAvif = $media->detail_avif_url ?? $media->grid_avif_url ?? null
                        )
                        @php(
                            $detailWebp = $media->detail_webp_url ?? $media->grid_webp_url ?? null
                        )
                        @php($card2xAvif = $media->card_2x_avif_url ?? null)
                        @php($card2xWebp = $media->card_2x_webp_url ?? null)
                        @php($card2xJpeg = $media->card_2x_jpeg_url ?? null)
                        @php($card3xAvif = $media->card_3x_avif_url ?? null)
                        @php($card3xWebp = $media->card_3x_webp_url ?? null)
                        @php($card3xJpeg = $media->card_3x_jpeg_url ?? null)
                        @php(
                            $gridWebp = $media->grid_webp_url
                        )
                        @php(
                            $gridAvif = $media->grid_avif_url
                        )
                        @php(
                            $detailJpeg = $media->detail_jpeg_url
                                ?? $media->grid_jpeg_url
                                ?? $media->path
                                ?? asset('build/assets/image-placeholder.png')
                        )
                        @php(
                            $gridJpeg = $media->grid_jpeg_url
                        )
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                    <picture>
                                        @if ($detailAvif)
                                            <source
                                                srcset="{{ trim(collect([
                                                    ($gridAvif ? $gridAvif.' 400w' : null),
                                                    ($card2xAvif ? $card2xAvif.' 520w' : null),
                                                    ($card3xAvif ? $card3xAvif.' 780w' : null),
                                                    ($isLcp && $detailAvif ? $detailAvif.' 1000w' : null),
                                                ])->filter()->unique()->values()->implode(', ')) }}"
                                                sizes="(max-width: 576px) 92vw, (max-width: 992px) 50vw, 650px"
                                                type="image/avif"
                                            >
                                        @endif

                                        @if ($detailWebp)
                                            <source
                                                srcset="{{ trim(collect([
                                                    ($gridWebp ? $gridWebp.' 400w' : null),
                                                    ($card2xWebp ? $card2xWebp.' 520w' : null),
                                                    ($card3xWebp ? $card3xWebp.' 780w' : null),
                                                ])->filter()->unique()->values()->implode(', ')) }}"
                                                sizes="(max-width: 576px) 92vw, (max-width: 992px) 50vw, 650px"
                                                type="image/webp"
                                            >
                                        @endif

                                        <img
                                            src="{{ $gridJpeg ?: ($card2xJpeg ?: ($card3xJpeg ?: $detailJpeg)) }}"
                                            srcset="{{ trim(collect([
                                                ($gridJpeg ? $gridJpeg.' 400w' : null),
                                                ($card2xJpeg ? $card2xJpeg.' 520w' : null),
                                                ($card3xJpeg ? $card3xJpeg.' 780w' : null),
                                            ])->filter()->unique()->values()->implode(', ')) }}"
                                            sizes="(max-width: 576px) 92vw, (max-width: 992px) 50vw, 650px"
                                            data-zoom="{{ $detailJpeg }}"
                                            alt="{{ $product->name }}"
                                            width="1100"
                                            height="1100"
                                            loading="{{ $isLcp ? 'eager' : 'lazy' }}"
                                            fetchpriority="{{ $isLcp ? 'high' : 'auto' }}"
                                            decoding="async"
                                        >
                                    </picture>
                                </div>

                                <a href="{{ $detailJpeg }}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox" aria-label="View image">
                                    <i class="las la-search-plus"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($videoMedia as $video)
                        @php(
                            $poster = $video->poster
                                ?? $product->base_image->path
                                ?? optional($product->images->first())->path
                                ?? asset('build/assets/image-placeholder.png')
                        )
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item gallery-preview-item--video" data-media-type="video" data-playing="false">
                                    <video
                                        class="product-main-media product-main-media--video"
                                        controls
                                        controlslist="nofullscreen"
                                        playsinline
                                        preload="metadata"
                                        poster="{{ $poster }}"
                                        style="width: 100%; height: 100%; object-fit: cover;"
                                    >
                                        <source src="{{ $video->path }}" type="video/mp4">
                                    </video>
                                    <span class="fc-video-play-icon">▶</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="swiper-slide">
                        <div class="gallery-preview-slide">
                            <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                <img src="{{ asset('build/assets/image-placeholder.png') }}" data-zoom="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}" class="image-placeholder">
                            </div>

                            <a href="{{ asset('build/assets/image-placeholder.png') }}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox" aria-label="View image">
                                <i class="las la-search-plus"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</div>
 

