Alpine.store("cart", {
    cart: {
        items: {},
        availableShippingMethods: {},
        coupon: {},
        quantity: 0,
        shippingCost: {},
        shippingMethodName: null,
        subTotal: {},
        codFee: {},
        taxes: [],
        total: [],
    },
    loading: false,
    fetching: false,
    fetched: false,

    get items() {
        return this.cart.items;
    },

    get quantity() {
        return this.fetched
            ? Object.values(this.items).length
            : FleetCart.cartQuantity;
    },

    get isEmpty() {
        if (!this.cart.items) return true;
        return Object.keys(this.cart.items).length === 0;
    },


    get shippingCost() {
        return this.cart.shippingCost?.inCurrentCurrency?.amount || 0;
    },

    get codFee() {
        return this.cart.codFee?.inCurrentCurrency?.amount || 0;
    },

    get taxTotal() {
        return Object.values(this.cart.taxes).reduce((accumulator, tax) => {
            return accumulator + tax.amount.inCurrentCurrency.amount;
        }, 0);
    },

    get subTotal() {
        return Object.values(this.items).reduce((accumulator, cartItem) => {
            return (
                accumulator +
                cartItem.qty * cartItem.unitPrice.inCurrentCurrency.amount
            );
        }, 0);
    },

    get total() {
        const codMode = window.codFeeDisplayMode || 'separate_line';
        const codPart = codMode === 'add_to_shipping' ? 0 : this.codFee;

        return (
            this.subTotal - this.couponValue + this.taxTotal + this.shippingCost + codPart
        );
    },

    get hasCoupon() {
        return Boolean(this.cart.coupon.code);
    },

    get couponValue() {
        return this.cart.coupon?.value?.inCurrentCurrency?.amount ?? 0;
    },

    init() {
        // Defer network request to reduce initial page load time
        this.fetching = true;

        // If cart is empty, skip initial network request.
        // UI can still show FleetCart.cartQuantity via the getter until user adds items.
        if (!window.FleetCart || !FleetCart.cartQuantity) {
            this.fetching = false;
            this.fetched = true;
            return;
        }

        if (typeof window.requestIdleCallback === "function") {
            window.requestIdleCallback(() => this.fetchingCart(), { timeout: 2000 });
            return;
        }

        setTimeout(() => this.fetchingCart(), 1200);
    },

    async fetchingCart() {
        try {
            this.fetching = true;

            const { data } = await axios.get("/cart/get");

            this.cart = data;
        } catch (error) {
            // Handle error
        } finally {
            this.fetching = false;
            this.fetched = true;
        }
    },

    updateCart(cart) {
        this.cart = { ...cart };
        this.fetched = true;

        this.setCoupon(cart);
    },


    updateCartItemQty({ id, qty }) {
        this.cart.items[id].qty = qty;
    },

    removeCartItem(id) {
        delete this.cart.items[id];
    },

    clearCart() {
        this.cart.items = {};
    },

    setCoupon(cart) {
        if (cart.coupon.code) {
            this.cart.coupon = cart.coupon;
        }
    },
});
