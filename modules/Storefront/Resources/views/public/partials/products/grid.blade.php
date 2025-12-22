<div class="grid-view-products">
    <template
        x-for="(product, idx) in visibleProducts"
        :key="product.listing_key || product.id"
    >
        <div class="grid-view-products-item">
            @include('storefront::public.partials.product_card')
        </div>
    </template>
</div>
