<div class="product-gallery position-relative align-self-start"> 
    <div class="product-gallery-wrapper" style="position: relative;">
    </div>
    <div class="product-gallery-preview-wrap position-relative overflow-hidden">
        @php
            $displayName = $product->name;
        @endphp
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
                        @php($detailAvif = $media->detail_avif_url)
                        @php($detailWebp = $media->detail_webp_url)
                        @php(
                            $detailJpeg = $media->detail_jpeg_url
                                ?? $media->path
                                ?? asset('build/assets/image-placeholder.png')
                        )
                        @php($detailSizes = '(max-width: 576px) 92vw, (max-width: 992px) 50vw, 720px')
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                    <picture>
                                        @if ($detailAvif)
                                            <source srcset="{{ $detailAvif }}" type="image/avif">
                                        @endif
                                        @if ($detailWebp)
                                            <source srcset="{{ $detailWebp }}" type="image/webp">
                                        @endif
                                        <img
                                            src="{{$detailJpeg }}"
                                            data-zoom="{{ $detailJpeg }}"
                                            alt="{{ $displayName }}"
                                            width="1100"
                                            height="1100"
                                            loading="{{ $isLcp ? 'eager' : 'lazy' }}"
                                            fetchpriority="{{ $isLcp ? 'high' : 'low' }}"
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
                                        preload="none"
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
                                <img src="{{ asset('build/assets/image-placeholder.png') }}" data-zoom="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $displayName }}" class="image-placeholder">
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
 

