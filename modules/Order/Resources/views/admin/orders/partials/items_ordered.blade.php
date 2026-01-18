<div class="items-ordered-wrapper">
    <h4 class="section-title">{{ trans('order::orders.items_ordered') }}</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="items-ordered">
                <div class="table-responsive">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Görsel</th>
                                <th>{{ trans('order::orders.product') }}</th>
                                <th>Stok Kodu</th>
                                <th>{{ trans('order::orders.unit_price') }}</th>
                                <th>{{ trans('order::orders.quantity') }}</th>
                                <th>{{ trans('order::orders.line_total') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($order->products as $product)
                                <tr>
                                    @php
                                        $imagePath = $product->product_variant?->base_image?->path
                                            ?? $product->product?->base_image?->path
                                            ?? $product->product_image_path;
                                    @endphp
                                    <td class="image-col" data-label="Görsel">
                                        <div class="product-media">
                                            @if ($imagePath)
                                                <div class="product-image-preview">
                                                    <img src="{{ $imagePath }}" alt="{{ $product->name }}" />
                                                    <div class="image-hover-preview">
                                                        <img src="{{ $imagePath }}" alt="{{ $product->name }}" />
                                                    </div>
                                                </div>
                                            @else
                                                <div class="product-image-preview">
                                                    <img src="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}" />
                                                    <div class="image-hover-preview">
                                                        <img src="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}" />
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="name-col" data-label="{{ trans('order::orders.product') }}">
                                        <div class="product-info">
                                            @if ($product->is_upsell)
                                                <div style="margin-bottom: 2px;">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold" style="display: inline-block; background-color:#fef3c7;color:#92400e; font-size: 10px; border-radius: 4px; padding: 2px 6px;">
                                                        {{ trans('storefront::upsell.offer_badge') }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div>
                                                @if ($product->trashed())
                                                    {{ $product->name }}
                                                @else
                                                    <a href="{{ route('admin.products.edit', $product->product->id) }}">{{ $product->name }}</a>
                                                @endif
                                            </div>

                                            @php
                                                $variantSegments = [];
                                                $optionSegments = [];

                                                if ($product->hasAnyVariation()) {
                                                    foreach ($product->variations as $variation) {
                                                        $valueLabel = $variation->values->first()?->label;

                                                        if ($valueLabel) {
                                                            $variantSegments[] = $variation->name . ': ' . $valueLabel;
                                                        }
                                                    }
                                                }

                                                if ($product->hasAnyOption()) {
                                                    foreach ($product->options as $option) {
                                                        if ($option->option->isFieldType()) {
                                                            if (! empty($option->value)) {
                                                                $optionSegments[] = $option->name . ': ' . $option->value;
                                                            }
                                                        } else {
                                                            $values = $option->values->pluck('label')->filter()->implode(', ');

                                                            if (! empty($values)) {
                                                                $optionSegments[] = $option->name . ': ' . $values;
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp

                                            @if (! empty($variantSegments))
                                                <div class="product-attributes-text">
                                                    {{ implode(' · ', $variantSegments) }}
                                                </div>
                                            @endif

                                            @if (! empty($optionSegments))
                                                <div class="product-attributes-text">
                                                    {{ implode(' · ', $optionSegments) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="sku-col" data-label="Stok Kodu">
                                        <span class="sku-text">{{ $product->sku }}</span>
                                    </td>

                                    <td class="price-col" data-label="{{ trans('order::orders.unit_price') }}">
                                        @if ($product->is_upsell && $product->original_price)
                                            <span style="color: #94a3b8; font-size: 12px; text-decoration: line-through; margin-right: 6px;">{{ $product->original_price->format() }}</span>
                                        @endif
                                        {{ $product->unit_price->format() }}
                                    </td>

                                    <td class="qty-col" data-label="{{ trans('order::orders.quantity') }}">
                                        {{ $product->getFormattedQuantityWithUnit() }}
                                    </td>

                                    <td class="total-col" data-label="{{ trans('order::orders.line_total') }}">
                                        @if ($product->is_upsell && $product->original_price)
                                            <span style="color: #94a3b8; font-size: 11px; text-decoration: line-through; margin-right: 4px;">{{ $product->original_price->multiply($product->qty)->format() }}</span>
                                        @endif
                                        {{ $product->line_total->format() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-order-items d-block d-md-none">
                    @foreach ($order->products as $product)
                        @php
                            $imagePath = $product->product_variant?->base_image?->path
                                ?? $product->product?->base_image?->path
                                ?? $product->product_image_path;
                        @endphp
                        <div class="mobile-order-item-card bg-white rounded-3 p-3 mb-3 w-100">
                            <div class="d-flex align-items-start gap-3 w-100">
                                <div class="mobile-image">
                                    @if ($imagePath)
                                        <div class="product-image-preview">
                                            <img src="{{ $imagePath }}" alt="{{ $product->name }}" class="mobile-img" />
                                            <div class="image-hover-preview">
                                                <img src="{{ $imagePath }}" alt="{{ $product->name }}" />
                                            </div>
                                        </div>
                                    @else
                                        <div class="product-image-preview">
                                            <img src="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}" class="mobile-img" />
                                            <div class="image-hover-preview">
                                                <img src="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}" />
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-grow-1">
                                    @if ($product->is_upsell)
                                        <div style="margin-bottom: 2px;">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold" style="display: inline-block; background-color:#fef3c7;color:#92400e; font-size: 10px; border-radius: 4px; padding: 2px 6px;">
                                                {{ trans('storefront::upsell.offer_badge') }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="fw-semibold product-name">{{ $product->name }}</div>

                                    @if ($product->hasAnyVariation() || $product->hasAnyOption())
                                        <div class="mobile-variant" style="font-size: 13px; margin-top: 2px;">
                                            @if ($product->hasAnyVariation())
                                                @foreach ($product->variations as $variation)
                                                    <span>{{ $variation->name }}: {{ $variation->values()->first()?->label }}</span>@if(!$loop->last), @endif
                                                @endforeach
                                            @endif
                                            @if ($product->hasAnyOption())
                                                @foreach ($product->options as $option)
                                                    <span>{{ $option->name }}:
                                                        @if ($option->option->isFieldType())
                                                            {{ $option->value }}
                                                        @else
                                                            {{ $option->values->implode('label', ', ') }}
                                                        @endif
                                                    </span>@if(!$loop->last), @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif

                                    <div class="mt-2 mobile-pricing">
                                        <div class="mobile-meta-row d-flex justify-content-between py-2">
                                            <span class="meta-label text-muted">Birim Fiyat</span>
                                            <span class="meta-value fw-semibold">
                                                @if ($product->is_upsell && $product->original_price)
                                                    <span style="color: #94a3b8; font-size: 11px; text-decoration: line-through; margin-right: 4px;">{{ $product->original_price->format() }}</span>
                                                @endif
                                                {{ $product->unit_price->format() }} /{{ $product->product->unit_suffix ?? '' }}
                                            </span>
                                        </div>
                                        <div class="mobile-meta-row d-flex justify-content-between py-2">
                                            <span class="meta-label text-muted">Miktar</span>
                                            <span class="meta-value fw-semibold">{{ $product->getFormattedQuantityWithUnit() }}</span>
                                        </div>
                                        <div class="mobile-meta-row d-flex justify-content-between py-2">
                                            <span class="meta-label text-muted">Toplam</span>
                                            <span class="meta-value fw-bold" style="color: #e53935;">
                                                @if ($product->is_upsell && $product->original_price)
                                                    <span style="color: #94a3b8; font-size: 11px; text-decoration: line-through; margin-right: 4px;">{{ $product->original_price->multiply($product->qty)->format() }}</span>
                                                @endif
                                                {{ $product->line_total->format() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>