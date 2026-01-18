<div
    x-data="HeaderSearch({
        categories: {{ $categories }},
        initialQuery: '{{ addslashes((string) request('query', '')) }}',
        initialCategory: ''
    })"
    class="header-search-wrap-parent"
>
    <div
        class="header-search-wrap-overlay"
        :class="{ active: showSuggestions || showMiniSearch }"
        @click="hideSuggestions(); showMiniSearch = false"
    >
    </div>

    <div
        class="header-search-wrap"
        :class="{ 'has-suggestion': showSuggestions && (hasAnySuggestion || hasAnyPopularSuggestion) }"
        @click.away="hideSuggestions"
    >
        <div class="header-search">
            <form autocomplete="on" class="search-form" @submit.prevent="search">
                <div
                    class="header-search-lg"
                    :class="{
                        'header-search-lg-background': showSuggestions
                    }"
                >
                    <input
                        type="text"
                        name="query"
                        class="form-control search-input"
                        :class="{ focused: showSuggestions }"
                        autocomplete="on"
                        placeholder="{{ trans('storefront::layouts.search_for_products') }}"
                        @focus="showExistingSuggestions"
                        @keydown.escape="hideSuggestions"
                        @keydown.down="nextSuggestion"
                        @keydown.up="prevSuggestion"
                        x-model="form.query"
                    />

                    <div
                        class="header-search-right"
                        :class="{
                            'header-search-right-background': showSuggestions
                        }"
                    >
                        <div
                            x-data="{
                                open: false,
                                selected: getCategoryNameBySlug(initialCategory)
                            }"
                            class="dropdown custom-dropdown"
                            @click.away="open = false"
                        >
                            <div
                                class="btn btn-secondary dropdown-toggle skeleton"
                                :class="{ active: open, skeleton }"
                                @click="open = !open"
                            >
                                <span x-text="selected || '{{ trans("storefront::layouts.all_categories") }}'"></span>

                                <i class="las la-angle-down"></i>
                            </div>

                            <ul
                                x-cloak
                                x-transition
                                x-show="open"
                                class="dropdown-menu"
                                :class="{ active: open }"
                            >
                                <div class="dropdown-menu-scroll">
                                    <li
                                        class="dropdown-item"
                                        :class="{
                                            active: selected === '' 
                                        }"
                                        @click="
                                            open = false;
                                            
                                            if (selected !== '') {
                                                changeCategory();
                                            }
    
                                            selected = '';
                                        "
                                    >
                                        {{ trans("storefront::layouts.all_categories") }}
                                    </li>
    
                                    <template x-for="(category, index) in categories" :key="index">
                                        <li
                                            class="dropdown-item"
                                            :class="{ active: category.name === selected }"
                                            @click="
                                                open = false;
                                                
                                                if (selected !== category.name) {
                                                    changeCategory(category.slug);
                                                }
    
                                                selected = category.name;
                                            "
                                            x-text="category.name"
                                        >
                                        </li>
                                    </template>
                                </div>
                            </ul>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary btn-search"
                            aria-label="Search Button"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z"
                                    stroke="#000000"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M22 22L20 20"
                                    stroke="#000000"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="header-search-sm" @click="showMiniSearch = true">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z"
                            stroke="#000000"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M22 22L20 20"
                            stroke="#000000"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
            </form>
        </div>

        <div class="header-search-sm-form" :class="{ active: showMiniSearch }">
            <form autocomplete="on" class="search-form" @submit.prevent="search">
                <div class="btn-close" @click="showMiniSearch = false">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M9.57 5.93005L3.5 12.0001L9.57 18.0701"
                            stroke="#292D32"
                            stroke-width="1.5"
                            stroke-miterlimit="10"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M20.4999 12H3.66992"
                            stroke="#292D32"
                            stroke-width="1.5"
                            stroke-miterlimit="10"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <input
                    x-ref="miniSearchInput"
                    type="text"
                    name="query"
                    class="form-control search-input-sm"
                    autocomplete="on"
                    placeholder="{{ trans('storefront::layouts.search_for_products') }}"
                    :value="form.query"
                    @input="form.query = $event.target.value"
                    @focus="showExistingSuggestions"
                    @keydown.escape="hideSuggestions"
                    @keydown.down="nextSuggestion"
                    @keydown.up="prevSuggestion"
                />

                <button type="submit" class="btn btn-search" aria-label="Search Button">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M11.5 21C16.7467 21 21 16.7467 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21Z"
                            stroke="#000000"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M22 22L20 20"
                            stroke="#000000"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
            </form>
        </div>

        <div
            x-cloak
            x-show="shouldShowSuggestions"
            class="search-suggestions overflow-hidden"
        >
            <div
                class="search-suggestions-inner"
                x-ref="searchSuggestionsInner"
            >
                <!-- Discovery View (Empty Query) -->
                <template x-if="form.query.trim() === ''">
                    <div class="search-discovery">
                        <template x-if="suggestions.recentSearches.length > 0">
                            <div class="recent-searches">
                                <div class="title-wrap">
                                    <h6 class="title">
                                        <i class="las la-history"></i>
                                        {{ trans('storefront::layouts.recent_searches') }}
                                    </h6>
                                    <button type="button" class="btn-clear-recent" @click="clearRecentSearches">
                                        {{ trans('storefront::layouts.clear') }}
                                    </button>
                                </div>
                                <ul class="recent-searches-list">
                                    <template x-for="term in suggestions.recentSearches">
                                        <li class="list-item" 
                                            :class="{ active: isActiveSuggestion({ slug: 'recent-' + term }) }">
                                            <a :href="`/products?query=${term}`" 
                                               class="single-item" 
                                               @mouseover="changeActiveSuggestion({ name: term, slug: 'recent-' + term })"
                                               @mouseleave="clearActiveSuggestion">
                                                <i class="las la-history"></i>
                                                <span x-text="term"></span>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <div class="discovery-grid">
                            <div class="discovery-column">
                                <template x-if="suggestions.bestSellingCategories.length > 0">
                                    <div class="category-suggestion">
                                        <h6 class="title">
                                            <i class="las la-th-list"></i>
                                            {{ trans('storefront::layouts.best_selling_categories') }}
                                        </h6>
                                        <ul class="list-inline category-suggestion-list">
                                            <template x-for="category in suggestions.bestSellingCategories" :key="category.slug">
                                                <li class="list-item"
                                                    :class="{ active: isActiveSuggestion(category) }"
                                                    @mouseover="changeActiveSuggestion(category)"
                                                    @mouseleave="clearActiveSuggestion">
                                                    <a :href="category.url" class="single-item" x-text="category.name"></a>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>

                            <div class="discovery-column">
                                <template x-if="suggestions.bestSellingBrands.length > 0">
                                    <div class="category-suggestion brands-suggestion">
                                        <h6 class="title">
                                            <i class="las la-tag"></i>
                                            {{ trans('storefront::layouts.best_selling_brands') }}
                                        </h6>
                                        <ul class="list-inline category-suggestion-list">
                                            <template x-for="brand in suggestions.bestSellingBrands" :key="brand.slug">
                                                <li class="list-item"
                                                    :class="{ active: isActiveSuggestion(brand) }"
                                                    @mouseover="changeActiveSuggestion(brand)"
                                                    @mouseleave="clearActiveSuggestion">
                                                    <a :href="brand.url" class="single-item" x-text="brand.name"></a>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <template x-if="suggestions.bestSellers.length > 0">
                            <div class="product-suggestion">
                                <h6 class="title">
                                    <i class="las la-star"></i>
                                    {{ trans('storefront::layouts.best_sellers') }}
                                </h6>
                                <ul class="list-inline product-suggestion-list">
                                    <template x-for="product in suggestions.bestSellers" :key="product.slug">
                                        <li class="list-item"
                                            :class="{ active: isActiveSuggestion(product) }"
                                            @mouseover="changeActiveSuggestion(product)"
                                            @mouseleave="clearActiveSuggestion">
                                            <a :href="product.url" class="single-item">
                                                <div class="product-image">
                                                    <img
                                                        :src="product.thumb_src || baseImage(product)"
                                                        :srcset="product.thumb_srcset || null"
                                                        sizes="60px"
                                                        :class="{ 'image-placeholder': !hasBaseImage(product) }"
                                                        :alt="product.name"
                                                        width="60"
                                                        height="60"
                                                    />
                                                </div>
                                                <div class="product-info">
                                                    <div class="product-info-top">
                                                        <h6 class="product-name" x-html="product.name"></h6>
                                                    </div>
                                                    <div class="product-price" x-html="product.formatted_price"></div>
                                                </div>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <!-- View All Products Link -->
                        <div class="view-all-products">
                            <a href="{{ route('products.index') }}" class="view-all-link">
                                <span>Tüm Ürünleri Gör</span>
                                <i class="las la-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </template>

                <!-- Search Results (Query present) -->
                <template x-if="form.query !== ''">
                    <div class="search-results-view">
                        <template x-if="hasAnyCategorySuggestion">
                            <div class="category-suggestion">
                                <h6 class="title">
                                    {{ trans("storefront::layouts.category_suggestions") }}
                                </h6>
        
                                <ul class="list-inline category-suggestion-list">
                                    <template
                                        x-for="category in suggestions.categories"
                                        :key="category.slug"
                                    >
                                        <li
                                            class="list-item"
                                            :class="{
                                                active: isActiveSuggestion(category),
                                            }"
                                            :data-slug="category.slug"
                                            @mouseover="changeActiveSuggestion(category)"
                                            @mouseleave="clearActiveSuggestion"
                                        >
                                            <a
                                                :href="category.url"
                                                class="single-item"
                                                x-text="category.name"
                                                @click="hideSuggestions"
                                            >
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>
        
                        <div class="product-suggestion">
                            <h6 class="title">
                                {{ trans("storefront::layouts.product_suggestions") }}
                            </h6>
        
                            <ul class="list-inline product-suggestion-list">
                                <template
                                    x-for="product in suggestions.products"
                                    :key="product.slug"
                                >
                                    <li
                                        class="list-item"
                                        :class="{
                                            active: isActiveSuggestion(product),
                                        }"
                                        :data-slug="product.slug"
                                        @mouseover="changeActiveSuggestion(product)"
                                        @mouseleave="clearActiveSuggestion"
                                    >
                                        <a
                                            :href="product.url"
                                            class="single-item"
                                            @click="hideSuggestions"
                                        >
                                            <div class="product-image">
                                                <img
                                                    :src="(product.thumb_src || baseImage(product))"
                                                    :srcset="(product.thumb_srcset || null)"
                                                    sizes="60px"
                                                    :class="{
                                                        'image-placeholder': !hasBaseImage(product),
                                                    }"
                                                    :alt="product.name"
                                                    width="60"
                                                    height="60"
                                                />
                                            </div>
        
                                            <div class="product-info">
                                                <div class="product-info-top">
                                                    <h6 class="product-name" x-html="product.name"></h6>
        
                                                    <template x-if="product.is_out_of_stock">
                                                        <ul class="list-inline product-badge">
                                                            <li class="badge badge-danger">
                                                                {{ trans("storefront::product_card.out_of_stock") }}
                                                            </li>
                                                        </ul>
                                                    </template>
                                                </div>
        
                                                <div
                                                    class="product-price"
                                                    x-html="product.formatted_price"
                                                ></div>
                                            </div>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </template>
            </div>

            <template x-if="suggestions.remaining !== 0">
                <a
                    :href="moreResultsUrl"
                    class="more-results"
                    x-text="
                        trans('storefront::layouts.more_results', {
                            count: suggestions.remaining
                        })
                    "
                    @click="hideSuggestions"
                >
                </a>
            </template>
        </div>
    </div>
    <style>
        .search-discovery {
            padding: 0;
            background: #fff;
        }
        .discovery-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 25px !important;
            padding: 15px 20px !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .discovery-column {
            min-width: 0;
        }
        .search-discovery .title {
            background: transparent !important;
            padding: 0 0 10px 0 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            margin-bottom: 12px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            display: flex !important;
            align-items: center;
            gap: 8px;
            white-space: nowrap !important;
            overflow: hidden !important;
        }
        .search-discovery .title::after, 
        .search-discovery .title::before {
            display: none !important;
        }
        .search-discovery .title i {
            width: 26px;
            height: 26px;
            flex-shrink: 0;
            display: flex !important;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #64748b !important;
            border-radius: 6px;
            font-size: 14px !important;
            transition: all 0.2s;
        }
        
        /* Highlight Category Icon */
        .category-suggestion .title i {
            background: rgba(59, 130, 246, 0.08); 
            color: #3b82f6 !important;
        }
        /* Highlight Brands Icon */
        .brands-suggestion .title i {
            background: rgba(139, 92, 246, 0.08);
            color: #8b5cf6 !important;
        }
        /* Highlight Recent Icon */
        .recent-searches .title i {
            background: rgba(100, 116, 139, 0.08);
            color: #64748b !important;
        }
        /* Highlight Best Sellers Icon */
        .product-suggestion .title i {
            background: rgba(245, 158, 11, 0.08);
            color: #f59e0b !important;
        }

        .search-discovery .category-suggestion-list {
            padding: 0 !important;
            list-style: none !important;
            margin: 0 !important;
        }
        .search-discovery .category-suggestion-list .single-item {
            padding: 7px 0 !important;
            font-size: 14px !important;
            color: #475569 !important;
            display: block !important;
            transition: all 0.2s;
            line-height: 1.5 !important;
        }
        .search-discovery .category-suggestion-list .single-item:hover {
            color: #3b82f6 !important;
            padding-left: 6px !important;
        }
        
        /* Recent Searches section style */
        .search-discovery .recent-searches {
            border-bottom: 1px solid #f1f5f9;
        }
        .search-discovery .recent-searches .title-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px 12px 20px;
        }
        .search-discovery .recent-searches .title {
            border-bottom: none !important;
            margin-bottom: 0 !important;
            padding: 0 !important;
        }
        .search-discovery .btn-clear-recent {
            font-size: 12px;
            color: #ef4444;
            background: transparent;
            border: none;
            padding: 0;
            cursor: pointer;
            font-weight: 500;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .search-discovery .btn-clear-recent:hover {
            text-decoration: underline;
            color: #dc2626;
        }
        .search-discovery .recent-searches-list {
            padding: 0 0 12px 0 !important;
            list-style: none !important;
            margin: 0 !important;
        }
        .search-discovery .recent-searches .single-item {
            padding: 7px 20px !important;
            font-size: 14px !important;
            display: flex !important;
            align-items: center;
            gap: 10px;
            color: #475569 !important;
            transition: all 0.2s;
        }
        .search-discovery .recent-searches .single-item i {
            background: transparent !important;
            width: auto;
            height: auto;
            font-size: 14px !important;
            color: #94a3b8 !important;
        }
        .search-discovery .recent-searches .single-item:hover {
            background: #f8fafc;
            color: #3b82f6 !important;
        }
        .search-discovery .recent-searches .single-item:hover i {
            color: #3b82f6 !important;
        }

        /* Best Sellers Product section - FIXED SPACING & PRODUCT NAMES */
        .search-discovery .product-suggestion {
            padding: 10px 15px 10px 20px !important;
            border-top: 1px solid #f1f5f9;
            margin-top: 0 !important;
        }
        .search-discovery .product-suggestion .title {
            margin-bottom: 10px !important;
            padding-top: 5px !important;
        }
        .search-discovery .product-suggestion-list {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        /* Enhanced product items - REDUCED LEFT PADDING */
        .search-discovery .product-suggestion-list .list-item {
            margin: 0 !important;
            padding: 0 !important;
        }
        .search-discovery .product-suggestion-list .single-item {
            border-radius: 8px;
            transition: all 0.2s;
            padding: 8px 10px 8px 5px !important;
        }
        .search-discovery .product-suggestion-list .single-item:hover {
            background: #f8fafc !important;
        }
        .search-discovery .product-suggestion-list .product-image {
            border: 1px solid #f1f5f9;
            border-radius: 6px;
            overflow: hidden;
        }
        
        /* FIX: Allow product names to wrap instead of truncating */
        .search-discovery .product-suggestion-list .product-name {
            font-weight: 500 !important;
            color: #334155 !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            line-height: 1.4 !important;
            max-height: 2.8em !important;
            font-size: 13px !important;
        }
        .search-discovery .product-suggestion-list .single-item:hover .product-name {
            color: #3b82f6 !important;
        }

        /* View All Products Link */
        .view-all-products {
            padding: 15px 20px;
            border-top: 1px solid #f1f5f9;
            background: #fafbfc;
        }
        .view-all-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }
        .view-all-link:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
        }
        .view-all-link i {
            font-size: 16px;
            transition: transform 0.3s ease;
        }
        .view-all-link:hover i {
            transform: translateX(3px);
        }

        /* Responsive: Hide icons on smaller screens */
        @media screen and (max-width: 768px) {
            .search-discovery .title i {
                display: none !important;
            }
            .search-discovery .title {
                gap: 0 !important;
                font-size: 11px !important;
            }
        }

        /* Responsive: Stack grid on mobile */
        @media screen and (max-width: 991px) {
            .discovery-grid {
                grid-template-columns: 1fr !important;
                gap: 20px !important;
                padding: 12px 15px !important;
            }
            .search-discovery .product-suggestion {
                padding: 8px 12px !important;
            }
            .search-discovery .recent-searches .title-wrap {
                padding: 12px 15px 10px 15px;
            }
            .view-all-products {
                padding: 12px 15px;
            }
            .view-all-link {
                padding: 10px 16px;
                font-size: 13px;
            }
        }
    </style>
</div>
