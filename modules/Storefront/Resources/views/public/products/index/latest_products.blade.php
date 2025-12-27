@if ($latestProducts->isNotEmpty())
    <div class="vertical-products">
        <div class="vertical-products-header">
            <h5 class="section-title">{{ trans('storefront::products.latest_products') }}</h5>
        </div>

        <div class="vertical-products-slider swiper" x-ref="latestProducts">
            <div x-cloak class="swiper-wrapper">
                @foreach ($latestProducts->chunk(5) as $latestProductChunks)
                    <div class="swiper-slide">
                        <div class="vertical-products-slide">
                            @foreach ($latestProductChunks as $latestProduct)
                                <div x-data='ProductCard(@json($latestProduct))' class="vertical-product-card">
                                    <a :href="productUrl" class="product-image">
                                        <picture>
                                            <template x-if="currentSourceFile?.listing_avif_srcset">
                                                <source
                                                    type="image/avif"
                                                    :srcset="currentSourceFile.listing_avif_srcset"
                                                    sizes="80px"
                                                >
                                            </template>
                                            <template x-if="currentSourceFile?.listing_webp_srcset">
                                                <source
                                                    type="image/webp"
                                                    :srcset="currentSourceFile.listing_webp_srcset"
                                                    sizes="80px"
                                                >
                                            </template>
                                            <template x-if="Boolean(currentSourceFile?.listing_jpeg_srcset)">
                                                <source
                                                    type="image/jpeg"
                                                    :srcset="currentSourceFile.listing_jpeg_srcset"
                                                    sizes="80px"
                                                >
                                            </template>

                                            <img
                                                :src="currentSourceFile?.thumb_jpeg_url || currentSourceFile?.card_jpeg_url || currentSourceFile?.grid_jpeg_url || currentSourceFile?.path || baseImage"
                                                :class="{ 'image-placeholder': !hasBaseImage }"
                                                :alt="productName"
                                                loading="lazy"
                                                decoding="async"
                                                width="80"
                                                height="80"
                                            />
                                        </picture>

                                        <div class="product-image-layer"></div>
                                    </a>

                                    <div class="product-info">
                                        <a :href="productUrl" class="product-name">
                                            <span x-text="productName"></span>
                                        </a>

                                        <template x-if="hasVisibleRating">
                                            @include('storefront::public.partials.product_rating', ['data' => $latestProduct, 'showLabel' => false])
                                        </template>

                                        <div class="product-price" x-html="productPrice"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
@endif
