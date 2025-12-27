<section x-data="Hero" class="home-section-wrap">
    <div class="container">
        <div class="row">
            <div class="home-section-inner">
                <div class="home-slider-wrap">
                    <div
                        class="home-slider overflow-hidden swiper"
                        data-speed="{{ $slider->speed }}"
                        data-autoplay="{{ $slider->autoplay ? 'true' : 'false' }}"
                        data-autoplay-speed="{{ $slider->autoplay_speed }}"
                        data-dots="{{ $slider->dots ? 'true' : 'false' }}"
                        data-arrows="{{ $slider->arrows ? 'true' : 'false' }}"
                    >
                        <div class="swiper-wrapper">
                            @foreach ($slider->slides as $slide)
                                @php
                                    $slideFile = $slide->file ?? null;
                                    $slideAvifSrcset = $slideFile?->ikas_avif_srcset ?: null;
                                    $slideWebpSrcset = $slideFile?->ikas_webp_srcset ?: null;
                                    $slideJpegSrcset = $slideFile?->ikas_jpeg_srcset ?: null;
                                    $slideFallback = $slideFile?->detail_jpeg_url
                                        ?? $slideFile?->grid_jpeg_url
                                        ?? $slideFile?->path;
                                    $slideAlt = strip_tags($slide->caption_1 ?? '') ?: 'Hero slide';
                                @endphp

                                <a href="{{ $slide->call_to_action_url }}" class="swiper-slide">
                                    <div
                                        class="slider-bg-image"
                                        data-swiper-parallax-x="50%"
                                    >
                                        @if (!empty($slideFallback))
                                            <picture>
                                                @if (!empty($slideAvifSrcset))
                                                    <source srcset="{{ $slideAvifSrcset }}" sizes="100vw" type="image/avif">
                                                @endif

                                                @if (!empty($slideWebpSrcset))
                                                    <source srcset="{{ $slideWebpSrcset }}" sizes="100vw" type="image/webp">
                                                @endif

                                                <img
                                                    src="{{ $slideFallback }}"
                                                    @if (!empty($slideJpegSrcset))
                                                        srcset="{{ $slideJpegSrcset }}"
                                                    @endif
                                                    sizes="100vw"
                                                    alt="{{ $slideAlt }}"
                                                    loading="eager"
                                                    decoding="async"
                                                >
                                            </picture>
                                        @endif
                                    </div>

                                    <div class="slide-content {{ $slide->isAlignedLeft() ? 'align-left' : 'align-right' }}">
                                        <div class="captions">
                                            <span
                                                class="caption caption-1"
                                                data-swiper-parallax-opacity="0.5"
                                            >
                                                {!! $slide->caption_1 !!}
                                            </span>

                                            <span
                                                class="caption caption-2"
                                                data-swiper-parallax-x="40%"
                                                data-swiper-parallax-opacity="0"
                                            >
                                                {{ $slide->caption_2 }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        
                        @if ($slider->dots) 
                            <div class="swiper-pagination"></div>
                        @endif

                        @if ($slider->arrows)
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        @endif
                    </div>
                </div>

                @include('storefront::public.home.sections.slider_banners')
            </div>
        </div>
    </div>
</section>
