import Swiper from "swiper";
import { Navigation, Pagination, Autoplay } from "swiper/modules";
import "../../../components/ProductCard";

Alpine.data("CarouselProducts", (products = []) => ({
    swiper: null,
    products: products,

    init() {
        this.$nextTick(() => {
            const container = this.$root.querySelector(".carousel-products");

            if (!container) {
                return;
            }

            if (this.swiper) {
                this.swiper.destroy();
            }

            const autoplayEnabled = container.getAttribute("data-autoplay") === "true";
            const autoplaySpeed = parseInt(container.getAttribute("data-autoplay-speed") || "3000", 10);
            const perRowMobile = parseInt(container.getAttribute("data-per-row-mobile") || "2", 10);
            const perRowTablet = parseInt(container.getAttribute("data-per-row-tablet") || "3", 10);
            const perRowDesktop = parseInt(container.getAttribute("data-per-row-desktop") || "5", 10);

            const showDots = container.getAttribute("data-show-dots") !== "false";
            const showArrows = container.getAttribute("data-show-arrows") !== "false";

            const nextEl = container.querySelector(".swiper-button-next");
            const prevEl = container.querySelector(".swiper-button-prev");
            const paginationEl = container.querySelector(".carousel-pagination");

            this.swiper = new Swiper(container, {
                modules: [Navigation, Pagination, Autoplay],
                // Varsayılan (telefon) görünüm için
                slidesPerView: perRowMobile,
                autoplay: autoplayEnabled
                    ? {
                          delay: autoplaySpeed,
                          disableOnInteraction: false,
                          pauseOnMouseEnter: true,
                      }
                    : false,
                navigation: showArrows
                    ? {
                          nextEl,
                          prevEl,
                      }
                    : false,
                pagination: showDots
                    ? {
                          el: paginationEl,
                          dynamicBullets: true,
                          clickable: true,
                      }
                    : false,
                breakpoints: {
                    576: {
                        slidesPerView: perRowTablet,
                    },
                    830: {
                        slidesPerView: perRowTablet + 1 <= perRowDesktop ? perRowTablet + 1 : perRowDesktop,
                    },
                    991: {
                        slidesPerView: perRowDesktop,
                    },
                    1200: {
                        slidesPerView: perRowDesktop + 1,
                    },
                    1400: {
                        slidesPerView: perRowDesktop + 2,
                    },
                    1760: {
                        slidesPerView: perRowDesktop + 3,
                    },
                },
            });
        });
    },
}));
