<div class="order-details-middle">
    <div class="order-details-card order-items-card">
        <h4 class="m-b-0">{{ trans('storefront::account.view_order.items_ordered') }}</h4>

        <div class="order-product-list">
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
                                $attributes[] = $variation->name . ': ' . $label;
                            }
                        }
                    }
                    if ($product->hasAnyOption()) {
                        foreach ($product->options as $option) {
                            $val = $option->isFieldType() ? $option->value : $option->values->implode('label', ', ');
                            if ($val) {
                                $attributes[] = $option->name . ': ' . $val;
                            }
                        }
                    }
                    $attributesText = implode(' • ', $attributes);
                @endphp

                <div class="order-product-card">
                    <div class="order-product-card-image">
                        @if ($imagePath)
                            <img src="{{ $imagePath }}" alt="{{ $product->name }}">
                        @else
                            <img src="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}">
                        @endif
                    </div>

                    <div class="order-product-card-content">
                        <a href="{{ $product->url() }}" class="product-name">{{ $product->name }}</a>
                        @if (!empty($attributesText))
                            <div class="product-attrs">{{ $attributesText }}</div>
                        @endif
                        @if ($product->sku)
                            <div class="product-sku">{{ trans('storefront::product.sku') }} {{ $product->sku }}</div>
                        @endif

                        <div class="product-meta">
                            <div class="meta-row">
                                <span class="meta-label">{{ trans('storefront::account.view_order.quantity') }}</span>
                                <span class="meta-value">{{ $product->getFormattedQuantityWithUnit() }}</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">{{ trans('storefront::account.view_order.line_total') }}</span>
                                <span class="meta-value">{{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
