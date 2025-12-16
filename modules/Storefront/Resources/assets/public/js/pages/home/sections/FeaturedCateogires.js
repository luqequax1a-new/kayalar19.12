import Swiper from "swiper";
import { Pagination, Autoplay } from "swiper/modules";
import ProductTabsMixin from "../../../mixins/ProductTabsMixin";
import "../../../components/ProductCard";

Alpine.data("FeaturedCategories", (tabs) => ({
    ...ProductTabsMixin(tabs),
    tabsSwiper: null,

    init() {
        this.changeTab(0);
        this.initTabsSwiper();
    },

    url(tabIndex) {
        return `/storefront/featured-categories/${tabIndex + 1}/products`;
    },

    selector() {
        return ".featured-category-products";
    },

    swiperOptions() {
        return {
            modules: [Pagination],
            slidesPerView: 2,
            pagination: {
                el: ".swiper-pagination",
                dynamicBullets: true,
                clickable: true,
            },
            breakpoints: {
                576: {
                    slidesPerView: 3,
                },
                830: {
                    slidesPerView: 4,
                },
                991: {
                    slidesPerView: 5,
                },
                1200: {
                    slidesPerView: 6,
                },
                1400: {
                    slidesPerView: 7,
                },
                1760: {
                    slidesPerView: 8,
                },
            },
        };
    },

    initTabsSwiper() {
        this.$nextTick(() => {
            const container = this.$root.querySelector(
                ".featured-categories-tabs-swiper"
            );

            if (!container) {
                return;
            }

            if (this.tabsSwiper) {
                this.tabsSwiper.destroy(true, true);
            }

            this.tabsSwiper = new Swiper(container, {
                modules: [Pagination, Autoplay],
                // Mobilde 3 kart görünür, 3'er kayar
                slidesPerView: 3,
                slidesPerGroup: 3,
                spaceBetween: 0,
                autoplay: {
                    delay: 3500,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".featured-categories-tabs-pagination",
                    clickable: true,
                },
                breakpoints: {
                    // Tablet: daha fazla kart, ama 3'er kayma mantığı korunabilir
                    768: {
                        slidesPerView: 5,
                        slidesPerGroup: 3,
                        spaceBetween: 8,
                    },
                    // Desktop: geniş ekranda 7 kart göster
                    1200: {
                        slidesPerView: 7,
                        slidesPerGroup: 3,
                        spaceBetween: 10,
                    },
                },
            });
        });
    },
}));
