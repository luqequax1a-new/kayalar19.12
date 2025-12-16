<div class="product-details-info position-relative flex-grow-1"> 
    <div class="details-info-top" style="min-height: 150px;">
        <h1 class="product-name" x-text="productName"></h1>

        @if (setting('reviews_enabled'))
            <div x-show="reviewCount > 0">
                @include('storefront::public.partials.product_rating', ['data' => 'product'])
            </div>
        @endif

        @if ($product->variant)
            <template x-if="isActiveItem">
                <div class="product-price">
                    <template x-if="hasSpecialPrice">
                        <span class="special-price" x-text="formatCurrency(specialPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')"></span>
                    </template>

                    <span class="previous-price" x-text="formatCurrency(regularPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')">
                        {!! $item->is_active ? $item->hasSpecialPrice() ? $item->special_price->format() : $item->price->format() : '' !!}
                    </span>
                </div>
            </template>
        @else
            <div class="product-price">
                <template x-if="hasSpecialPrice">
                    <span class="special-price" x-text="formatCurrency(specialPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')"></span>
                </template>

                <span class="previous-price" x-text="formatCurrency(regularPrice) + (product.unit_suffix ? ' /' + product.unit_suffix : '')">
                    {{ $item->hasSpecialPrice() ? $item->special_price->format() : $item->price->format() }}
                </span>
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
            <button
                class="btn btn-wishlist"
                :class="{ 'added': inWishlist }"
                @click="syncWishlist"
            >
                <template x-if="inWishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M16.44 3.1001C14.63 3.1001 13.01 3.9801 12 5.3301C10.99 3.9801 9.37 3.1001 7.56 3.1001C4.49 3.1001 2 5.6001 2 8.6901C2 9.8801 2.19 10.9801 2.52 12.0001C4.1 17.0001 8.97 19.9901 11.38 20.8101C11.72 20.9301 12.28 20.9301 12.62 20.8101C15.03 19.9901 19.9 17.0001 21.48 12.0001C21.81 10.9801 22 9.8801 22 8.6901C22 5.6001 19.51 3.1001 16.44 3.1001Z" fill="#292D32"/>
                    </svg>
                </template>
                
                <template x-if="!inWishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12.62 20.81C12.28 20.93 11.72 20.93 11.38 20.81C8.48 19.82 2 15.69 2 8.68998C2 5.59998 4.49 3.09998 7.56 3.09998C9.38 3.09998 10.99 3.97998 12 5.33998C13.01 3.97998 14.63 3.09998 16.44 3.09998C19.51 3.09998 22 5.59998 22 8.68998C22 15.69 15.52 19.82 12.62 20.81Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </template>

                {{ trans('storefront::product.wishlist') }}
            </button>

            <button
                class="btn btn-compare"
                :class="{ 'added': inCompareList }"
                @click="syncCompareList"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M3.58008 5.15991H17.4201C19.0801 5.15991 20.4201 6.49991 20.4201 8.15991V11.4799" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M6.74008 2L3.58008 5.15997L6.74008 8.32001" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M20.4201 18.84H6.58008C4.92008 18.84 3.58008 17.5 3.58008 15.84V12.52" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M17.26 21.9999L20.42 18.84L17.26 15.6799" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                
                {{ trans('storefront::product.compare') }}
            </button>
        </div>

        @if (!empty($hasSizeChart) && !empty($sizeChartEndpoint))
            <div class="product-size-chart">
                <button
                    type="button"
                    class="btn btn-link p-0 size-chart-trigger"
                    data-size-chart-url="{{ $sizeChartEndpoint }}"
                    data-size-chart-title="{{ trans('size_chart::storefront.size_chart') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M4 7H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M4 12H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M4 17H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M7 4V20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M17 4V20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span>{{ trans('size_chart::storefront.size_chart') }}</span>
                </button>
            </div>

            <div
                class="modal fade"
                id="sizeChartModal"
                tabindex="-1"
                aria-hidden="true"
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
                <template x-if="product.unit_decimal">
                    <div class="decimal-quantity-card">
                        <div class="decimal-quantity-header">
                            <div>
                                <div class="decimal-quantity-title">
                                    Uzunluk (<span x-text="product.unit_suffix"></span>)
                                </div>
                                <div class="decimal-quantity-desc" x-text="product.unit_info_top || 'Bu ürün metre bazında satılır. İstediğiniz ölçüyü girerek sepete ekleyebilirsiniz.'"></div>
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

                        <div class="decimal-quantity-info" x-text="product.unit_info_bottom || `Minimum kesim: ${new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(product.unit_min)} ${product.unit_suffix}`"></div>
                    </div>
                </template>

                <template x-if="!product.unit_decimal">
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
                    <div x-data="{ open: false, requested: false, email: '{{ auth()->check() ? auth()->user()->email : '' }}', submitting: false, notice: '', noticeType: '' }">
                        <button
                            type="button"
                            class="btn btn-primary btn-add-to-cart"
                            :disabled="requested"
                            @click="if (!requested) { open = !open }"
                        >
                            <span x-show="!requested">Stoğa geldiğinde haber ver</span>
                            <span x-show="requested" x-cloak>Talebiniz alındı</span>
                        </button>

                        <div class="mt-3" x-show="open && !requested" x-cloak>
                            <div class="input-group">
                                <input
                                    type="email"
                                    name="stock_notify_email"
                                    class="form-control"
                                    required
                                    x-model="email"
                                    placeholder="E-posta adresiniz"
                                    :disabled="submitting"
                                >

                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    :disabled="submitting"
                                    @click="if (requested) return; submitting = true; notice = ''; noticeType = ''; const params = new URLSearchParams({ product_id: '{{ $product->id }}', email }); if (product.variant && isActiveItem && item && item.id) { params.append('variant_id', item.id); } fetch('{{ route('stock.notify') }}', { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }, body: params }).then(async (res) => { let data = null; try { data = await res.json(); } catch (e) {} if (res.ok) { noticeType = 'success'; notice = (data && data.message) ? data.message : 'Talebiniz alındı.'; requested = true; open = false; } else { noticeType = 'error'; notice = (data && data.message) ? data.message : 'İşlem başarısız.'; } submitting = false; }).catch(() => { noticeType = 'error'; notice = 'İşlem başarısız.'; submitting = false; });"
                                >
                                    Gönder
                                </button>
                            </div>

                            <div class="mt-2" x-show="notice && noticeType === 'error'" x-cloak>
                                <div class="alert alert-danger" x-text="notice"></div>
                            </div>
                        </div>

                        <div class="mt-3" x-show="requested" x-cloak>
                            <div class="alert alert-success" x-text="notice || 'Talebiniz alındı. Ürün tekrar stok açıldığında size haber vereceğiz.'"></div>
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
                    <label>{{ trans('storefront::product.categories') }}</label>

                    @foreach ($product->categories as $category)
                        <a href="{{ $category->url() }}">{{ $category->name }}</a>{{ $loop->last ? '' : ',' }}
                    @endforeach
                </li>
            @endif

            @if ($product->tags->isNotEmpty())
                <li>
                    <label>{{ trans('storefront::product.tags') }}</label>

                    @foreach ($product->tags as $tag)
                        <a href="{{ $tag->url() }}">{{ $tag->name }}</a>{{ $loop->last ? '' : ',' }}
                    @endforeach
                </li>
            @endif
        </ul>

        @include('storefront::public.products.show.social_share')
    </div>
</div>
