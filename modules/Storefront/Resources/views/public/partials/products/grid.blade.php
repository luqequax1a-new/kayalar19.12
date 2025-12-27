<div class="grid-view-products">
    @if (isset($products) && $products)
        @foreach ($products as $idx => $product)
            <div class="grid-view-products-item">
                @include('storefront::public.partials.product_card', ['data' => $product])
            </div>
        @endforeach
    @else
        <template
            x-for="(product, idx) in visibleProducts"
            :key="product.listing_key || product.id"
        >
            <div class="grid-view-products-item">
                @include('storefront::public.partials.product_card')
            </div>
        </template>
    @endif
</div>
