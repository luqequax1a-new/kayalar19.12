@foreach ($product->variations as $variation)
    <div class="variant-custom-selection">
        <div class="row">
            <div class="col-lg-18">
                <span class="d-flex">
                    {{ $variation->name }}:
                    
                    <span class="d-flex variation-label" x-text="activeVariationValues['{{ $variation->uid }}']">
                        @if($product->variant)
                            @php($currentVal = $variation->values->first(fn($v) => in_array($v->uid, explode('.', (string) $product->variant->uids))))
                            {{ $currentVal?->label }}
                        @endif
                    </span>
                </span>
            </div>

            <div class="col-lg-18">               
                <ul class="list-inline form-custom-radio custom-selection">
                    @foreach ($variation->values as $value)
                        <li
                            :title="
                                isVariationValueEnabled('{{ $variation->uid }}', {{ $loop->parent->index }}, '{{ $value->uid }}') &&
                                !isActiveVariationValue('{{ $variation->uid }}', '{{ $value->uid }}') ?
                                '{{ trans('storefront::product.click_to_select') }} {{ $value->label }}' :
                                ''
                            "
                            class="
                                {{ $variation->type === 'color' ? 'variation-color' : '' }}
                                {{ $variation->type === 'image' ? 'variation-image' : '' }}
                                @if($product->variant && in_array($value->uid, explode('.', (string) $product->variant->uids ?? ''))) active @endif
                            "
                            :class="{
                                active: isActiveVariationValue('{{ $variation->uid }}', '{{ $value->uid }}'),
                                disabled: !isVariationValueEnabled('{{ $variation->uid }}', {{ $loop->parent->index }}, '{{ $value->uid }}')

                            }"
                            @mouseenter="prefetchVariantMedia({{ $loop->parent->index }}, {{ $loop->index }}); setVariationValueLabel({{ $loop->parent->index }}, {{ $loop->index }})"
                            @mouseleave="setActiveVariationValueLabel({{ $loop->parent->index }})"
                            @touchstart.passive="prefetchVariantMedia({{ $loop->parent->index }}, {{ $loop->index }})"
                            @click="syncVariationValue(
                                '{{ $variation->uid }}',
                                {{ $loop->parent->index }},
                                '{{ $value->uid }}',
                                {{ $loop->index }}
                            )"
                        >
                            @if ($variation->type === 'text')
                                {{ $value->label }}
                            @elseif ($variation->type === 'color')
                                <div
                                    style="background-color: {{ $value->color }};"
                                    role="img"
                                    aria-label="{{ $product->name }} {{ $value->label }}"
                                    title="{{ $product->name }} {{ $value->label }}"
                                ></div>
                            @elseif ($variation->type === 'image' && $value->image)
                                @php(
                                    $imageJpeg = $value->image->thumb_jpeg_url
                                        ?? $value->image->grid_jpeg_url
                                        ?? $value->image->detail_jpeg_url
                                        ?? $value->image->path
                                )
                                <picture>
                                    @if ($value->image->listing_avif_srcset)
                                        <source
                                            type="image/avif"
                                            srcset="{{ $value->image->listing_avif_srcset }}"
                                            sizes="65px"
                                        >
                                    @endif
                                    @if ($value->image->listing_webp_srcset)
                                        <source
                                            type="image/webp"
                                            srcset="{{ $value->image->listing_webp_srcset }}"
                                            sizes="65px"
                                        >
                                    @endif
                                    <img
                                        src="{{ $imageJpeg }}"
                                        @if ($value->image->listing_jpeg_srcset)
                                            srcset="{{ $value->image->listing_jpeg_srcset }}"
                                        @endif
                                        sizes="65px"
                                        alt="{{ $product->name }} {{ $value->label }}"
                                        width="65"
                                        height="65"
                                        loading="{{ $loop->index < 6 ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $loop->index < 6 ? 'high' : 'auto' }}"
                                        decoding="async"
                                    >
                                </picture>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endforeach
