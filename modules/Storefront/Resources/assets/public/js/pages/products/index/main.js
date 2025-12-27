import Alpine from "alpinejs";
import { generateUid } from "../../../functions";
import { Navigation } from "swiper/modules";
import Swiper from "swiper";
import noUiSlider from "nouislider";
import "./components/CustomFilterSelect";
import "./components/CustomPageSelect";
import "../../../components/ProductCard";
import "../../../components/Pagination";

if (import.meta && import.meta.env && import.meta.env.DEV) {
    try {
        new PerformanceObserver((list) => {
            const entries = list.getEntries();
            const last = entries && entries.length ? entries[entries.length - 1] : null;
            const el = last && last.element ? last.element : null;
            if (el && el.tagName === "IMG") {
                console.log(
                    "[LCP]",
                    `${Math.round(last.startTime)}ms`,
                    el.currentSrc || el.src,
                    el
                );
            } else if (last) {
                console.log("[LCP]", `${Math.round(last.startTime)}ms`, last.element);
            }
        }).observe({ type: "largest-contentful-paint", buffered: true });
    } catch (e) {}
}

function updatePageTitle(selectedCategory) {
    const baseTitle = (window.FleetcartSEO && window.FleetcartSEO.baseTitle) || document.title;
    if (!selectedCategory) {
        document.title = baseTitle;
        return;
    }
    const categoryTitle = selectedCategory.meta_title || `${selectedCategory.name} | ${baseTitle}`;
    document.title = categoryTitle;
}

const {
    initialQuery,
    initialBrandName,
    initialBrandBanner,
    initialBrandSlug,
    initialCategoryName,
    initialCategoryBanner,
    initialCategorySlug,
    initialCategoryDescriptionHtml,
    initialCategoryFaqItems,
    initialTagName,
    initialTagSlug,
    initialAttribute,
    minPrice,
    maxPrice,
    initialSort,
    initialPage,
    initialPerPage,
    initialViewMode,
    initialProducts,
    initialAttributes,
    initialCategoryData,
    initialProductsHtml,
    initialPaginationHtml,
    initialShowingText,
    initialTotal,
} = FleetCart.data;

