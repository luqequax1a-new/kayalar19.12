@php
     $xDataParam = is_null($data ?? null)
         ? 'product'
         : (is_string($data ?? null)
             ? ($data ?? 'product')
             : json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT));
 @endphp
 <div x-data='ProductCard({{ $xDataParam }})' class="vertical-product-card">
    <a :href="productUrl" class="product-image">
        <img
            :src="baseImage"
            :class="{ 'image-placeholder': !hasBaseImage }"
            :alt="productName"
            loading="lazy"
        />

        <div class="product-image-layer"></div>
    </a>

    <div class="product-info">
        <a :href="productUrl" class="product-name">
            <span x-text="productName"></span>
        </a>

        <template x-if="hasVisibleRating">
            @include('storefront::public.partials.product_rating', ['data' => $data ?? null])
        </template>
        
        <div class="product-price" x-html="productPrice"></div>
    </div>
</div>
