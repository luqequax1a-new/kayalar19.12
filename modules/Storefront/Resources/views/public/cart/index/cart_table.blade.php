<div class="table-responsive">
    <table class="table table-borderless cart-table">
        <thead>
            <tr>
                <th>{{ trans('storefront::cart.table.image') }}</th>
                <th>{{ trans('storefront::cart.table.product_name') }}</th>
                <th>{{ trans('storefront::cart.table.unit_price') }}</th>
                <th>{{ trans('storefront::cart.table.quantity') }}</th>
                <th>{{ trans('storefront::cart.table.line_total') }}</th>
                <th>
                    <button class="btn-remove" @click="clearCart">
                        <svg xmlns="http://www.w3.org/2000/svg" stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" width="1em" height="1em" style="color:#000"><path d="M15 2H9c-1.103 0-2 .897-2 2v2H3v2h2v12c0 1.103.897 2 2 2h10c1.103 0 2-.897 2-2V8h2V6h-4V4c0-1.103-.897-2-2-2zM9 4h6v2H9V4zm8 16H7V8h10v12z"></path></svg>
                    </button>
                </th>
            </tr>
        </thead>

        <tbody>
            <template x-for="cartItem in $store.cart.items" :key="cartItem.id">
                <tr x-data="CartItem(cartItem)">
                    <td>
                        <a :href="productUrl" class="product-image">
                            <img
                                :src="baseImage"
                                :class="{ 'image-placeholder': !hasBaseImage }"
                                :alt="productName"
                            />
                        </a>
                    </td>
                    <td>
                        <template x-if="cartItem.upsell && cartItem.upsell.is_upsell">
                            <div class="mb-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold" style="background-color:#fef3c7;color:#92400e; font-size: 11px; border-radius: 4px;">
                                    {{ trans('storefront::upsell.offer_badge') }}
                                </span>
                            </div>
                        </template>

                        <a
                            :href="productUrl"
                            class="product-name"
                            x-text="productName"
                        >
                        </a>

                        <template x-cloak x-if="hasAnyVariation">
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

                        <template x-cloak x-if="hasAnyOption">
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
                    </td>
                    <td>
                        <label>{{ trans('storefront::cart.table.unit_price') }}:</label>

                        <span class="product-price" x-text="formatCurrency(unitPrice).replace(/,00$/, '').replace(/^₺/, '₺ ')"></span>
                    </td>
                    <td>
                        <label>{{ trans('storefront::cart.table.quantity') }}:</label>

                        <span class="cart-qty" x-text="`${Number(cartItem.qty).toString()}${product.unit_suffix ? ' ' + product.unit_suffix : ''}`"></span>
                    </td>
                    <td>
                        <label>{{ trans('storefront::cart.table.line_total') }}:</label>

                        <span class="product-price" x-text="formatCurrency(lineTotal(cartItem.qty)).replace(/,00$/, '').replace(/^₺/, '₺ ')"></span>
                    </td>
                    <td>
                        <button class="btn-remove" @click="removeCartItem">
                            <svg xmlns="http://www.w3.org/2000/svg" stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" width="1em" height="1em" style="color:#000"><path d="M15 2H9c-1.103 0-2 .897-2 2v2H3v2h2v12c0 1.103.897 2 2 2h10c1.103 0 2-.897 2-2V8h2V6h-4V4c0-1.103-.897-2-2-2zM9 4h6v2H9V4zm8 16H7V8h10v12z"></path></svg>
                        </button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

<div class="cart-mobile-list">
    <template x-for="cartItem in $store.cart.items" :key="cartItem.id">
        <div x-data="CartItem(cartItem)" class="cart-mobile-item">
            <button class="btn-remove-item" type="button" @click="removeCartItem">
                <svg xmlns="http://www.w3.org/2000/svg" stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" width="1em" height="1em" style="color:#000"><path d="M15 2H9c-1.103 0-2 .897-2 2v2H3v2h2v12c0 1.103.897 2 2 2h10c1.103 0 2-.897 2-2V8h2V6h-4V4c0-1.103-.897-2-2-2zM9 4h6v2H9V4zm8 16H7V8h10v12z"></path></svg>
            </button>

            <div class="cart-mobile-top">
                <a :href="productUrl" class="cart-mobile-thumb">
                    <img
                        :src="baseImage"
                        :class="{ 'image-placeholder': !hasBaseImage }"
                        :alt="productName"
                    />
                </a>

                <div class="cart-mobile-info">
                    <template x-if="cartItem.upsell && cartItem.upsell.is_upsell">
                        <div style="margin-bottom: 2px;">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold" style="background-color:#fef3c7;color:#92400e; font-size: 10px; border-radius: 4px;">
                                {{ trans('storefront::upsell.offer_badge') }}
                            </span>
                        </div>
                    </template>

                    <a :href="productUrl" class="cart-mobile-name" x-text="productName"></a>

                    <template x-cloak x-if="hasAnyVariation">
                        <ul class="cart-mobile-variations">
                            <template x-for="(variation, key) in cartItem.variations" :key="variation.id">
                                <li>
                                    <span class="cart-option-label" x-text="`${variation.name}:`"></span>
                                    <span class="cart-option-value" x-text="`${variation.values[0].label}${variationsLength === Number(key) ? '' : ','}`"></span>
                                </li>
                            </template>
                        </ul>
                    </template>

                    <template x-cloak x-if="hasAnyOption">
                        <ul class="cart-mobile-variations">
                            <template x-for="(option, key) in cartItem.options" :key="option.id">
                                <li>
                                    <span class="cart-option-label" x-text="`${option.name}:`"></span>
                                    <span class="cart-option-value" x-text="`${optionValues(option)}${optionsLength === Number(key) ? '' : ','}`"></span>
                                </li>
                            </template>
                        </ul>
                    </template>

                    <div class="cart-mobile-summary">
                        <span class="cart-summary" x-text="`${Number(cartItem.qty).toString()}${product.unit_suffix ? ' ' + product.unit_suffix : ''} x ${formatCurrency(unitPrice).replace(/,00$/, '').replace(/^₺/, '₺ ')} = ${formatCurrency(lineTotal(cartItem.qty)).replace(/,00$/, '').replace(/^₺/, '₺ ')}`"></span>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<style>
@media (max-width: 768px) {
    .cart-mobile-item { position: relative; }
    .cart-mobile-name { padding-right: 10px; font-size: .75rem; font-weight: 600; color: #000; margin-top: 5px; }
    .cart-mobile-top { display: grid; grid-template-columns: 75px 1fr; gap: 8px 12px; }
    .cart-mobile-thumb { width: 75px; height: 75px; }
    .btn-remove-item {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 22px;
        height: 22px;
        border-radius: 999px;
        background: transparent;
        color: #111827;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    .btn-remove-item i { font-size: 14px; line-height: 1; }
    .btn-remove-item:hover, .btn-remove-item:focus { background: transparent; color: #000; border: none; }

    .cart-mobile-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 10px;
    }

    .cart-mobile-summary { margin-top: 0; }
    .cart-mobile-summary .cart-summary { color: #111827; font-weight: 500; font-size: .75rem; }

    .cart-mobile-variations { padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: 4px 12px; font-size: .8rem; color: #000; justify-content: flex-start; }
}
</style>
