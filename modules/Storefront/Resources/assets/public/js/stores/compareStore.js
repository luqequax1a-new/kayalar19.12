Alpine.store("compare", {
    compareList: [],
    products: {},
    attributes: {},
    fetchingCompareList: false,
    fetchedCompareProducts: false,

    get isEmptyCompareList() {
        return this.compareList.length === 0;
    },

    get count() {
        return this.fetchingCompareList
            ? FleetCart.compareCount
            : this.compareList.length;
    },

    init() {
        const path = window.location?.pathname || "";

        if (path.endsWith("/compare")) {
            this.fetchCompareList();
            return;
        }

        // If there are no compare items, skip the network request on initial page load.
        // UI can still show FleetCart.compareCount via the getter until user adds items.
        if (!FleetCart || !FleetCart.compareCount) {
            this.fetchingCompareList = false;
            return;
        }

        this.fetchingCompareList = true;

        if (typeof window.requestIdleCallback === "function") {
            window.requestIdleCallback(() => this.fetchCompareList(), { timeout: 2000 });
            return;
        }

        setTimeout(() => this.fetchCompareList(), 1500);
    },

    async fetchCompareProducts() {
        const { data } = await axios.get("/compare/products");

        this.fetchedCompareProducts = true;
        this.products = data.products;
        this.attributes = data.attributes;
    },

    async fetchCompareList() {
        try {
            this.fetchingCompareList = true;

            const { data } = await axios.get("/compare/list");

            this.compareList = data;
        } catch (error) {
            // Handle error
        } finally {
            this.fetchingCompareList = false;
        }
    },

    inCompareList(id) {
        return this.compareList.includes(id);
    },

    async syncCompareList(id) {
        if (this.inCompareList(id)) {
            this.removeFromCompareList(id);

            return;
        }

        this.addToCompareList(id);
    },

    addToCompareList(id) {
        this.compareList.push(id);

        axios
            .post("/compare", {
                productId: id,
            })
            .then(() => {
                if (window.location.pathname.endsWith("/compare")) {
                    this.fetchCompareProducts();
                }
            });
    },

    async removeFromCompareList(id) {
        delete this.products[id];

        this.compareList.splice(this.compareList.indexOf(id), 1);

        await axios.delete(`compare/${id}`);
    },
});
