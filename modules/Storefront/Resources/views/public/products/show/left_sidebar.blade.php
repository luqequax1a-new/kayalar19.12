<aside class="left-sidebar">
    @if (setting('storefront_product_page_upsell_products_enabled') && !empty($upSellProducts) && $upSellProducts->isNotEmpty())
        <div class="vertical-products" data-upsell-products>
            <div class="vertical-products-header">
                <div class="section-title">
                    {{ setting('storefront_product_page_upsell_products_title') ?: trans('storefront::product.you_might_also_like') }}
                </div>
            </div>

            <div class="vertical-products-slider swiper" x-ref="upSellProducts">
                <div x-cloak class="swiper-wrapper">
                    @foreach ($upSellProducts->chunk(5) as $chunk)
                        <div class="swiper-slide">
                            <div class="vertical-products-slide">
                                @foreach ($chunk as $p)
                                    @include('storefront::public.partials.vertical_products', ['data' => $p])
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

    @if ($banner->image->exists)
        <a
            href="{{ $banner->call_to_action_url }}"
            class="banner d-none d-lg-block"
            target="{{ $banner->open_in_new_window ? '_blank' : '_self' }}"
        >
            <img src="{{ $banner->image->path }}" alt="Banner" loading="lazy" />
        </a>
    @endif
</aside>
