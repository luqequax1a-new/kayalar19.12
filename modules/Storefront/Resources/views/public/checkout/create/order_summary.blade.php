<aside class="order-summary-wrap">
    <div class="order-summary">
        <div class="order-summary-top">
            <h3 class="section-title">{{ trans('storefront::checkout.order_summary') }}</h3>

            @include('storefront::public.checkout.create.cart_items_skeleton')

            <template x-if="cartFetched">
                <ul class="cart-items list-inline">
                    <template x-for="cartItem in cart.items" :key="cartItem.id">
                        <li x-data="CartItem(cartItem)" class="cart-item">
                            <a :href="productUrl" class="product-image">
                                <img
                                    :src="baseImage"
                                    :class="{
                                        'image-placeholder': !hasBaseImage,
                                    }"
                                    :alt="productName"
                                    loading="lazy"
                                />

                                <span class="qty-count" x-text="Number(cartItem.qty).toString()"></span>
                            </a>

                            <div class="product-info">
                                <template x-if="cartItem.upsell && typeof cartItem.upsell === 'object' && Object.keys(cartItem.upsell).length > 0 && cartItem.upsell.is_upsell">
                                    <div style="margin-bottom: 2px;">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold" style="background-color:#fef3c7;color:#92400e; font-size: 10px; border-radius: 4px;">
                                            🎁 Sepet Teklifi
                                        </span>
                                    </div>
                                </template>

                                <a
                                    :href="productUrl"
                                    class="product-name"
                                    :title="productName"
                                    x-text="productName"
                                >
                                </a>
                                
                                <template x-if="hasAnyVariation">
                                    <ul class="list-inline product-options">
                                        <template
                                            x-for="(variation, key) in cartItem.variations"
                                            :key="variation.id"
                                        >
                                            <li>
                                                <label x-text="`${variation.name}:`"></label>
                                                
                                                <span x-text="`${variation.values[0].label}${variationsLength === Number(key) ? '' : ','}`"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </template>

                                <template x-if="hasAnyOption">
                                    <ul class="list-inline product-options">
                                        <template
                                            x-for="(option, key) in cartItem.options"
                                            :key="option.id"
                                        >
                                            <li>
                                                <label x-text="`${option.name}:`"></label>
                                                
                                                <span x-text="`${optionValues(option)}${optionsLength === Number(key) ? '' : ','}`"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                            </div>
                            
                            <div class="product-price">
                                <template x-if="cartItem.upsell && typeof cartItem.upsell === 'object' && Object.keys(cartItem.upsell).length > 0 && cartItem.upsell.is_upsell && cartItem.upsell.original_price">
                                    <span style="color: #94a3b8; font-size: 11px; text-decoration: line-through; margin-right: 4px;" x-text="formatCurrency(cartItem.upsell.original_price * cartItem.qty).replace(/,00$/, '')"></span>
                                </template>
                                <span :style="cartItem.upsell && typeof cartItem.upsell === 'object' && Object.keys(cartItem.upsell).length > 0 && cartItem.upsell.is_upsell ? 'color: #10b981; font-weight: 700;' : ''" x-text="formatCurrency(lineTotal(cartItem.qty)).replace(/,00$/, '').replace(/^₺/, '₺ ')"></span>
                            </div>
                        </li>
                    </template>
                </ul>
            </template>

            @include('storefront::public.checkout.create.coupon')
        </div>

        <div class="order-summary-middle">
            <ul class="list-inline order-summary-list order-summary-list-skeleton">
                <li>
                    <label class="skeleton"></label>

                    <span class="skeleton"></span>
                </li>

                <li>
                    <label class="skeleton"></label>

                    <span class="skeleton"></span>
                </li>
            </ul>

            <template x-if="cartFetched">
                <ul class="list-inline order-summary-list">
                    <li>
                        <label>{{ trans('storefront::checkout.subtotal') }}</label>

                        <span x-text="formatCurrency($store.cart.subTotal).replace(/,00$/, '').replace(/^₺/, '₺ ')"></span>
                    </li>

                    <template x-for="(tax, index) in cart.taxes" :key="index">
                        <li>
                            <label x-text="tax.name"></label>

                            <span x-text="formatCurrency(tax.amount.inCurrentCurrency.amount).replace(/,00$/, '').replace(/^₺/, '₺ ')"></span>
                        </li>
                    </template>

                    <template x-if="$store.cart.hasCoupon">
                        <li>
                            <label>
                                {{ trans('storefront::checkout.coupon') }}

                                <span class="coupon-code">
                                    (<span x-text="cart.coupon.code"></span>)
                                    
                                    <span class="btn-remove-coupon" @click="removeCoupon">
                                        <i class="las la-times"></i>
                                    </span>
                                </span>
                            </label>

                            <span class="color-primary" x-text="`-${formatCurrency($store.cart.couponValue).replace(/,00$/, '').replace(/^₺/, '₺ ')}`"></span>
                        </li>
                    </template>

                    <template x-if="hasShippingMethod">
                        <li>
                            <label>
                                {{ trans('storefront::checkout.shipping_cost') }}
                            </label>

                            <span
                                :class="{ 'color-primary': hasFreeShipping }"
                                x-text="
                                    (hasFreeShipping || $store.cart.shippingCost === 0) ?
                                    '{{ trans('storefront::checkout.free') }}' :
                                    formatCurrency($store.cart.shippingCost).replace(/,00$/, '').replace(/^₺/, '₺ ')
                                "
                            >
                            </span>
                        </li>
                    </template>

                    @if (setting('cod_fee_display_mode') === 'separate_line')
                        <template x-if="$store.cart.codFee > 0">
                            <li>
                                <label>{{ trans('storefront::checkout.cod_fee') }}</label>

                                <span x-text="formatCurrency($store.cart.codFee).replace(/,00$/, '').replace(/^₺/, '₺ ')"></span>
                            </li>
                        </template>
                    @endif
                </ul>
            </template>

            @include('storefront::public.checkout.create.order_summary_total_skeleton')

            <template x-if="cartFetched">
                <div class="order-summary-total">
                    <label>{{ trans('storefront::checkout.total') }}</label>

                    <span x-text="formatCurrency($store.cart.total).replace(/,00$/, '').replace(/^₺/, '₺ ')"></span>
                </div>
            </template>
        </div>

        <div class="order-summary-bottom">
            <div class="form-group checkout-terms-and-conditions">
                <div class="form-check">
                    <input type="checkbox" x-model="form.terms_and_conditions" id="terms-and-conditions">

                    <label for="terms-and-conditions" class="form-check-label">
                        {{ trans('storefront::checkout.i_agree_to_the') }}

                        <a href="{{ $termsPageURL }}">
                            {{ trans('storefront::checkout.terms_&_conditions') }}
                        </a>
                    </label>

                    <template x-if="errors.has('terms_and_conditions')">
                        <span class="error-message" x-text="errors.get('terms_and_conditions')"></span>
                    </template>
                </div>
            </div>

            <template x-if="form.payment_method === 'paypal'">
                <div id="paypal-button-container"></div>
            </template>

            <template x-if="form.payment_method !== 'paypal'">
                <button
                    x-cloak
                    type="button"
                    class="btn btn-primary btn-place-order"
                    :class="{ 'btn-loading': placingOrder }"
                    :disabled="!form.terms_and_conditions"
                    id="place-order-button"
                    @click="placeOrder"
                >
                    {{ trans('storefront::checkout.place_order') }}
                </button>
            </template>

            @if (setting('phone_number') && setting('cart_button_enabled'))
                @php
                    $items = \Modules\Cart\Facades\Cart::items();
                    $lines = $items->map(function($ci){
                        $name = optional($ci->product)->name ?: '';
                        $attrs = [];
                        try {
                            foreach (($ci->variations ?? []) as $variation) {
                                $label = optional($variation->values->first())->label;
                                if ($label) { $attrs[] = $variation->name . ': ' . $label; }
                            }
                        } catch (\Throwable $e) {}
                        try {
                            foreach (($ci->options ?? []) as $option) {
                                $val = null;
                                if (method_exists($option, 'isFieldType') && $option->isFieldType()) {
                                    $val = $option->value;
                                } elseif (isset($option->values)) {
                                    $val = $option->values->implode('label', ', ');
                                }
                                if ($val) { $attrs[] = $option->name . ': ' . $val; }
                            }
                        } catch (\Throwable $e) {}
                        $attrStr = empty($attrs) ? '' : (' (' . implode(', ', $attrs) . ')');
                        $qty = $ci->qty;
                        $unit = optional($ci->product)->unit_suffix ? (' ' . $ci->product->unit_suffix) : '';
                        return $name . $attrStr . ' x ' . $qty . $unit;
                    })->implode("\n");
                    $total = \Modules\Cart\Facades\Cart::subTotal()->format();
                    $restoreUrl = route('cart.index');
                    $template = setting('cart_message_template');
                    $msg = str_replace(['{cart_lines}','{cart_total}','{cart_restore_url}'], [$lines, $total, $restoreUrl], $template);
                    $waUrl = 'https://api.whatsapp.com/send?phone=' . setting('phone_number') . '&text=' . urlencode($msg);
                @endphp
                <a class="btn btn-default whatsapp-cart-btn" href="{{ $waUrl }}" target="_blank" rel="noopener">
                    {{ setting('cart_button_text', 'Sepetini WhatsApp’tan Gönder') }}
                </a>
            @endif
        </div>
    </div>
</aside>
