Alpine.data("CartItem", (cartItem) => ({
    controller: null,
    product: cartItem.product,
    item: cartItem.variant || cartItem.product,
    qty: cartItem.qty,

    get minQty() {
        return this.product.unit_min || 1;
    },

    get stepQty() {
        return this.product.unit_step || 0.5;
    },

    get productName() {
        return this.product.name;
    },

    get productUrl() {
        let url = `/products/${this.product.slug}`;

        if (this.hasAnyVariant) {
            url += `?variant=${this.item.uid}`;
        }

        return url;
    },

    get unitPrice() {
        return cartItem.unitPrice.inCurrentCurrency.amount;
    },

    get hasAnyVariation() {
        return Object.keys(cartItem.variations).length !== 0;
    },

    get variationsLength() {
        return Object.keys(cartItem.variations).length;
    },

    get hasAnyOption() {
        return Object.keys(cartItem.options).length !== 0;
    },

    get optionsLength() {
        return Object.keys(cartItem.options).length;
    },

    get hasAnyVariant() {
        return this.product.variant !== null;
    },

    get hasAnyMedia() {
        return this.item.media.length !== 0;
    },

    get hasBaseImage() {
        if (this.hasAnyVariant) {
            return this.item.base_image.length !== 0 ||
                this.product.base_image.length !== 0
                ? true
                : false;
        }

        return this.item.base_image.length !== 0;
    },

    get baseImage() {
        if (!this.hasBaseImage) {
            return `${window.location.origin}/build/assets/image-placeholder.png`;
        }

        const img = this.item.base_image.path
            ? this.item.base_image
            : this.product.base_image;

        return (
            img.thumb_webp_url ||
            img.thumb_jpeg_url ||
            img.card_webp_url ||
            img.card_jpeg_url ||
            img.path
        );
    },


    isQtyIncreaseDisabled(cartItem) {
        return (
            this.maxQuantity(cartItem) !== null &&
            cartItem.qty >= cartItem.item.qty
        );
    },

    lineTotal(qty) {
        return qty * cartItem.unitPrice.inCurrentCurrency.amount;
    },

    optionValues(option) {
        let values = [];

        for (let value of option.values) {
            values.push(value.label);
        }

        return values.join(", ");
    },

    maxQuantity({ item }) {
        return item.is_in_stock && item.does_manage_stock ? item.qty : null;
    },

    exceedsMaxStock({ item, qty }) {
        return item.does_manage_stock && item.qty < qty;
    },

    normalizeQty(value) {
        let v = Number((value + '').replace(',', '.')) || 0;
        const min = this.minQty;
        const step = this.stepQty;

        if (v < min) v = min;

        const steps = Math.round((v - min) / step);
        v = min + steps * step;

        if (this.product.unit_decimal) {
            return Number(v.toFixed(2));
        }

        return Math.round(v);
    },

    changeQuantity(cartItem, qty) {
        let v = this.normalizeQty(qty);

        cartItem.qty = v;

        if (this.exceedsMaxStock(cartItem)) {
            v = cartItem.item.qty;
            this.updateCart(cartItem, v);
            return;
        }

        this.updateCart(cartItem, v);
    },

    updateQuantity(cartItem, qty) {
        let v = this.normalizeQty(qty);

        cartItem.qty = v;

        if (this.exceedsMaxStock(cartItem)) {
            cartItem.qty = cartItem.item.qty;
            this.updateCart(cartItem, cartItem.qty);
            return;
        }

        this.updateCart(cartItem, v);
    },

    async updateCart(cartItem, qty) {
        if (this.controller) {
            this.controller.abort();
        }

        this.controller = new AbortController();

        try {
            const { data } = await axios.put(
                `/cart/items/${cartItem.id}`,
                {
                    qty: qty || 1,
                },
                {
                    signal: this.controller.signal,
                }
            );

            this.qty = data.items[cartItem.id].qty;
            this.$store.cart.updateCart(data);
        } catch (error) {
            if (error.code !== "ERR_CANCELED") {
                // revert cart item quantity on error
                this.$store.cart.updateCartItemQty({
                    id: cartItem.id,
                    qty: this.qty,
                });

                notify(trans("storefront::storefront.something_went_wrong"));
            }
        }
    },

    removeCartItem() {
        this.$store.cart.removeCartItem(cartItem.id);

        axios.delete(`/cart/items/${cartItem.id}`).then((response) => {
            this.$store.cart.updateCart(response.data);
        });
    },
}));
