import Swiper from "swiper";

export default function (tabs) {
    return {
        tabs,
        activeTab: null,
        loading: false,
        swiper: null,
        products: [],

        get hasAnyProduct() {
            return this.products.length;
        },

        tab(index) {
            return this.tabs[index].name || this.tabs[index];
        },

        async changeTab(index) {
            if (
                this.activeTab === this.tab(index) ||
                this.tab(index) === undefined
            ) {
                return;
            }

            this.activeTab = this.tab(index);

            this.fetchProducts(index);
        },

        classes(index) {
            return {
                active: this.activeTab === this.tab(index) && !this.loading,
                loading: this.activeTab === this.tab(index) && this.loading,
            };
        },

        hideSkeletons() {
            const skeletons = this.$root.querySelectorAll(
                `${this.selector()} .swiper-slide-skeleton`
            );

            skeletons.forEach((skeleton) => skeleton.remove());
        },

        async fetchProducts(tabIndex = 0) {
            this.loading = true;

            try {
                const response = await axios.get(this.url(tabIndex));

                if (this.swiper) {
                    this.swiper.destroy();
                }

                this.products = response.data;

                // Wait for Alpine to render new slides before initializing Swiper.
                if (typeof this.$nextTick === "function") {
                    await this.$nextTick();
                }

                if (this.products.length !== 0) {
                    const selector = this.selector();
                    const element = this.$root.querySelector(selector);
                    
                    this.swiper = new Swiper(element, this.swiperOptions());

                    // Ensure IKAS-style lazy injection rescans newly added slides.
                    try {
                        if (typeof window.__fleetcartIkasRescan === "function") {
                            window.__fleetcartIkasRescan(element);
                        }
                    } catch (e) {}
                }
            } catch (error) {
                // handle error
            } finally {
                this.loading = false;

                this.hideSkeletons();
            }
        },
    };
}
