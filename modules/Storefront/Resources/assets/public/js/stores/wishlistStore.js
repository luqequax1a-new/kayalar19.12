Alpine.store("wishlist", {
    wishlist: [],
    bootstrapped: {},
    countValue: null,
    fetching: false,
    fetched: false,

    get count() {
        if (this.fetched) {
            return this.wishlist.length;
        }

        if (this.countValue === null) {
            this.countValue = FleetCart.wishlistCount;
        }

        return this.countValue;
    },

    init() {
        const path = window.location?.pathname || "";

        if (this.countValue === null) {
            this.countValue = FleetCart.wishlistCount;
        }

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

                this.countValue = this.wishlist.length;
                FleetCart.wishlistCount = this.countValue;
            } catch (error) {
                // Handle error
            } finally {
                this.fetching = false;
            }

            return;
        }

        this.fetching = false;
    },

    bootstrap(id, inWishlist) {
        try {
            this.bootstrapped[String(id)] = !!inWishlist;
        } catch (_) {}
    },

    inWishlist(id) {
        if (this.fetched) {
            return this.wishlist.includes(id);
        }

        try {
            const key = String(id);
            if (Object.prototype.hasOwnProperty.call(this.bootstrapped, key)) {
                return !!this.bootstrapped[key];
            }
        } catch (_) {}

        return false;
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

            try {
                await axios.post("/account/wishlist/products", {
                    productId: id,
                });

                if (!this.wishlist.includes(id)) {
                    this.wishlist.push(id);
                }

                this.bootstrap(id, true);

                if (!this.fetched) {
                    if (this.countValue === null) {
                        this.countValue = FleetCart.wishlistCount;
                    }
                    this.countValue = Number(this.countValue || 0) + 1;
                    FleetCart.wishlistCount = this.countValue;
                }
            } catch (error) {
                this.bootstrap(id, false);
                throw error;
            }

            return;
        }

        window.location.href = "/login";
    },

    async removeFromWishlist(id) {
        if (FleetCart.loggedIn && !this.fetched && !this.fetching) {
            this.fetchWishlist().then(() => this.removeFromWishlist(id));
            return;
        }

        try {
            await axios.delete(`/account/wishlist/products/${id}`);

            const idx = this.wishlist.indexOf(id);
            if (idx !== -1) {
                this.wishlist.splice(idx, 1);
            }

            this.bootstrap(id, false);

            if (!this.fetched) {
                if (this.countValue === null) {
                    this.countValue = FleetCart.wishlistCount;
                }
                this.countValue = Math.max(0, Number(this.countValue || 0) - 1);
                FleetCart.wishlistCount = this.countValue;
            }
        } catch (error) {
            this.bootstrap(id, true);
            throw error;
        }
    },
});
