<div class="product-gallery position-relative align-self-start"> 
    <div class="product-gallery-wrapper" style="position: relative;">
    </div>

    <div
        class="product-gallery-preview-wrap position-relative overflow-hidden"
        :class="{ 'visible-variation-image': hasAnyVariationImage }"
    >
        @include('storefront::public.partials.products.tag_badges', [
            'product' => $product,
            'context' => 'detail',
        ])

        <template x-if="hasAnyVariationImage">
            <img :src="variationImagePath" class="variation-image" :alt="productName" fetchpriority="high" decoding="async">
        </template>

        <div class="product-gallery-preview swiper">
            <div class="swiper-wrapper">
                @php($videoMedia = $product->productMedia->where('type', 'video')->where('is_active', true)->sortBy('position'))
                @php($hasAnyMedia = $product->variant->media->isNotEmpty() || $product->media->isNotEmpty())
                @php($lcpAssigned = false)

                @if (!$hasAnyMedia)
                    <div class="swiper-slide">
                        <div class="gallery-preview-slide">
                            <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                <img
                                    src="{{ asset('build/assets/image-placeholder.png') }}"
                                    data-zoom="{{ asset('build/assets/image-placeholder.png') }}"
                                    alt="{{ $product->name }}"
                                    class="image-placeholder"
                                >
                            </div>

                            <a href="{{ asset('build/assets/image-placeholder.png') }}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox" aria-label="View image">
                                <i class="las la-search-plus"></i>
                            </a>
                        </div>
                    </div>
                @else
                    @foreach ($product->variant->media as $media)
                        @php($isLcp = !$lcpAssigned)
                        @php($lcpAssigned = true)
                        @php(
                            $detailJpeg = $media->detail_jpeg_url
                                ?? $media->grid_jpeg_url
                                ?? $media->path
                                ?? asset('build/assets/image-placeholder.png')
                        )
                        @php($detailSizes = '(max-width: 576px) 92vw, (max-width: 992px) 50vw, 720px')
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                    <picture>
                                        @if ($media->ikas_avif_srcset)
                                            <source
                                                srcset="{{ $media->ikas_avif_srcset }}"
                                                sizes="{{ $detailSizes }}"
                                                type="image/avif"
                                            >
                                        @endif

                                        @if ($media->ikas_webp_srcset)
                                            <source
                                                srcset="{{ $media->ikas_webp_srcset }}"
                                                sizes="{{ $detailSizes }}"
                                                type="image/webp"
                                            >
                                        @endif

                                        <img
                                            src="{{ $detailJpeg }}"
                                            @if ($media->ikas_jpeg_srcset)
                                                srcset="{{ $media->ikas_jpeg_srcset }}"
                                            @endif
                                            sizes="{{ $detailSizes }}"
                                            data-zoom="{{ $detailJpeg }}"
                                            alt="{{ $product->name }}"
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

                    @foreach ($product->media as $media)
                        @php($isLcp = !$lcpAssigned)
                        @php($lcpAssigned = true)
                        @php(
                            $detailJpeg = $media->detail_jpeg_url
                                ?? $media->grid_jpeg_url
                                ?? $media->path
                                ?? asset('build/assets/image-placeholder.png')
                        )
                        @php($detailSizes = '(max-width: 576px) 92vw, (max-width: 992px) 50vw, 720px')
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                    <picture>
                                        @if ($media->ikas_avif_srcset)
                                            <source
                                                srcset="{{ $media->ikas_avif_srcset }}"
                                                sizes="{{ $detailSizes }}"
                                                type="image/avif"
                                            >
                                        @endif

                                        @if ($media->ikas_webp_srcset)
                                            <source
                                                srcset="{{ $media->ikas_webp_srcset }}"
                                                sizes="{{ $detailSizes }}"
                                                type="image/webp"
                                            >
                                        @endif

                                        <img
                                            src="{{ $detailJpeg }}"
                                            @if ($media->ikas_jpeg_srcset)
                                                srcset="{{ $media->ikas_jpeg_srcset }}"
                                            @endif
                                            sizes="{{ $detailSizes }}"
                                            data-zoom="{{ $detailJpeg }}"
                                            alt="{{ $product->name }}"
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
                                ?? optional($product->variant)->base_image?->path
                                ?? $product->base_image?->path
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
                @endif
            </div>

            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</div>
