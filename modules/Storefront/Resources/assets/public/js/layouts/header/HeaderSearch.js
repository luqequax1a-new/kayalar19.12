import { throttle } from "lodash";

Alpine.data(
    "HeaderSearch",
    ({ categories, initialQuery, initialCategory }) => ({
        categories,
        initialQuery,
        initialCategory,
        skeleton: true,
        showMiniSearch: false,
        activeSuggestion: null,
        showSuggestions: false,
        form: {
            query: initialQuery,
            category: initialCategory,
        },
        userSelectedCategory: false,
        suggestions: {
            categories: [],
            products: [],
            bestSellers: [],
            popularCategories: [],
            popularSearches: [],
            bestSellingCategories: [],
            bestSellingBrands: [],
            recentSearches: [],
            remaining: 0,
        },

        get shouldShowSuggestions() {
            if (!this.showSuggestions) {
                return false;
            }

            return this.hasAnySuggestion || this.hasAnyPopularSuggestion;
        },

        get moreResultsUrl() {
            if (this.userSelectedCategory && this.form.category) {
                // Category search - use clean URL
                return `/${this.form.category}?query=${this.form.query}`;
            }
            // All products search
            return `/${window.FleetCart.productsPageSlug}?query=${this.form.query}`;
        },

        get hasAnySuggestion() {
            return this.suggestions.products.length !== 0;
        },

        get hasAnyCategorySuggestion() {
            return this.suggestions.categories.length !== 0;
        },

        get hasAnyPopularSuggestion() {
            return this.suggestions.bestSellers.length !== 0 ||
                this.suggestions.popularCategories.length !== 0 ||
                this.suggestions.popularSearches.length !== 0 ||
                this.suggestions.bestSellingCategories.length !== 0 ||
                this.suggestions.bestSellingBrands.length !== 0 ||
                this.suggestions.recentSearches.length !== 0;
        },

        get allSuggestions() {
            return [
                ...this.suggestions.categories,
                ...this.suggestions.products,
                ...this.suggestions.recentSearches.map(term => ({ name: term, url: `/${window.FleetCart.productsPageSlug}?query=${term}`, slug: 'recent-' + term, type: 'search' })),
                ...this.suggestions.popularSearches.map(term => ({ name: term, url: `/${window.FleetCart.productsPageSlug}?query=${term}`, slug: 'popular-' + term, type: 'search' })),
                ...this.suggestions.popularCategories,
                ...this.suggestions.bestSellingCategories,
                ...this.suggestions.bestSellingBrands,
                ...this.suggestions.bestSellers,
            ];
        },

        get firstSuggestion() {
            return this.allSuggestions[0];
        },

        get lastSuggestion() {
            return this.allSuggestions[this.allSuggestions.length - 1];
        },

        init() {
            this.hideSkeleton();

            this.$watch(
                "form.query",
                throttle((newQuery) => {
                    this.showSuggestions = true;

                    this.fetchSuggestions();
                }, 500)
            );

            this.$watch("showMiniSearch", (newValue) => {
                if (newValue) {
                    this.$refs.miniSearchInput.focus();

                    return;
                }

                this.hideSuggestions();
            });

            this.loadRecentSearches();
            this.fetchSuggestions();
        },

        loadRecentSearches() {
            const recent = localStorage.getItem('recentSearches');
            this.suggestions.recentSearches = recent ? JSON.parse(recent) : [];
        },

        saveRecentSearch(term) {
            if (!term) return;
            let recent = localStorage.getItem('recentSearches');
            recent = recent ? JSON.parse(recent) : [];
            recent = [term, ...recent.filter(t => t !== term)].slice(0, 5);
            localStorage.setItem('recentSearches', JSON.stringify(recent));
            this.suggestions.recentSearches = recent;
        },

        clearRecentSearches() {
            localStorage.removeItem('recentSearches');
            this.suggestions.recentSearches = [];
        },

        hideSkeleton() {
            setTimeout(() => {
                this.skeleton = false;
            }, 100);
        },

        getCategoryNameBySlug(slug) {
            return (
                this.categories.find((category) => category.slug === slug)
                    ?.name || ""
            );
        },

        changeCategory(category = "") {
            this.form.category = category;
            this.userSelectedCategory = Boolean(category);
            this.fetchSuggestions();
        },

        async fetchSuggestions() {
            const params = { query: this.form.query };
            if (this.userSelectedCategory && this.form.category) {
                params.category = this.form.category;
            }

            const { data } = await axios.get(`/suggestions`, {
                params,
            });

            this.clearActiveSuggestion();
            this.resetSuggestionScrollBar();

            this.suggestions.categories = data.categories;
            this.suggestions.products = data.products;
            this.suggestions.bestSellers = data.best_sellers || [];
            this.suggestions.popularCategories = data.popular_categories || [];
            this.suggestions.popularSearches = data.popular_searches || [];
            this.suggestions.bestSellingCategories = data.best_selling_categories || [];
            this.suggestions.bestSellingBrands = data.best_selling_brands || [];
            this.suggestions.remaining = data.remaining;
        },

        search() {
            if (!this.form.query) {
                return;
            }

            this.saveRecentSearch(this.form.query);

            if (this.activeSuggestion) {
                window.location.href = this.activeSuggestion.url;

                this.hideSuggestions();

                return;
            }

            if (this.userSelectedCategory && this.form.category) {
                window.location.href = `/categories/${this.form.category}/products?query=${this.form.query}`;
                return;
            }
            window.location.href = `/${window.FleetCart.productsPageSlug}?query=${this.form.query}`;
        },

        showExistingSuggestions() {
            this.showSuggestions = true;

            if (this.form.query === '' && !this.hasAnyPopularSuggestion) {
                this.fetchSuggestions();
            }
        },

        clearSuggestions() {
            this.suggestions.categories = [];
            this.suggestions.products = [];
        },

        hideSuggestions() {
            this.showSuggestions = false;

            this.clearActiveSuggestion();
            this.clearSuggestions();
        },

        isActiveSuggestion(suggestion) {
            if (!this.activeSuggestion) {
                return false;
            }

            return this.activeSuggestion.slug === suggestion.slug;
        },

        changeActiveSuggestion(suggestion) {
            this.activeSuggestion = suggestion;
        },

        clearActiveSuggestion() {
            this.activeSuggestion = null;
        },

        nextSuggestion() {
            if (!this.hasAnySuggestion) {
                return;
            }

            this.activeSuggestion =
                this.allSuggestions[this.nextSuggestionIndex()];

            if (!this.activeSuggestion) {
                this.activeSuggestion = this.firstSuggestion;
            }

            this.adjustSuggestionScrollBar();
        },

        prevSuggestion() {
            if (!this.hasAnySuggestion) {
                return;
            }

            if (this.prevSuggestionIndex() === -1) {
                this.clearActiveSuggestion();

                return;
            }

            this.activeSuggestion =
                this.allSuggestions[this.prevSuggestionIndex()];

            if (!this.activeSuggestion) {
                this.activeSuggestion = this.lastSuggestion;
            }

            this.adjustSuggestionScrollBar();
        },

        nextSuggestionIndex() {
            return this.currentSuggestionIndex() + 1;
        },

        prevSuggestionIndex() {
            return this.currentSuggestionIndex() - 1;
        },

        currentSuggestionIndex() {
            return this.allSuggestions.indexOf(this.activeSuggestion);
        },

        adjustSuggestionScrollBar() {
            const element = document.querySelector(
                `.search-suggestions-inner li[data-slug='${this.activeSuggestion.slug}']`
            );

            if (element) {
                this.$refs.searchSuggestionsInner.scrollTop =
                    element.offsetTop - 200;
            }
        },

        resetSuggestionScrollBar() {
            if (this.$refs.searchSuggestionsInner !== undefined) {
                this.$refs.searchSuggestionsInner.scrollTop = 0;
            }
        },

        hasBaseImage(product) {
            const t = product?.thumb_src;
            const p = product?.base_image?.path;
            const pm0 = product?.media?.[0];
            const pm = (typeof pm0 === 'string') ? pm0 : (pm0 && pm0.path);
            const v = product?.variant?.base_image?.path;
            return !!(t || p || pm || v);
        },

        baseImage(product) {
            const t = product?.thumb_src;
            const p = product?.base_image?.path;
            const pm0 = product?.media?.[0];
            const pm = (typeof pm0 === 'string') ? pm0 : (pm0 && pm0.path);
            const v = product?.variant?.base_image?.path;
            if (t) return t;
            if (p) return p;
            if (pm) return pm;
            if (v) return v;
            return `${window.location.origin}/build/assets/image-placeholder.png`;
        },
    })
);
