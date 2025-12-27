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
            remaining: 0,
        },

        get shouldShowSuggestions() {
            if (!this.showSuggestions) {
                return false;
            }

            return this.hasAnySuggestion;
        },

        get moreResultsUrl() {
            if (this.userSelectedCategory && this.form.category) {
                return `/categories/${this.form.category}/products?query=${this.form.query}&viewMode=grid`;
            }
            return `/products?query=${this.form.query}&viewMode=grid`;
        },

        get hasAnySuggestion() {
            return this.suggestions.products.length !== 0;
        },

        get hasAnyCategorySuggestion() {
            return this.suggestions.categories.length !== 0;
        },

        get allSuggestions() {
            return [
                ...this.suggestions.categories,
                ...this.suggestions.products,
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
                    if (newQuery === "") {
                        this.clearSuggestions();
                    } else {
                        this.showSuggestions = true;

                        this.fetchSuggestions();
                    }
                }, 1000)
            );

            this.$watch("showMiniSearch", (newValue) => {
                if (newValue) {
                    this.$refs.miniSearchInput.focus();

                    return;
                }

                this.hideSuggestions();
            });

            this.fetchSuggestions();
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
            if (this.form.query === "") return;

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
            this.suggestions.remaining = data.remaining;
        },

        search() {
            if (!this.form.query) {
                return;
            }

            if (this.activeSuggestion) {
                window.location.href = this.activeSuggestion.url;

                this.hideSuggestions();

                return;
            }

            if (this.userSelectedCategory && this.form.category) {
                window.location.href = `/categories/${this.form.category}/products?query=${this.form.query}&viewMode=grid`;
                return;
            }
            window.location.href = `/products?query=${this.form.query}&viewMode=grid`;
        },

        showExistingSuggestions() {
            this.showSuggestions = true;
        },

        clearSuggestions() {
            this.suggestions.categories = [];
            this.suggestions.products = [];
        },

        hideSuggestions() {
            this.showSuggestions = false;

            this.clearActiveSuggestion();
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
