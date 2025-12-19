@if (!empty($upSellProducts) && $upSellProducts->isNotEmpty())
    <section data-upsell-products>
        <div class="tab-products-header text-center">
            <h3 class="section-title section-title--no-divider">
                {{ setting('storefront_product_page_upsell_products_title') ?: trans('storefront::product.you_might_also_like') }}
            </h3>
        </div>

        <div class="grid-products products-slider swiper related-products-carousel">
            <div class="swiper-wrapper">
                @foreach ($upSellProducts as $p)
                    <div class="swiper-slide">
                        <div class="grid-view-products-item">
                            @include('storefront::public.partials.product_card', ['data' => $p])
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="swiper-pagination"></div>
        </div>
    </section>
@endif
