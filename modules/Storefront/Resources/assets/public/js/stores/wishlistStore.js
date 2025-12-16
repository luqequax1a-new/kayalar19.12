Alpine.store("wishlist", {
    wishlist: [],
    fetching: false,
    fetched: false,

    get count() {
        return this.fetching ? FleetCart.wishlistCount : this.wishlist.length;
    },

    init() {
        const path = window.location?.pathname || "";

        // Hard-disable wishlist fetch on non-wishlist pages.
        // Keep showing count via FleetCart.wishlistCount.
        if (path.startsWith("/account/wishlist")) {
            this.fetchWishlist();
        }
    },

    async fetchWishlist() {
        if (FleetCart.loggedIn) {
            try {
                this.fetching = true;

                const { data } = await axios.get(
                    "/account/wishlist/products/list"
                );

                this.wishlist = data;
                this.fetched = true;
            } catch (error) {
                // Handle error
            } finally {
                this.fetching = false;
            }

            return;
        }

        this.fetching = false;
    },

    inWishlist(id) {
        return this.wishlist.includes(id);
    },

    syncWishlist(id) {
        if (FleetCart.loggedIn && !this.fetched && !this.fetching) {
            this.fetchWishlist().then(() => this.syncWishlist(id));

            return;
        }

        if (this.inWishlist(id)) {
            this.removeFromWishlist(id);

            return;
        }

        this.addToWishlist(id);
    },

    async addToWishlist(id) {
        if (FleetCart.loggedIn) {
            if (!this.fetched && !this.fetching) {
                await this.fetchWishlist();
            }

            this.wishlist.push(id);

            await axios.post("/account/wishlist/products", {
                productId: id,
            });

            return;
        }

        window.location.href = "/login";
    },

    removeFromWishlist(id) {
        if (FleetCart.loggedIn && !this.fetched && !this.fetching) {
            this.fetchWishlist().then(() => this.removeFromWishlist(id));
            return;
        }

        this.wishlist.splice(this.wishlist.indexOf(id), 1);

        axios.delete(`/account/wishlist/products/${id}`);
    },
});
