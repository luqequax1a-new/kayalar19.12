@if (!empty($relatedProducts) && $relatedProducts->isNotEmpty())
    <section data-related-products>
        <div class="tab-products-header text-center">
            <h3 class="section-title section-title--no-divider">{{ trans("storefront::product.related_products") }}</h3>
        </div>

        <div class="grid-products products-slider swiper related-products-carousel">
            <div class="swiper-wrapper">
                @foreach ($relatedProducts as $rp)
                    <div class="swiper-slide">
                        <div class="grid-view-products-item">
                            @include('storefront::public.partials.product_card', ['data' => $rp])
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="swiper-pagination"></div>
        </div>
    </section>
@endif