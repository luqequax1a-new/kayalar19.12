import Swiper from "swiper";
import { Navigation } from "swiper/modules";
import { chunk } from "lodash";
import "../../../../components/ProductCard";

Alpine.data("VerticalProducts", (columnNumber) => ({
    chunk,
    products: [],

    normalizeProducts(items) {
        const list = Array.isArray(items) ? items : [];

        return list.flatMap((p) => {
            if (!p) return [];

            const listSeparately = !!p.list_variants_separately;
            const variants = Array.isArray(p.variants) ? p.variants : [];

            if (listSeparately && variants.length) {
                const actives = variants.filter((v) => !!v && (v.is_active === true || v.is_active === 1));

                if (actives.length) {
                    return actives.map((v) => {
                        return {
                            ...p,
                            variant: v,
                            // keep variants array for UI bits that expect it
                            variants: p.variants,
                            // stable key for x-for
                            listing_key: p.listing_key || `p${p.id}-v${v.id || v.uid || ''}`,
                        };
                    });
                }
            }

            return [p];
        });
    },

    get hasAnyProduct() {
        return this.products.length !== 0;
    },

    init() {
        this.fetchProducts();
    },

    async fetchProducts() {
        const response = await axios.get(
            `/storefront/vertical-products/${columnNumber}`
        );

        this.products = this.normalizeProducts(response.data);

        setTimeout(() => {
            new Swiper(this.$refs.verticalProducts, this.swiperOptions());
        }, 0);
    },

    swiperOptions() {
        return {
            modules: [Navigation],
            slidesPerView: 1,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        };
    },
}));