Alpine.data("ProductIndex", () => ({
    phase: "boot",
    hasEverRenderedProducts: false,
    fetchingProducts: false,
    products: initialProducts || { data: [] },
    productsHtml: "",
    paginationHtml: "",
    showingText: "",
    total: 0,
    attributeFilters: Array.isArray(initialAttributes) ? initialAttributes : [],
    initialBrandName,
    initialTagName,
    brandBanner: initialBrandBanner,
    categoryName: initialCategoryName,
    categorySlug: initialCategorySlug,
    categoryBanner: initialCategoryBanner,
    categoryDescriptionHtml: initialCategoryDescriptionHtml || "",
    categoryFaqItems: Array.isArray(initialCategoryFaqItems)
        ? initialCategoryFaqItems
        : [],
    viewMode: initialViewMode,
    currentPage: initialPage,
    queryParams: {
        query: initialQuery,
        brand: initialBrandSlug,
        category: initialCategorySlug,
        tag: initialTagSlug,
        attribute: initialAttribute,
        fromPrice: 0,
        toPrice: maxPrice,
        sort: initialSort,
        perPage: initialPerPage,
        page: initialPage,
    },

    get totalPage() {
        const t = Number(this.total || 0);
        const per = Number(this.queryParams?.perPage || 1);
        return per > 0 ? Math.ceil(t / per) : 0;
    },

    init() {
        const hasSsrProducts =
            this.products && Array.isArray(this.products?.data) && this.products.data.length > 0;

        this.total = Number((initialTotal ?? this.products?.total) || 0);
        this.showingText =
            typeof initialShowingText === "string" && initialShowingText.length
                ? initialShowingText
                : this.total > 0
                  ? trans("storefront::products.showing_results", {
                        from: this.products?.from,
                        to: this.products?.to,
                        total: this.products?.total,
                    })
                  : "";

        try {
            // Initial SSR HTML: prefer server-provided fragments to avoid blanking mounts.
            if (typeof initialProductsHtml === "string" && initialProductsHtml.length) {
                this.productsHtml = initialProductsHtml;
            } else if (this.$refs?.productsMount) {
                this.productsHtml = this.$refs.productsMount.innerHTML || "";
            }

            if (typeof initialPaginationHtml === "string" && initialPaginationHtml.length) {
                this.paginationHtml = initialPaginationHtml;
            } else if (this.$refs?.paginationMount) {
                this.paginationHtml = this.$refs.paginationMount.innerHTML || "";
            }
            this.hasEverRenderedProducts = true;
        } catch (e) {}

        if (this.queryParams.query && this.queryParams.category) {
            const url = new URL(window.location.href);
            url.pathname = "/products";
            url.searchParams.set("query", this.queryParams.query);
            url.searchParams.delete("category");
            window.history.replaceState({}, "", url.toString());

            this.queryParams.category = null;
            this.queryParams.attribute = {};
            this.queryParams.fromPrice = 0;
            this.queryParams.toPrice = maxPrice;
            this.categoryName = "";
            this.categoryBanner = null;

            if (this.$refs?.priceRange?.noUiSlider) {
                this.$refs.priceRange.noUiSlider.set([minPrice, maxPrice]);
            }
        } else if (this.queryParams.query) {
            this.currentPage = 1;
            this.queryParams.page = 1;
            this.queryParams.attribute = {};
            this.queryParams.fromPrice = 0;
            this.queryParams.toPrice = maxPrice;

            if (this.$refs?.priceRange?.noUiSlider) {
                this.$refs.priceRange.noUiSlider.set([minPrice, maxPrice]);
            }
        }
        this.initPriceFilter();

        if (!hasSsrProducts) {
            this.fetchProducts();
        } else {
            if (initialCategoryData) {
                if (typeof initialCategoryData.description_html !== "undefined") {
                    this.categoryDescriptionHtml = initialCategoryData.description_html || "";
                }
                if (Array.isArray(initialCategoryData.faq_items)) {
                    this.categoryFaqItems = initialCategoryData.faq_items;
                }
                if (typeof initialCategoryData.name !== "undefined" && initialCategoryData.name) {
                    this.categoryName = initialCategoryData.name;
                }
                if (typeof initialCategoryData.slug !== "undefined" && initialCategoryData.slug) {
                    this.categorySlug = initialCategoryData.slug;
                }
            }
        }
        this.initLatestProductsSlider();
        this.phase = "ready";
    },

    reinitMounts() {
        try {
            if (window.Alpine && typeof window.Alpine.initTree === "function") {
                if (this.$refs?.productsMount) {
                    window.Alpine.initTree(this.$refs.productsMount);
                }
                if (this.$refs?.paginationMount) {
                    window.Alpine.initTree(this.$refs.paginationMount);
                }
            }
        } catch (e) {}
    },

    uid() {
        return generateUid();
    },

    changeSort(value) {
        this.queryParams.sort = value;

        this.fetchProducts();
    },

    changePerPage(value) {
        this.currentPage = 1;
        this.queryParams.perPage = value;
        this.queryParams.page = 1;

        this.fetchProducts();
    },

    initPriceFilter() {
        noUiSlider.create(this.$refs.priceRange, {
            connect: true,
            direction: window.FleetCart.rtl ? "rtl" : "ltr",
            start: [minPrice, maxPrice],
            range: {
                min: [minPrice],
                max: [maxPrice],
            },
        });

        this.$refs.priceRange.noUiSlider.on("update", (values, handle) => {
            const value = Number(values[handle]);

            if (handle === 0) {
                this.queryParams.fromPrice = value;
            } else {
                this.queryParams.toPrice = value;
            }
        });

        this.$refs.priceRange.noUiSlider.on("change", () => {
            this.fetchProducts();
        });
    },

    updatePriceRange(fromPrice, toPrice) {
        this.$refs.priceRange.noUiSlider.set([fromPrice, toPrice]);

        this.fetchProducts();
    },

    toggleAttributeFilter(slug, value) {
        if (!this.queryParams.attribute.hasOwnProperty(slug)) {
            this.queryParams.attribute[slug] = [];
        }

        if (this.queryParams.attribute[slug].includes(value)) {
            this.queryParams.attribute[slug].splice(
                this.queryParams.attribute[slug].indexOf(value),
                1
            );
        } else {
            this.queryParams.attribute[slug].push(value);
        }

        this.fetchProducts({ updateAttributeFilters: false });
    },

    isFilteredByAttribute(slug, value) {
        if (!this.queryParams.attribute.hasOwnProperty(slug)) {
            return false;
        }

        return this.queryParams.attribute[slug].includes(value);
    },

    changeCategory(category) {
        const url = new URL(window.location.href);
        const prevCategorySlug = this.queryParams.category;

        this.categoryName = category.name;
        this.categoryBanner = category.banner.path;

        this.currentPage = 1;
        this.queryParams.query = null;
        this.queryParams.category = category.slug;
        this.queryParams.attribute = {};
        this.queryParams.page = 1;
        this.queryParams.fromPrice = 0;
        this.queryParams.toPrice = maxPrice;

        if (this.$refs?.priceRange?.noUiSlider) {
            this.$refs.priceRange.noUiSlider.set([minPrice, maxPrice]);
        }

        this.fetchProducts();

        if (url.pathname.includes(`/categories/${prevCategorySlug}/products`)) {
            url.pathname = url.pathname.replace(
                prevCategorySlug,
                category.slug
            );

            window.history.replaceState(null, "", url.toString());
            updatePageTitle(category);
            return;
        }

        url.searchParams.set("category", category.slug);
        window.history.replaceState({}, "", url);
        updatePageTitle(category);
    },

    changePage(page) {
        this.currentPage = page;
        this.queryParams.page = page;

        this.fetchProducts();
    },

    async fetchProducts(options = { updateAttributeFilters: true }) {
        this.fetchingProducts = true;
        this.phase = "fetching";

        try {
            const response = await axios.get(`/products`, {
                params: {
                    ...this.queryParams,
                    fragment: 1,
                },
            });

            const products = response.data.products;
            const data = Array.isArray(products?.data) ? products.data : [];
            const rawData = data.map((p) => (typeof Alpine.raw === "function" ? Alpine.raw(p) : p));

            // Atomic swap: first update data, then HTML fragments.
            this.products = { ...products, data: rawData };
            this.total = Number(response.data.total ?? products?.total ?? 0);
            this.productsHtml = response.data.products_html || "";
            this.paginationHtml = response.data.pagination_html || "";
            this.showingText = response.data.showing_text || "";
            this.hasEverRenderedProducts = true;

            this.$nextTick(() => {
                this.reinitMounts();
            });

            if (options.updateAttributeFilters) {
                this.attributeFilters = response.data.attributes;
            }

            if (
                response.data.category &&
                Object.prototype.hasOwnProperty.call(
                    response.data.category,
                    "description_html"
                )
            ) {
                this.categoryDescriptionHtml =
                    response.data.category.description_html || "";
                this.categoryFaqItems = Array.isArray(
                    response.data.category.faq_items
                )
                    ? response.data.category.faq_items
                    : [];
            } else {
                this.categoryDescriptionHtml = "";
                this.categoryFaqItems = [];
            }

            this.phase = "ready";
        } catch (error) {
            notify(error.response.data.message);
            this.phase = "error";
        } finally {
            this.fetchingProducts = false;
        }
    },

    initLatestProductsSlider() {
        new Swiper(this.$refs.latestProducts, {
            modules: [Navigation],
            slidesPerView: 1,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    },
}));
