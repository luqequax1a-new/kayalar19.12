<div class="product-details-info position-relative flex-grow-1"> 
    <script>
        window.FleetCart = window.FleetCart || {};
        window.FleetCart.data = window.FleetCart.data || {};
        window.FleetCart.data.productId = {{ (int) $product->id }};
        window.FleetCart.data.productInWishlist = {{ auth()->check() && auth()->user()->wishlistHas($product->id) ? 'true' : 'false' }};
    </script>
    <div class="details-info-top" style="min-height: 150px;">
        <div class="details-top-bar">
            @php($serverTitle = $product->name . (((string) ($item->name ?? '')) !== '' && (string) ($item->name ?? '') !== (string) $product->name ? (' (' . $item->name . ')') : ''))
            <h1 class="product-name" x-text="productName || $el.textContent">{{ $serverTitle }}</h1>

            <button
                class="btn btn-wishlist"
                :class="{ 'added': inWishlist }"
                @click="syncWishlist"
                aria-label="{{ trans('storefront::product.wishlist') }}"
            >
                <template x-if="inWishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </template>
                
                <template x-if="!inWishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none">
                        <path d="M12.1 21.35l-.1.1-.11-.1C6.14 16.24 2.5 12.97 2.5 8.5 2.5 5.64 4.82 3.5 7.5 3.5c1.74 0 3.41.81 4.5 2.09C13.09 4.31 14.76 3.5 16.5 3.5c2.68 0 5 2.14 5 5 0 4.47-3.64 7.74-9.4 12.85z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </template>

                <span class="visually-hidden">{{ trans('storefront::product.wishlist') }}</span>
            </button>
        </div>

        @if (setting('reviews_enabled') && (int) ($review->count ?? 0) > 0)
            @include('storefront::public.partials.product_rating', ['data' => 'product'])
        @endif

        @php($unitSuffixText = $product->unit_suffix ? (' /' . $product->unit_suffix) : '')
        @php($serverRegularAmount = (float) optional($item->price)->convertToCurrentCurrency()->amount())
        @php($serverSpecialAmount = (float) ((method_exists($item, 'hasSpecialPrice') && $item->hasSpecialPrice()) ? $item->getSpecialPrice()->convertToCurrentCurrency()->amount() : $serverRegularAmount))
        @php($serverRegularFormatted = optional(optional($item->price)->convertToCurrentCurrency())->format())
        @php($serverSpecialFormatted = (method_exists($item, 'hasSpecialPrice') && $item->hasSpecialPrice()) ? $item->getSpecialPrice()->convertToCurrentCurrency()->format() : $serverRegularFormatted)
        @php($hasServerDiscount = $serverSpecialAmount < $serverRegularAmount)

        @if ($product->variant)
            <div class="product-price" x-show="isActiveItem">
                <span
                    class="product-discount-badge"
                    x-show="specialPrice < regularPrice"
                    x-text="'%' + Math.round((1 - (specialPrice / regularPrice)) * 100)"
                    style="{{ $hasServerDiscount ? '' : 'display:none' }}"
                >{{ $serverRegularAmount > 0 ? ('%' . (string) (int) round((1 - ($serverSpecialAmount / $serverRegularAmount)) * 100)) : '' }}</span>

                <div class="product-price-values">
                    <span
                        class="previous-price"
                        x-show="specialPrice < regularPrice"
                        x-text="formatCurrency(regularPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')"
                        style="{{ $hasServerDiscount ? '' : 'display:none' }}"
                    >{{ ($serverRegularFormatted ?: '') . $unitSuffixText }}</span>

                    <span
                        class="special-price"
                        x-show="specialPrice < regularPrice"
                        x-text="formatCurrency(specialPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')"
                        style="{{ $hasServerDiscount ? '' : 'display:none' }}"
                    >{{ ($serverSpecialFormatted ?: '') . $unitSuffixText }}</span>

                    <span
                        class="special-price"
                        x-show="!(specialPrice < regularPrice)"
                        x-text="formatCurrency(regularPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')"
                        style="{{ $hasServerDiscount ? 'display:none' : '' }}"
                    >{{ ($serverRegularFormatted ?: '') . $unitSuffixText }}</span>
                </div>
            </div>
        @else
            <div class="product-price">
                <span
                    class="product-discount-badge"
                    x-show="specialPrice < regularPrice"
                    x-text="'%' + Math.round((1 - (specialPrice / regularPrice)) * 100)"
                    style="{{ $hasServerDiscount ? '' : 'display:none' }}"
                >{{ $serverRegularAmount > 0 ? ('%' . (string) (int) round((1 - ($serverSpecialAmount / $serverRegularAmount)) * 100)) : '' }}</span>

                <div class="product-price-values">
                    <span
                        class="previous-price"
                        x-show="specialPrice < regularPrice"
                        x-text="formatCurrency(regularPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')"
                        style="{{ $hasServerDiscount ? '' : 'display:none' }}"
                    >{{ ($serverRegularFormatted ?: '') . $unitSuffixText }}</span>

                    <span
                        class="special-price"
                        x-show="specialPrice < regularPrice"
                        x-text="formatCurrency(specialPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')"
                        style="{{ $hasServerDiscount ? '' : 'display:none' }}"
                    >{{ ($serverSpecialFormatted ?: '') . $unitSuffixText }}</span>

                    <span
                        class="special-price"
                        x-show="!(specialPrice < regularPrice)"
                        x-text="formatCurrency(regularPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')"
                        style="{{ $hasServerDiscount ? 'display:none' : '' }}"
                    >{{ ($serverRegularFormatted ?: '') . $unitSuffixText }}</span>
                </div>
            </div>
        @endif

        <template x-cloak x-if="isInStock">
            <div>
                <template x-if="doesManageStock">
                    <div
                        class="availability in-stock"
                    >
                        <span x-text="trans('storefront::product.left_in_stock', { count: new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(item.qty) + (product.unit_suffix ? ' ' + product.unit_suffix : '') })"></span>
                        <span x-cloak x-show="item.sku">
                            ({{ trans('storefront::product.sku') }} <span x-text="item.sku"></span>)
                        </span>
                    </div>
                </template>

                <template x-if="!doesManageStock">
                    <div class="availability in-stock">
                        {{ trans('storefront::product.in_stock') }}
                        <span x-cloak x-show="item.sku">
                            ({{ trans('storefront::product.sku') }} <span x-text="item.sku"></span>)
                        </span>
                    </div>
                </template>
            </div>
        </template>

        <template x-cloak x-if="!isInStock">
            <div class="availability out-of-stock">
                {{ trans('storefront::product.out_of_stock') }}
                <span x-cloak x-show="item.sku">
                    ({{ trans('storefront::product.sku') }} <span x-text="item.sku"></span>)
                </span>
            </div>
        </template>
        <div class="details-info-top-actions">
            @if (!empty($hasSizeChart) && !empty($sizeChartEndpoint))
                <button
                    type="button"
                    class="btn btn-link p-0 size-chart-trigger"
                    data-size-chart-url="{{ $sizeChartEndpoint }}"
                    data-size-chart-title="{{ trans('size_chart::storefront.size_chart') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                        <path d="M14.86,14.86a6.68,6.68,0,1,1-13.36,0A6.53,6.53,0,0,1,2.15,12a6.67,6.67,0,0,1,12.06,0A6.53,6.53,0,0,1,14.86,14.86Z" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91"/>
                        <path d="M11.05,14.86a2.87,2.87,0,1,1-5.73,0,2.77,2.77,0,0,1,.28-1.22,2.86,2.86,0,0,1,5.17,0A2.77,2.77,0,0,1,11.05,14.86Z" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91"/>
                        <path d="M22.5,2.45V8.18H8.18a6.66,6.66,0,0,0-6,3.82,6.53,6.53,0,0,0-.65,2.86V9.14a5,5,0,0,1,.08-1,6.66,6.66,0,0,1,6.6-5.73Z" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91"/>
                        <line x1="18.68" y1="2.45" x2="18.68" y2="6.27" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91"/>
                        <line x1="14.86" y1="2.45" x2="14.86" y2="5.32" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91"/>
                        <line x1="11.05" y1="2.45" x2="11.05" y2="6.27" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91"/>
                        <line x1="7.23" y1="2.45" x2="7.23" y2="5.32" stroke="currentColor" stroke-miterlimit="10" stroke-width="1.91"/>
                    </svg>
                    <span>{{ trans('size_chart::storefront.size_chart') }}</span>
                </button>
            @endif
        </div>

        @foreach (($productSectionsOrder ?? []) as $section)
            @if ($section === 'product_page_custom_text')
                @if (setting('storefront_product_page_custom_text_enabled') && setting('storefront_product_page_custom_text_content'))
                    <div class="product-page-custom-section product-page-custom-text">
                        {!! setting('storefront_product_page_custom_text_content') !!}
                    </div>
                @endif
            @elseif ($section === 'product_page_custom_html')
                @if (setting('storefront_product_page_custom_html_enabled') && setting('storefront_product_page_custom_html_content'))
                    <div class="product-page-custom-section product-page-custom-html">
                        {!! setting('storefront_product_page_custom_html_content') !!}
                    </div>
                @endif
            @elseif ($section === 'product_page_image_banner')
                @php($productPageImageBanner = \Modules\Storefront\Banner::findByName('storefront_product_page_image_banner'))
                @if (setting('storefront_product_page_image_banner_enabled') && !is_null($productPageImageBanner) && $productPageImageBanner->image->exists)
                    <div class="product-page-custom-section product-page-image-banner">
                        @if (!empty($productPageImageBanner->call_to_action_url))
                            <a
                                href="{{ $productPageImageBanner->call_to_action_url }}"
                                target="{{ $productPageImageBanner->open_in_new_window ? '_blank' : '_self' }}"
                                rel="{{ $productPageImageBanner->open_in_new_window ? 'noopener noreferrer' : '' }}"
                                class="d-block"
                            >
                                <img src="{{ $productPageImageBanner->image->path }}" alt="Banner" loading="lazy" class="img-fluid" />
                            </a>
                        @else
                            <img src="{{ $productPageImageBanner->image->path }}" alt="Banner" loading="lazy" class="img-fluid" />
                        @endif
                    </div>
                @endif
            @elseif ($section === 'product_page_info_icons')
                @if (setting('storefront_product_page_info_icons_enabled'))
                    @php(
                        $productPageInfoIconsItems = collect([1, 2, 3])->map(function ($number) {
                            return [
                                'image' => \Modules\Media\Entities\File::findOrNew(setting("storefront_product_page_info_icons_icon_{$number}_image")),
                                'title' => setting("storefront_product_page_info_icons_icon_{$number}_title"),
                                'text' => setting("storefront_product_page_info_icons_icon_{$number}_text"),
                            ];
                        })
                    )
                    <div class="product-page-info-icons">
                        @include('storefront::public.products.show.info_icons', [
                            'items' => $productPageInfoIconsItems,
                        ])
                    </div>
                @endif
            @endif
        @endforeach

        @if (!empty($hasSizeChart) && !empty($sizeChartEndpoint))
            <div
                class="modal fade"
                id="sizeChartModal"
                tabindex="-1"
            >
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <button type="button" class="size-chart-modal-close" data-bs-dismiss="modal" aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
                                <path d="M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div class="modal-body">
                            <div class="size-chart-modal-tabs" data-size-chart-tabs></div>
                            <div class="size-chart-modal-body" data-size-chart-modal-body></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="brief-description">
            {!! $product->short_description !!}
        </div>

        @include('storefront::public.partials.cart.upsell_box')

    <div class="details-info-middle" style="min-height: 260px;">
        <form
            @input="errors.clear($event.target.name)"
            @submit.prevent="addToCart"
        >
            @if ($product->variant)
                <div class="product-variants">
                    @include('storefront::public.products.show.variations')
                </div>
            @endif
            
            @if ($product->options->isNotEmpty())
                <div class="product-variants">
                    @foreach ($product->options as $option)
                        @includeIf("storefront::public.products.show.custom_options.{$option->type}")
                    @endforeach
                </div>
            @endif

            <div class="details-info-middle-actions">
                <template x-if="product.unit_decimal && isInStock">
                    <div class="decimal-quantity-card">
                        <div class="decimal-quantity-header">
                            <div>
                                <div class="decimal-quantity-title">
                                    <span x-text="product.unit_label || 'Uzunluk'"></span> (<span x-text="product.unit_suffix"></span>)
                                </div>
                                <div class="decimal-quantity-desc" x-text="product.unit_info_top"></div>
                            </div>
                        </div>

                        <div class="decimal-quantity-main">
                            <button
                                type="button"
                                class="btn-quantity minus"
                                :disabled="isQtyDecreaseDisabled"
                                @click="updateQuantity(cartItemForm.qty - stepQty)"
                            >
                                −
                            </button>

                            <div class="decimal-quantity-input">
                                <span
                                    class="input-overlay"
                                    x-text="(product.unit_decimal ? Number(cartItemForm.qty).toFixed(2).replace(/\.00$/, '') : cartItemForm.qty) + (product.unit_suffix ? product.unit_suffix : '')"
                                ></span>
                                <input
                                    x-ref="inputQuantity"
                                    type="text"
                                    inputmode="decimal"
                                    :value="isEditingQty ? qtyInput : cartItemForm.qty"
                                    autocomplete="off"
                                    :min="minQty"
                                    :max="maxQuantity"
                                    id="qty"
                                    aria-label="{{ trans('storefront::product.quantity') }}"
                                    class="form-control input-quantity-decimal input-overlay-target"
                                    :disabled="isAddToCartDisabled"
                                    @focus="beginEditQty($event)"
                                    @blur="commitEditQty()"
                                    @input="onQtyInput($event)"
                                    @keydown.up="updateQuantity(cartItemForm.qty + stepQty)"
                                    @keydown.down="updateQuantity(cartItemForm.qty - stepQty)"
                                >
                                <span class="input-suffix" x-text="product.unit_suffix"></span>
                            </div>

                            <button
                                type="button"
                                class="btn-quantity plus"
                                :disabled="isQtyIncreaseDisabled"
                                @click="updateQuantity(cartItemForm.qty + stepQty)"
                            >
                                +
                            </button>
                        </div>

                        <div class="decimal-quantity-chips">
                            <button type="button" class="chip" :disabled="doesManageStock && item.qty < 0.5" @click="updateQuantity(0.5)">0.5<span x-text="product.unit_suffix"></span></button>
                            <button type="button" class="chip" :disabled="doesManageStock && item.qty < 1" @click="updateQuantity(1)">1<span x-text="product.unit_suffix"></span></button>
                            <button type="button" class="chip" :disabled="doesManageStock && item.qty < 2.5" @click="updateQuantity(2.5)">2.5<span x-text="product.unit_suffix"></span></button>
                            <button type="button" class="chip chip--desktop-only" :disabled="doesManageStock && item.qty < 5" @click="updateQuantity(5)">5<span x-text="product.unit_suffix"></span></button>
                            <button type="button" class="chip" :disabled="doesManageStock && item.qty < 10" @click="updateQuantity(10)">10<span x-text="product.unit_suffix"></span></button>
                        </div>

                        <div class="decimal-quantity-info" x-text="product.unit_info_bottom"></div>
                    </div>
                </template>

                <template x-if="!product.unit_decimal && isInStock">
                    <div class="number-picker-lg">
                        <label for="qty">{{ trans('storefront::product.quantity') }}</label>

                        <div class="input-group-quantity">
                            <input
                                x-ref="inputQuantity"
                                type="text"
                                :value="cartItemForm.qty"
                                autocomplete="off"
                                :min="minQty"
                                :max="maxQuantity"
                                id="qty"
                                class="form-control input-number input-quantity"
                                :disabled="isAddToCartDisabled"
                                @focus="$event.target.select()"
                                @input="updateQuantity(Number($event.target.value))"
                                @keydown.up="updateQuantity(cartItemForm.qty + stepQty)"
                                @keydown.down="updateQuantity(cartItemForm.qty - stepQty)"
                            >

                            <span class="btn-wrapper">
                                <button
                                    type="button"
                                    aria-label="quantity"
                                    class="btn btn-number btn-plus"
                                    :disabled="isQtyIncreaseDisabled"
                                    @click="updateQuantity(cartItemForm.qty + stepQty)"
                                >
                                    +
                                </button>

                                <button
                                    type="button"
                                    aria-label="quantity"
                                    class="btn btn-number btn-minus"
                                    :disabled="isQtyDecreaseDisabled"
                                    @click="updateQuantity(cartItemForm.qty - stepQty)"
                                >
                                    -
                                </button>
                            </span>
                        </div>
                    </div>
                </template>

                <template x-if="isInStock">
                    <button
                        type="submit"
                        class="btn btn-primary btn-add-to-cart"
                        :class="{'btn-loading': addingToCart }"
                        :disabled="isAddToCartDisabled"
                        x-text="isActiveItem ? '{{ trans('storefront::product.add_to_cart') }}' : '{{ trans('storefront::product.unavailable') }}'"
                    >
                        {{ trans($item->is_active ? 'storefront::product.add_to_cart' : 'storefront::product.unavailable') }}
                    </button>
                </template>

                <template x-if="!isInStock">
                    <div x-data="{ open: true, email: '{{ auth()->check() ? auth()->user()->email : '' }}', submitting: false, notice: '', noticeType: '' }">
                        <div class="stock-notify-container" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                            <h4 style="font-size: 15px; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                Bu Ürün Şu An Stokta Yok
                            </h4>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">E-posta adresinizi bırakın, ürün tekrar stoklara girdiğinde size haber verelim.</p>
                            
                            <div x-show="!isRequested(item.id)" x-cloak>
                                <div class="input-group">
                                    <input
                                        type="email"
                                        name="stock_notify_email"
                                        class="form-control"
                                        required
                                        x-model="email"
                                        placeholder="E-posta adresiniz"
                                        :disabled="submitting"
                                        style="height: 48px; border-radius: 8px 0 0 8px;"
                                    >

                                    <button
                                        type="button"
                                        class="btn btn-warning"
                                        :disabled="submitting"
                                        @click="if (isRequested(item.id)) return; submitting = true; notice = ''; noticeType = ''; const params = new URLSearchParams({ product_id: '{{ $product->id }}', email }); if (hasAnyVariant && isActiveItem && item && item.id && item.id != {{ $product->id }}) { params.append('variant_id', item.id); } fetch('{{ route('stock.notify') }}', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }, body: params }).then(async (res) => { let data = null; try { data = await res.json(); } catch (e) {} if (res.ok) { noticeType = 'success'; notice = (data && data.message) ? data.message : 'Talebiniz alındı.'; requestedStockNotifications.push(item.id); } else { noticeType = 'error'; notice = (data && data.message) ? data.message : 'İşlem başarısız.'; } submitting = false; }).catch(() => { noticeType = 'error'; notice = 'İşlem başarısız.'; submitting = false; });"
                                        style="background-color: #f59e0b; border-color: #f59e0b; color: #fff; font-weight: 600; padding: 0 25px; height: 48px; border-radius: 0 8px 8px 0;"
                                    >
                                        <span x-show="!submitting">Haber Ver</span>
                                        <span x-show="submitting" class="spinner-border spinner-border-sm"></span>
                                    </button>
                                </div>

                                <div class="mt-2" x-show="notice && noticeType === 'error'" x-cloak>
                                    <div class="alert alert-danger" style="font-size: 13px; padding: 8px 12px;" x-text="notice"></div>
                                </div>
                            </div>

                            <div x-show="isRequested(item.id)" x-cloak>
                                <div class="alert alert-success" style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; font-size: 14px; border-radius: 8px; margin: 0; display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span x-text="notice || 'Talebiniz alındı. Ürün stok açıldığında size haber vereceğiz.'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                @if (setting('phone_number') && setting('product_button_enabled'))
                    <a
                        class="btn btn-default"
                        href="#"
                        @click.prevent="(() => {
                            const phone = '{{ setting('phone_number') }}';
                            const name = productName;
                            const variant = (item && item.name) ? item.name : '';
                            const qty = cartItemForm.qty;
                            const unit = product.unit_suffix ? product.unit_suffix : '';
                            const url = window.location.href;
                            const template = `{{ str_replace(['\n',"\r\n"],'\\n', setting('product_message_template')) }}` || '';
                            const msg = template
                                .replace('{product_name}', name)
                                .replace('{variant_name}', variant)
                                .replace('{quantity}', qty)
                                .replace('{unit}', unit)
                                .replace('{product_url}', url);
                            const link = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`;
                            window.open(link, '_blank');
                        })()"
                    >
                        {{ setting('product_button_text', 'WhatsApp’tan Sor') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="details-info-bottom" style="min-height: 80px;">
        <ul class="list-inline additional-info">
            @if ($product->categories->isNotEmpty())
                <li>
                    <span>{{ trans('storefront::product.categories') }}</span>

                    @foreach ($product->categories as $category)
                        <a href="{{ $category->url() }}">{{ $category->name }}</a>{{ $loop->last ? '' : ',' }}
                    @endforeach
                </li>
            @endif

            @if ($product->tags->isNotEmpty())
                <li>
                    <span>{{ trans('storefront::product.tags') }}</span>

                    @foreach ($product->tags as $tag)
                        <a href="{{ $tag->url() }}">{{ $tag->name }}</a>{{ $loop->last ? '' : ',' }}
                    @endforeach
                </li>
            @endif
        </ul>

        @include('storefront::public.products.show.social_share')
    </div>
</div>
