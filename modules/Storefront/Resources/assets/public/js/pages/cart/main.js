import "../../components/CartItem";
import "../../components/LandscapeProducts";


Alpine.data("Cart", () => ({
    shippingMethodName: null,

    get cartFetched() {
        return this.$store.cart.fetched;
    },

    get cartIsEmpty() {
        return this.$store.cart.isEmpty;
    },

    init() {
        Alpine.effect(() => {
            if (this.cartFetched) {
                try {
                    console.log("[CART] cartFetched=true, items:", Object.keys(this.$store.cart.cart.items || {}).length);
                } catch (e) { }
                this.hideSkeleton();
            }
        });
    },

    hideSkeleton() {
        const el = document.querySelector(".cart-skeleton");

        if (!el) {
            try {
                console.log("[CART] hideSkeleton: .cart-skeleton not found");
            } catch (e) { }
            return;
        }

        try {
            console.log("[CART] hideSkeleton: removing .cart-skeleton");
        } catch (e) { }

        if (typeof el.remove === "function") {
            el.remove();
        } else if (el.parentNode) {
            el.parentNode.removeChild(el);
        }
    },

    clearCart() {
        this.$store.cart.clearCart();

        axios
            .delete("/cart/clear")
            .then(({ data }) => {
                this.$store.cart.updateCart(data);
            })
            .catch((error) => {
                notify(error.response.data.message);
            });
    },
}));
