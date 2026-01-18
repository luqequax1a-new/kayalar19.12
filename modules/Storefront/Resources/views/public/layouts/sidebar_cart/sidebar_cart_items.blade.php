<template x-for="cartItem in $store.cart.items" :key="cartItem.id">
    <div x-data="CartItem(cartItem)" class="cart-item sidebar-cart-item">
        <a :href="productUrl" class="product-image">
            <img
                :src="baseImage"
                :class="{
                    'image-placeholder': !hasBaseImage,
                }"
                :alt="productName"
                loading="lazy"
            />
        </a>

        <div class="product-info">
            <template x-if="cartItem.upsell && typeof cartItem.upsell === 'object' && Object.keys(cartItem.upsell).length > 0 && cartItem.upsell.is_upsell">
                <div style="margin-bottom: 2px;">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold" style="background-color:#fef3c7;color:#92400e; font-size: 10px; border-radius: 4px;">
                        Sepet Teklifi
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

            <div class="product-info-bottom">
                <template x-if="cartItem.upsell && typeof cartItem.upsell === 'object' && Object.keys(cartItem.upsell).length > 0 && cartItem.upsell.is_upsell">
                    <div style="margin-top: 5px; color: #10b981; font-weight: 800; font-size: 0.9375rem;">
                        <template x-if="cartItem.upsell.original_price">
                            <span style="color: #94a3b8; font-size: 0.8125rem; text-decoration: line-through; margin-right: 6px;" x-text="formatCurrency(cartItem.upsell.original_price).replace(/,00$/, '')"></span>
                        </template>
                        <span x-text="`${formatCurrency(unitPrice).replace(/,00$/, '')} x ${Number(cartItem.qty).toString()}${cartItem.product.unit_suffix ? ' ' + cartItem.product.unit_suffix : ''} = ${formatCurrency(lineTotal(cartItem.qty)).replace(/,00$/, '')}`"></span>
                    </div>
                </template>

                <template x-if="!(cartItem.upsell && typeof cartItem.upsell === 'object' && Object.keys(cartItem.upsell).length > 0 && cartItem.upsell.is_upsell)">
                    <div class="line-summary" x-text="`${Number(cartItem.qty).toString()}${cartItem.product.unit_suffix ? ' ' + cartItem.product.unit_suffix : ''} x ${formatCurrency(unitPrice).replace(/,00$/, '').replace(/^₺/, '₺ ')} = ${formatCurrency(lineTotal(cartItem.qty)).replace(/,00$/, '').replace(/^₺/, '₺ ')}`"></div>
                </template>
            </div>
        </div>

        <div class="remove-cart-item">
            <button class="btn-remove" @click="removeCartItem">
                <i class="las la-times"></i>
            </button>
        </div>
    </div>
</template>
