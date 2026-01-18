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
    } catch (e) { }
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
    initialProducts,
    initialAttributes,
    initialBrands,
    initialCategoryData,
    initialProductsHtml,
    initialPaginationHtml,
    initialShowingText,
    initialTotal,
    productsPageSlug,
} = FleetCart.data;

Alpine.data("ProductIndex", () => ({
    phase: "boot",
    hasEverRenderedProducts: false,
    fetchingProducts: false,
    products: initialProducts || { data: [] },
    productsHtml: "",
    paginationHtml: "",
    showingText: "",
    total: initialTotal || 0,
    isSubmitting: false,
    isSubmittingCount: false,
    attributeFilters: Array.isArray(initialAttributes) ? initialAttributes : (initialAttributes ? Object.values(initialAttributes) : []),
    brands: Array.isArray(initialBrands) ? initialBrands : (initialBrands ? Object.values(initialBrands) : []),
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
    currentPage: initialPage,
    queryParams: {
        query: initialQuery,
        brand: initialBrandSlug ? (initialBrandSlug.includes(",") ? initialBrandSlug.split(",") : [initialBrandSlug]) : [],
        category: initialCategorySlug,
        tag: initialTagSlug,
        attribute: initialAttribute,
        rating: null,
        fromPrice: 0,
        toPrice: maxPrice,
        // Default to 'relevance' (position-based) when:
        // 1. Category is selected OR
        // 2. Main products page (no query - just browsing)
        sort: initialSort || (initialCategorySlug || !initialQuery ? 'relevance' : null),
        perPage: initialPerPage,
        page: initialPage,
    },
    abortController: null,
    fetchTimeout: null,
    openAccordions: ['category'], // Category stays open, price/brands/rating closed by default

    isAccordionOpen(key) {
        return this.openAccordions.includes(key);
    },

    toggleAccordion(key) {
        if (this.isAccordionOpen(key)) {
            this.openAccordions = this.openAccordions.filter(k => k !== key);
        } else {
            this.openAccordions.push(key);
        }
    },

    get totalPage() {
        const t = Number(this.total || 0);
        const per = Number(this.queryParams?.perPage || 1);
        return per > 0 ? Math.ceil(t / per) : 0;
    },

    get isFiltered() {
        const hasAttribute = Object.entries(this.queryParams.attribute || {}).some(([slug, v]) => {
            if (Array.isArray(v)) {
                return v.length > 0;
            }
            if (v && typeof v === "object") {
                const attr = this.attributeFilters.find(a => a.slug === slug);
                if (attr && attr.filterable_type === "range") {
                    return Number(v.min) > Number(attr.min) || Number(v.max) < Number(attr.max);
                }
            }
            return false;
        });
        const hasBrand = Array.isArray(this.queryParams.brand) && this.queryParams.brand.length > 0;
        const hasRating = this.queryParams.rating !== null;
        const hasPrice = this.queryParams.fromPrice > 0 || this.queryParams.toPrice < maxPrice;

        return hasAttribute || hasBrand || hasRating || hasPrice;
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
        } catch (e) { }

        if (this.queryParams.query && this.queryParams.category) {
            const url = new URL(window.location.href);
            url.pathname = `/${productsPageSlug}`;
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

        // Open accordions if filters are active
        if (Number(this.queryParams.fromPrice) > 0 || Number(this.queryParams.toPrice) < Number(maxPrice)) {
            if (!this.openAccordions.includes('price')) this.openAccordions.push('price');
        }
        if (Array.isArray(this.queryParams.brand) && this.queryParams.brand.length > 0) {
            if (!this.openAccordions.includes('brands')) this.openAccordions.push('brands');
        }
        if (this.queryParams.rating) {
            if (!this.openAccordions.includes('rating')) this.openAccordions.push('rating');
        }

        // Open attribute accordions if active
        Object.keys(this.queryParams.attribute || {}).forEach(slug => {
            const attr = this.attributeFilters.find(a => a.slug === slug);
            if (attr && !this.openAccordions.includes('attr_' + attr.id)) {
                this.openAccordions.push('attr_' + attr.id);
            }
        });

        this.$nextTick(() => {
            this.initPriceFilter();
        });

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
        } catch (e) { }
    },

    uid() {
        return generateUid();
    },

    changeSort(value) {
        this.queryParams.sort = value;

        this.delayedFetchProducts();
    },

    changePerPage(value) {
        this.currentPage = 1;
        this.queryParams.perPage = value;
        this.queryParams.page = 1;

        this.delayedFetchProducts();
    },

    initPriceFilter() {
        if (!this.$refs.priceRange) {
            console.warn("Price range slider ref not found");
            return;
        }

        noUiSlider.create(this.$refs.priceRange, {
            connect: true,
            direction: window.FleetCart.rtl ? "rtl" : "ltr",
            start: [minPrice, maxPrice],
            range: {
                min: [minPrice],
                max: [maxPrice],
            },
            tooltips: [true, true],
            format: {
                to: (v) => Math.round(v),
                from: (v) => Number(v)
            }
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
            if (window.innerWidth >= 992) {
                this.delayedFetchProducts();
            } else {
                this.updateCountPreview();
            }
        });
    },

    updatePriceRange(fromPrice, toPrice) {
        this.$refs.priceRange.noUiSlider.set([fromPrice, toPrice]);
        this.updateCountPreview();
    },

    resetFilters() {
        this.queryParams.brand = [];
        this.queryParams.rating = null;
        this.queryParams.attribute = {};
        this.queryParams.fromPrice = 0;
        this.queryParams.toPrice = maxPrice;
        this.queryParams.page = 1;
        this.currentPage = 1;

        try {
            if (this.$refs?.priceRange?.noUiSlider) {
                this.$refs.priceRange.noUiSlider.set([minPrice, maxPrice]);
            }
        } catch (e) {
            console.warn("Main price slider reset failed", e);
        }

        try {
            this.attributeFilters.forEach(attr => {
                if (attr.filterable_type === 'range') {
                    const el = document.querySelector(`[data-attribute-slider="${attr.slug}"]`);
                    if (el && el.noUiSlider) {
                        el.noUiSlider.set([attr.min, attr.max]);
                    }
                }
            });
        } catch (e) {
            console.warn("Attribute sliders reset failed", e);
        }

        this.fetchProducts();
    },

    applyFilters() {
        this.currentPage = 1;
        this.queryParams.page = 1;
        this.fetchProducts();

        if (window.innerWidth < 992 && this.$store.layout.isOpenSidebarFilter) {
            this.$store.layout.closeSidebarFilter();
        }
    },

    // Smart count preview like Ikas
    updateCountPreview() {
        if (this.abortController) {
            this.abortController.abort();
        }
        this.abortController = new AbortController();
        this.isSubmittingCount = true;

        // Prune empty values deeply so the backend doesn't apply empty filters
        const prune = (obj) => {
            if (Array.isArray(obj)) {
                return obj.length === 0 ? null : obj;
            }
            if (obj !== null && typeof obj === 'object') {
                const newObj = {};
                let hasValue = false;
                Object.keys(obj).forEach(k => {
                    const v = prune(obj[k]);
                    if (v !== null && v !== undefined && v !== '') {
                        newObj[k] = v;
                        hasValue = true;
                    }
                });
                return hasValue ? newObj : null;
            }
            return obj;
        };

        const prunedParams = prune(JSON.parse(JSON.stringify(this.queryParams))) || {};

        const params = {
            ...prunedParams,
            page: 1,
            fragment: 1,
            _cache: new Date().getTime()
        };

        const queryString = $.param(params);
        const fetchUrl = `${window.location.pathname}?${queryString}`;

        fetch(fetchUrl, {
            signal: this.abortController.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data && typeof data.total !== 'undefined') {
                    this.total = data.total;
                }
            })
            .catch(error => {
                if (error.name !== 'AbortError') {
                    console.error("Smart count preview failed", error);
                }
            })
            .finally(() => {
                this.isSubmittingCount = false;
            });
    },

    toggleAttributeFilter(slug, value) {
        const attribute = this.attributeFilters.find(a => a.slug === slug);
        const isSingleSelect = attribute && (attribute.filterable_type === 'radio' || attribute.filterable_type === 'dropdown');

        if (!this.queryParams.attribute.hasOwnProperty(slug)) {
            this.queryParams.attribute[slug] = isSingleSelect ? null : [];
        }

        if (isSingleSelect) {
            this.queryParams.attribute[slug] = this.queryParams.attribute[slug] === value ? null : value;
        } else if (Array.isArray(this.queryParams.attribute[slug])) {
            const index = this.queryParams.attribute[slug].indexOf(value);
            if (index > -1) {
                this.queryParams.attribute[slug].splice(index, 1);
            } else {
                this.queryParams.attribute[slug].push(value);
            }
            // Trigger Alpine reactivity by replacing the array reference
            this.queryParams.attribute[slug] = [...this.queryParams.attribute[slug]];
        } else {
            this.queryParams.attribute[slug] = [value];
        }

        if (attribute && attribute.filterable_type === 'dropdown') {
            this.delayedFetchProducts({ updateAttributeFilters: false });
        } else {
            if (window.innerWidth >= 992) {
                this.delayedFetchProducts();
            } else {
                this.updateCountPreview();
            }
        }
    },

    initAttributeRangeFilter(el, attribute) {
        if (el.noUiSlider) {
            el.noUiSlider.destroy();
        }

        const initialMin = (this.queryParams.attribute[attribute.slug] && this.queryParams.attribute[attribute.slug].min) || attribute.min;
        const initialMax = (this.queryParams.attribute[attribute.slug] && this.queryParams.attribute[attribute.slug].max) || attribute.max;

        noUiSlider.create(el, {
            connect: true,
            direction: window.FleetCart.rtl ? "rtl" : "ltr",
            start: [initialMin, initialMax],
            range: {
                min: [Number(attribute.min)],
                max: [Number(attribute.max)],
            },
            tooltips: [true, true],
            format: {
                to: (v) => Math.round(v),
                from: (v) => Number(v)
            }
        });

        // Track if the first update is from initialization
        let isInitial = true;

        el.noUiSlider.on("update", (values, handle) => {
            const min = Number(values[0]);
            const max = Number(values[1]);

            // If it's the initial set and the values are standard, don't set queryParams
            // This prevents the backend from applying a whereHas that excludes products without attributes
            if (isInitial && min === Number(attribute.min) && max === Number(attribute.max)) {
                if (!this.queryParams.attribute[attribute.slug]) {
                    isInitial = false;
                    return;
                }
            }
            isInitial = false;

            if (!this.queryParams.attribute[attribute.slug] || Array.isArray(this.queryParams.attribute[attribute.slug])) {
                this.queryParams.attribute[attribute.slug] = { min: attribute.min, max: attribute.max };
            }

            this.queryParams.attribute[attribute.slug].min = min;
            this.queryParams.attribute[attribute.slug].max = max;
        });

        el.noUiSlider.on("change", (values) => {
            const min = Number(values[0]);
            const max = Number(values[1]);

            // If user moved it back to default, we can choose to remove it to be "clean"
            if (min === Number(attribute.min) && max === Number(attribute.max)) {
                delete this.queryParams.attribute[attribute.slug];
            }

            if (window.innerWidth >= 992) {
                this.delayedFetchProducts();
            } else {
                this.updateCountPreview();
            }
        });
    },

    updateAttributeRange(slug, min, max) {
        const el = document.querySelector(`[data-attribute-slider="${slug}"]`);
        if (el && el.noUiSlider) {
            el.noUiSlider.set([min, max]);
        }
    },

    isFilteredByAttribute(slug, value) {
        if (!this.queryParams.attribute.hasOwnProperty(slug)) {
            return false;
        }

        const attrValue = this.queryParams.attribute[slug];

        if (Array.isArray(attrValue)) {
            return attrValue.includes(value);
        }

        return attrValue === value;
    },

    toggleBrandFilter(slug) {
        if (!Array.isArray(this.queryParams.brand)) {
            this.queryParams.brand = this.queryParams.brand ? [this.queryParams.brand] : [];
        }

        const index = this.queryParams.brand.indexOf(slug);
        if (index > -1) {
            this.queryParams.brand.splice(index, 1);
        } else {
            this.queryParams.brand.push(slug);
        }

        this.queryParams.brand = [...this.queryParams.brand];

        if (window.innerWidth >= 992) {
            this.delayedFetchProducts();
        } else {
            this.updateCountPreview();
        }
    },

    toggleRatingFilter(rating) {
        this.queryParams.rating = this.queryParams.rating == rating ? null : rating;

        if (window.innerWidth >= 992) {
            this.delayedFetchProducts();
        } else {
            this.updateCountPreview();
        }
    },

    clearRatingFilter() {
        this.queryParams.rating = null;

        if (window.innerWidth >= 992) {
            this.delayedFetchProducts();
        } else {
            this.updateCountPreview();
        }
    },

    changeCategory(category) {
        const url = new URL(window.location.href);

        this.categoryName = category.name;
        this.categoryBanner = category.banner.path;

        this.currentPage = 1;
        this.queryParams.category = category.slug;
        this.queryParams.attribute = {};
        this.queryParams.page = 1;
        this.queryParams.fromPrice = 0;
        this.queryParams.toPrice = maxPrice;
        // Reset sort to relevance (position-based) when changing category
        this.queryParams.sort = 'relevance';

        if (this.$refs?.priceRange?.noUiSlider) {
            this.$refs.priceRange.noUiSlider.set([minPrice, maxPrice]);
        }

        this.fetchProducts();

        // SEO Optimized URL Handling
        // If we have a search query, keep it but switch the primary path to the category slug
        // If no query, just go to the category slug path.
        const cleanPath = `/${category.slug}`;
        if (url.pathname !== cleanPath) {
            url.pathname = cleanPath;
            // Remove the category query param since it's now in the path
            url.searchParams.delete("category");

            window.history.pushState({}, "", url.toString());
            updatePageTitle(category);

            // Update Canonical and Meta
            const canonical = document.querySelector('link[rel="canonical"]');
            if (canonical) canonical.setAttribute('href', window.location.origin + cleanPath);

            const metaOgUrl = document.querySelector('meta[property="og:url"]');
            if (metaOgUrl) metaOgUrl.setAttribute('content', window.location.origin + cleanPath);
        }
    },

    changePage(page) {
        this.currentPage = page;
        this.queryParams.page = page;

        this.fetchProducts();
    },

    async fetchProducts(options = { updateAttributeFilters: true }) {
        if (this.fetchTimeout) {
            clearTimeout(this.fetchTimeout);
        }

        if (this.abortController) {
            this.abortController.abort();
        }

        this.abortController = new AbortController();
        this.fetchingProducts = true;
        this.isSubmitting = true;
        this.phase = "fetching";

        try {
            const response = await axios.get(window.location.pathname, {
                params: {
                    ...this.queryParams,
                    fragment: 1,
                },
                signal: this.abortController.signal,
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

            if (response.data.brands) {
                this.brands = response.data.brands;
            }

            if (
                response.data.category &&
                Object.prototype.hasOwnProperty.call(
                    response.data.category,
                    "description_html"
                )
            ) {
                this.categoryDescriptionHtml = response.data.category.description_html || "";
                this.categoryFaqItems = Array.isArray(response.data.category.faq_items)
                    ? response.data.category.faq_items
                    : [];
            } else {
                this.categoryDescriptionHtml = "";
                this.categoryFaqItems = [];
            }

            this.phase = "ready";

            if (window.innerWidth < 992 && this.$store.layout.sidebarFilterOpen) {
                this.$store.layout.closeSidebarFilter();
            }
        } catch (error) {
            if (axios.isCancel(error) || error.name === 'CanceledError' || error.name === 'AbortError') {
                return;
            }
            if (error.response?.data?.message) {
                notify(error.response.data.message);
            }
            this.phase = "error";
        } finally {
            if (this.abortController?.signal?.aborted) {
                // Keep fetching state if we were aborted by a new request
            } else {
                this.fetchingProducts = false;
                this.isSubmitting = false;
            }
        }
    },

    delayedFetchProducts(options = { updateAttributeFilters: true }) {
        if (this.fetchTimeout) clearTimeout(this.fetchTimeout);

        // Faster debounce for instant feel (400ms)
        this.fetchTimeout = setTimeout(() => {
            this.fetchProducts(options);
        }, 400);
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
