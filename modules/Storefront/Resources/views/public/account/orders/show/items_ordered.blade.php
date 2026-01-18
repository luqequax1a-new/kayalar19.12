<div class="order-items-section">
    <h5 class="section-subtitle"><i class="las la-shopping-basket"></i> {{ trans('storefront::account.view_order.items_ordered') }}</h5>

    <div class="order-product-list minimal">
        @foreach ($order->products as $product)
            @php
                $imagePath = $product->product_variant?->base_image?->path
                    ?? $product->product?->base_image?->path
                    ?? $product->product_image_path;
                
                $attributes = [];
                if ($product->hasAnyVariation()) {
                    foreach ($product->variations as $variation) {
                        $label = $variation->values()->first()?->label;
                        if ($label) {
                            $attributes[] = $label;
                        }
                    }
                }
                if ($product->hasAnyOption()) {
                    foreach ($product->options as $option) {
                        $val = $option->isFieldType() ? $option->value : $option->values->implode('label', ', ');
                        if ($val) {
                            $attributes[] = $val;
                        }
                    }
                }
                $variantText = !empty($attributes) ? ' (' . implode(', ', $attributes) . ')' : '';
                $hasDiscount = $product->is_upsell && $product->original_price;
            @endphp

            <div class="minimal-cart-item {{ $product->is_upsell ? 'is-upsell' : '' }}">
                <div class="item-visual">
                    @if ($imagePath)
                        <img src="{{ filter_var($imagePath, FILTER_VALIDATE_URL) ? $imagePath : Illuminate\Support\Facades\Storage::url($imagePath) }}" alt="{{ $product->name }}">
                    @else
                        <img src="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}">
                    @endif
                </div>

                <div class="item-details">
                    <div class="item-header">
                        @if ($product->is_upsell)
                            <span class="upsell-mini-badge">{{ trans('storefront::upsell.offer_badge') }}</span>
                        @endif
                        <a href="{{ $product->url() }}" class="item-name">{{ $product->name }}{{ $variantText }}</a>
                    </div>

                    <div class="item-meta">
                        @if ($product->sku)
                            <span class="meta-unit">SKU: {{ $product->sku }}</span>
                        @endif
                        <span class="meta-unit">{{ trans('storefront::account.view_order.quantity') }}: <strong>{{ $product->getFormattedQuantityWithUnit() }}</strong></span>
                    </div>

                    <div class="item-price-row {{ $hasDiscount ? 'is-discounted' : '' }}">
                        @if ($hasDiscount)
                            <span class="old-price">{{ $product->original_price->multiply($product->qty)->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                        @endif
                        <span class="current-price">{{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
