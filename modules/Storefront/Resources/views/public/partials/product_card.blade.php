@php
     $xDataParam = is_null($data ?? null)
         ? 'product'
         : (is_string($data ?? null)
             ? ($data ?? 'product')
             : json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT));
 @endphp
 <div x-data='ProductCard({{ $xDataParam }}, (typeof index !== "undefined" ? index : (typeof idx !== "undefined" ? idx : 0)))' class="product-card">
    <div class="product-card-top">
        <a :href="productUrl" class="product-image" style="position: relative;">
            <template x-if="product.tag_badges && product.tag_badges.length">
                <template x-for="pos in ['top_left','top_right','bottom_left','bottom_right']" :key="'pos-' + pos">
                    <div
                        class="product-badge-labels product-badge-labels--listing"
                        :class="'product-badge-labels--' + pos"
                        x-show="product.tag_badges.some(b => (b.listing_position || 'top_left') === pos)"
                    >
                        <template x-for="badge in product.tag_badges.filter(b => (b.listing_position || 'top_left') === pos)"
                                  :key="badge.name + '-' + pos">
                            <div class="product-badge-label">
                                <template x-if="badge.image_url">
                                    <img :src="badge.image_url" :alt="badge.name" loading="lazy">
                                </template>

                                <template x-if="!badge.image_url">
                                    <span x-text="badge.name"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </template>

            <div class="product-image-wrap" x-init="init && init()">
                <template x-if="Array.isArray(galleryItems) && galleryItems.length > 1">
                    <div class="product-image-carousel swiper" x-ref="gallery">
                        <div class="swiper-wrapper product-image-track">
                            <template x-for="(file, gidx) in galleryItems" :key="(file && file.path ? file.path : gidx)">
                                <div class="swiper-slide product-image-slide">
                                    <div
                                        class="product-image-shell"
                                        :data-real-img="(file?.fast_avif_url
                                            || file?.fast_webp_url
                                            || file?.grid_webp_url
                                            || file?.grid_avif_url
                                            || file?.detail_webp_url
                                            || file?.detail_avif_url
                                            || file?.path
                                            || baseImage)"
                                        :data-alt="productName"
                                    >
                                        <img
                                            class="product-image-img lqip"
                                            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop offset='0' stop-color='%23f2f2f2'/%3E%3Cstop offset='1' stop-color='%23e6e6e6'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='40' height='40' fill='url(%23g)'/%3E%3Ccircle cx='12' cy='14' r='8' fill='%23ededed'/%3E%3Crect x='18' y='20' width='18' height='10' rx='3' fill='%23eaeaea'/%3E%3C/svg%3E"
                                            width="400"
                                            height="400"
                                            decoding="async"
                                        />
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="product-card-dots swiper-pagination" x-ref="galleryPagination" @click.stop.prevent></div>
                        <button type="button" class="product-image-nav product-image-nav--prev" x-ref="galleryPrev" @click.stop.prevent>
                            <svg class="product-image-nav__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button type="button" class="product-image-nav product-image-nav--next" x-ref="galleryNext" @click.stop.prevent>
                            <svg class="product-image-nav__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <template x-if="!(Array.isArray(galleryItems) && galleryItems.length > 1)">
                    <div
                        x-ref="pshell"
                        class="product-image-shell"
                        :data-real-img="(currentSourceFile?.fast_avif_url
                            || currentSourceFile?.fast_webp_url
                            || currentSourceFile?.grid_webp_url
                            || currentSourceFile?.grid_avif_url
                            || currentSourceFile?.detail_webp_url
                            || currentSourceFile?.detail_avif_url
                            || currentSourceFile?.path
                            || baseImage)"
                        :data-alt="productName"
                    >
                        <img
                            class="product-image-img lqip"
                            src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop offset='0' stop-color='%23f2f2f2'/%3E%3Cstop offset='1' stop-color='%23e6e6e6'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='40' height='40' fill='url(%23g)'/%3E%3Ccircle cx='12' cy='14' r='8' fill='%23ededed'/%3E%3Crect x='18' y='20' width='18' height='10' rx='3' fill='%23eaeaea'/%3E%3C/svg%3E"
                            width="400"
                            height="400"
                            decoding="async"
                        />
                    </div>
                </template>
            </div>

            @if (setting('storefront_grid_variant_badge_enabled'))
                <div
                    class="variant-grid-badge"
                    x-show="(product.variants && product.variants.length) || product.variant"
                >
                    <span
                        x-text="((product.variants && product.variants.length)
                            ? product.variants.length
                            : (product.variant ? 1 : 0))
                            + ((product.variations && product.variations.length && product.variations[0].name)
                                ? (' ' + product.variations[0].name)
                                : '')"
                    ></span>
                </div>
            @endif

            <div class="product-image-layer"></div>
        </a>


        <div class="product-card-actions">
            <button
                class="btn btn-compare"
                :class="{ added: inCompareList }"
                title="{{ trans('storefront::product_card.compare') }}"
                @click="syncCompareList"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M13.6667 3.66675H6.33333C3.85781 3.66675 2 5.45677 2 8.00008" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2.33301 12.3333H9.66634C12.1419 12.3333 13.9997 10.5433 13.9997 8" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12.333 2C12.333 2 13.9997 3.22748 13.9997 3.66668C13.9997 4.10588 12.333 5.33333 12.333 5.33333" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3.66665 10.6667C3.66665 10.6667 2.00001 11.8942 2 12.3334C1.99999 12.7726 3.66667 14.0001 3.66667 14.0001" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <ul class="list-inline product-badge" :class="{ 'is-empty': !(isOutOfStock || isNew || hasPercentageSpecialPrice || hasSpecialPrice) }">
            <template x-if="isOutOfStock">
                <li class="badge badge-danger">
                    {{ trans("storefront::product_card.out_of_stock") }}
                </li>
            </template>

            <template x-if="isNew">
                <li class="badge badge-info">
                    {{ trans("storefront::product_card.new") }}
                </li>
            </template>

            <template x-if="hasPercentageSpecialPrice">
                <li
                    class="badge badge-success"
                    x-text="`-${item.special_price_percent}%`"
                >
                </li>
            </template>

            <template x-if="hasSpecialPrice && !hasPercentageSpecialPrice">
                <li
                    class="badge badge-success"
                    x-text="`-${specialPricePercent}%`"
                >
                </li>
            </template>
        </ul>
    </div>

    <div class="product-card-middle" :class="{ 'has-rating': hasVisibleRating }">
        <div class="variant-thumbnails" x-show="!product.list_variants_separately && product.variants && product.variants.length">
            <template x-for="(variant, idx) in product.variants" :key="variant.id">
                <template x-if="idx < 3">
                    <button type="button" class="variant-thumb" @mouseenter="previewVariant(variant)" @mouseleave="clearPreview()" @click="selectVariant(variant)">
                        <img
                            :src="(variant.base_image?.thumb_webp_url
                                    || variant.base_image?.thumb_jpeg_url
                                    || (variant.base_image && variant.base_image.path)
                                    || (baseImageThumb || baseImage))"
                            :alt="variant.name"
                            loading="lazy"
                            width="80"
                            height="80"
                        />
                    </button>
                </template>
            </template>
            <template x-if="product.variants.length > 3">
                <a :href="productUrl" class="variant-count">+<span x-text="product.variants.length - 3"></span></a>
            </template>
        </div>

        {{-- inline all-variants grid disabled; +N links to product detail --}}

        <a :href="productUrl" class="product-name">
            <span x-text="productName"></span>
        </a> 
        
        <template x-if="hasVisibleRating">
            @include('storefront::public.partials.product_rating', ['data' => $data ?? null, 'showLabel' => false])
        </template>

        <div class="product-price" x-html="productPrice"></div>
    </div>

    

    <div class="product-card-bottom">
        <template x-if="hasNoOption || isOutOfStock">
            <button
                class="btn btn-primary btn-add-to-cart"
                :class="{ 'btn-loading': addingToCart }"
                :disabled="isOutOfStock"
                title="{{ trans('storefront::product_card.add_to_cart') }}"
                @click="addToCart"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <g clip-path="url(#clip0_2055_61)">
                        <path d="M1.3335 1.33325H2.20427C2.36828 1.33325 2.45029 1.33325 2.51628 1.36341C2.57444 1.38999 2.62373 1.43274 2.65826 1.48655C2.69745 1.54761 2.70905 1.6288 2.73225 1.79116L3.04778 3.99992M3.04778 3.99992L3.74904 9.15419C3.83803 9.80827 3.88253 10.1353 4.0389 10.3815C4.17668 10.5984 4.37422 10.7709 4.60773 10.8782C4.87274 10.9999 5.20279 10.9999 5.8629 10.9999H11.5682C12.1965 10.9999 12.5107 10.9999 12.7675 10.8869C12.9939 10.7872 13.1881 10.6265 13.3283 10.4227C13.4875 10.1917 13.5462 9.88303 13.6638 9.26576L14.5462 4.63305C14.5876 4.41579 14.6083 4.30716 14.5783 4.22225C14.552 4.14777 14.5001 4.08504 14.4319 4.04526C14.3541 3.99992 14.2435 3.99992 14.0223 3.99992H3.04778ZM6.66683 13.9999C6.66683 14.3681 6.36835 14.6666 6.00016 14.6666C5.63197 14.6666 5.3335 14.3681 5.3335 13.9999C5.3335 13.6317 5.63197 13.3333 6.00016 13.3333C6.36835 13.3333 6.66683 13.6317 6.66683 13.9999ZM12.0002 13.9999C12.0002 14.3681 11.7017 14.6666 11.3335 14.6666C10.9653 14.6666 10.6668 14.3681 10.6668 13.9999C10.6668 13.6317 10.9653 13.3333 11.3335 13.3333C11.7017 13.3333 12.0002 13.6317 12.0002 13.9999Z" stroke="white" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <defs>
                        <clipPath id="clip0_2055_61">
                            <rect width="16" height="16" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
            </button>
        </template>
        
        <template x-if="!(hasNoOption || isOutOfStock)">
            <a
                :href="productUrl"
                title="{{ trans('storefront::product_card.view_options') }}"
                class="btn btn-primary btn-add-to-cart"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M14.3623 7.3635C14.565 7.6477 14.6663 7.78983 14.6663 8.00016C14.6663 8.2105 14.565 8.35263 14.3623 8.63683C13.4516 9.9139 11.1258 12.6668 7.99967 12.6668C4.87353 12.6668 2.54774 9.9139 1.63703 8.63683C1.43435 8.35263 1.33301 8.2105 1.33301 8.00016C1.33301 7.78983 1.43435 7.6477 1.63703 7.3635C2.54774 6.08646 4.87353 3.3335 7.99967 3.3335C11.1258 3.3335 13.4516 6.08646 14.3623 7.3635Z" stroke="white" stroke-width="1"/>
                    <path d="M10 8C10 6.8954 9.1046 6 8 6C6.8954 6 6 6.8954 6 8C6 9.1046 6.8954 10 8 10C9.1046 10 10 9.1046 10 8Z" stroke="white" stroke-width="1"/>
                </svg>
            </a>
        </template>
    </div>
</div>
