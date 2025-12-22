import Swiper from "swiper";
import { Navigation, Pagination, Autoplay } from "swiper/modules";

Alpine.data("Testimonials", () => ({
    swiper: null,

    init() {
        this.$nextTick(() => {
            this.initSwiper();
        });
    },

    initSwiper() {
        if (this.swiper) {
            this.swiper.destroy();
        }

        const container = this.$refs.swiper;

        this.swiper = new Swiper(container, {
            modules: [Navigation, Pagination, Autoplay],
            slidesPerView: 1,
            spaceBetween: 20,
            loop: false,
            autoplay: false,
            pagination: {
                el: this.$refs.pagination,
                clickable: true,
                dynamicBullets: true,
            },
            breakpoints: {
                640: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 24,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
        });
    },
}));
