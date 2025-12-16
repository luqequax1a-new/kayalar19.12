import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";

Alpine.data("KoleysiyonGrid", () => ({
    init() {
        this.initKoleysiyonGridSlider();
    },

    initKoleysiyonGridSlider() {
        if (!this.$refs.koleysiyonGrid) {
            return;
        }

        new Swiper(this.$refs.koleysiyonGrid, {
            modules: [Navigation, Pagination],
            slidesPerView: 1,
            spaceBetween: 16,
            navigation: {
                nextEl: this.$refs.koleysiyonNext,
                prevEl: this.$refs.koleysiyonPrev,
            },
            pagination: {
                el: this.$refs.koleysiyonPagination,
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
            },
        });
    },
}));
