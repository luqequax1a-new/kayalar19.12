<div class="search-result">
    <style>
        .listing-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 24px;
        }

        .listing-title-wrap {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 12px;
            min-width: 0;
        }

        .listing-title {
            margin: 0;
            font-size: 28px;
            line-height: 1.2;
            font-weight: 700;
            color: #0E1E3E;
            letter-spacing: -0.4px;
            max-width: 100%;
            word-break: break-word;
        }

        .listing-count {
            font-size: 15px;
            line-height: 1;
            color: #0E1E3E;
            font-weight: 400;
            white-space: nowrap;
        }

        .mobile-action-bar {
            display: none;
            align-items: center;
            width: 100%;
            background: #F3F4F6;
            border: 1px solid rgba(14, 30, 62, 0.08);
            border-radius: 12px;
            overflow: visible;
            position: relative;
            z-index: 10;
        }

        .mobile-action-item {
            appearance: none;
            border: 0;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            width: 50%;
            font-size: 14px;
            font-weight: 600;
            color: #0E1E3E;
        }

        .mobile-action-divider {
            width: 1px;
            height: 22px;
            background: rgba(14, 30, 62, 0.14);
            flex: 0 0 auto;
        }

        .mobile-action-item i {
            font-size: 18px;
        }

        .mobile-action-bar .custom-dropdown {
            width: 50%;
            position: relative;
            z-index: 11;
        }

        .mobile-action-bar .dropdown-menu {
            z-index: 12;
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 8px);
        }

        .mobile-action-bar .custom-dropdown .btn {
            width: 100%;
            border: 0;
            background: transparent;
            border-radius: 0;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #0E1E3E;
        }

        .mobile-action-bar .custom-dropdown .btn i {
            font-size: 18px;
        }

        .listing-actions {
            display: none;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .listing-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(14, 30, 62, 0.12);
            background: #F8FAFC;
            color: #0E1E3E;
            font-weight: 600;
            font-size: 14px;
            transition: background 120ms ease, border-color 120ms ease;
        }

        .listing-action-btn:hover {
            background: #F1F5F9;
            border-color: rgba(14, 30, 62, 0.18);
        }

        .listing-action-btn i {
            font-size: 18px;
        }

        .listing-actions .custom-dropdown .btn {
            border-radius: 10px;
        }

        @media (max-width: 576px) {
            .listing-header {
                flex-direction: column;
                align-items: stretch;
                margin-bottom: 8px;
            }

            .listing-actions {
                justify-content: flex-start;
            }

            .listing-title {
                font-size: 22px;
            }

            .listing-count {
                font-size: 13px;
            }

            .mobile-action-bar {
                display: flex;
                margin-top: 8px;
                margin-bottom: 16px;
            }

            .listing-actions {
                display: none;
            }
        }
    </style>

    <div class="search-result-top">
        <div class="listing-header">
            <div class="listing-title-wrap">
                <template x-if="queryParams.query">
                    <h1 class="listing-title">
                        {{ trans('storefront::products.search_results_for') }}
                        <span x-text="queryParams.query"></span>
                    </h1>
                </template>

                <template x-if="!queryParams.query">
                    <h1 class="listing-title" x-text="categoryName || initialBrandName || initialTagName || '{{ addslashes(setting('products_page_name', trans('storefront::products.shop'))) }}'"></h1>
                </template>

                <span class="listing-count" x-show="phase === 'ready'" x-text="'(' + (total || 0) + ' ürün)'"></span>
            </div>

            <div class="listing-actions">
                <div class="mobile-view-filter-dropdown">
                    <div
                        x-data="CustomFilterSelect"
                        class="dropdown custom-dropdown"
                        @click.away="hideDropdown"
                    >
                        <div
                            class="btn btn-secondary dropdown-toggle"
                            :class="activeClass"
                            @click="toggleOpen"
                        >
                            <span x-text="selectedValueText">{{ trans('storefront::products.sort_options')[request('sort', 'latest')] ?? trans('storefront::products.sort_options')['latest'] }}</span>

                            <i class="las la-angle-down"></i>
                        </div>
                        
                        <ul
                            x-cloak
                            x-show="open"
                            x-transition
                            class="dropdown-menu"
                            :class="activeClass"
                        >
                            <div class="dropdown-menu-scroll">
                                @foreach (trans('storefront::products.sort_options') as $key => $value)
                                    <li
                                        class="dropdown-item"
                                        data-value="{{ $key }}"
                                        @click="changeValue"
                                    >
                                        {{ $value }}
                                    </li>
                                @endforeach
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mobile-action-bar d-sm-flex d-md-none" aria-label="Listing Controls">
        <button type="button" class="mobile-action-item" @click.stop="$store.layout.openSidebarFilter()">
            <i class="las la-sliders-h"></i>
            <span>Filtrele</span>
        </button>

        <div class="mobile-action-divider"></div>

        <div x-data="CustomFilterSelect" class="dropdown custom-dropdown" @click.away="hideDropdown">
            <div class="btn dropdown-toggle" :class="activeClass" @click="toggleOpen">
                <i class="las la-sort"></i>
                <span x-text="selected === 'latest' ? 'Sırala' : selectedValueText">{{ trans('storefront::products.sort_options')[request('sort', 'latest')] ?? trans('storefront::products.sort_options')['latest'] }}</span>
                <i class="las la-angle-down"></i>
            </div>

            <ul
                x-cloak
                x-show="open"
                x-transition
                class="dropdown-menu"
                :class="activeClass"
            >
                <div class="dropdown-menu-scroll">
                    @foreach (trans('storefront::products.sort_options') as $key => $value)
                        <li
                            class="dropdown-item"
                            data-value="{{ $key }}"
                            @click="changeValue"
                        >
                            {{ $value }}
                        </li>
                    @endforeach
                </div>
            </ul>
        </div>
    </div>

    <div
        class="search-result-middle"
        :class="{
            empty: phase === 'ready' && total === 0,
            fetching: fetchingProducts
        }"
    >  
        <!-- Sleek Linear Loading Bar (Ikas Style) -->
        <div class="ikas-loading-bar-wrap" x-show="fetchingProducts && hasEverRenderedProducts">
            <div class="ikas-loading-bar"></div>
        </div>

        <!-- Loading Overlay (Visible only during fetch - primarily for mobile now) -->
        <template x-if="fetchingProducts && hasEverRenderedProducts">
            <div class="fetching-overlay">
                <div class="loading-spinner-wrapper">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">{{ trans('storefront::products.loading') }}</div>
                </div>
            </div>
        </template>

        <!-- Initial Skeleton Loading (Only for first load) -->
        <template x-if="fetchingProducts && !hasEverRenderedProducts">
            <div class="products-loading-skeleton">
                <div class="grid-view-products">
                    <template x-for="i in 8">
                        <div class="grid-view-products-item">
                            <div class="skeleton-product-card">
                                <div class="skeleton-image"></div>
                                <div class="skeleton-content">
                                    <div class="skeleton-line title"></div>
                                    <div class="skeleton-line price"></div>
                                    <div class="skeleton-line button"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <div id="productsMount" x-ref="productsMount" x-html="productsHtml"></div>
        
        <template x-if="phase === 'ready' && total === 0 && !fetchingProducts">
            <div class="empty-message-wrap">
                <div class="empty-icon-box">
                    @include('storefront::public.products.index.empty_results_logo')
                </div>
                
                <h2 class="empty-title">{{ trans('storefront::products.no_products_found') }}</h2>
                <p class="empty-subtitle">Kriterlerinize uygun ürün bulamadık, filtreleri temizleyerek daha fazla sonuca ulaşabilirsiniz.</p>
                
                <button type="button" @click="resetFilters" class="btn btn-primary btn-clear-filters">
                    Filtreleri Temizle
                </button>
            </div>
        </template>
    </div>

    <template x-if="phase === 'ready' && total > 0">
        <div class="search-result-bottom">
            <div id="paginationMount" x-ref="paginationMount" x-html="paginationHtml"></div>
        </div>
    </template>

    <section
        class="category-description mt-5 mb-4"
        x-show="categoryDescriptionHtml"
        x-html="categoryDescriptionHtml"
    >
        @if (isset($initialCategoryData['description_html']) && $initialCategoryData['description_html'])
            {!! $initialCategoryData['description_html'] !!}
        @endif
    </section>

    <section
        class="fc-faq-section mt-5"
        x-data="{ open: null }"
        x-show="categoryFaqItems.length"
    >
        <h2
            class="faq-title mb-3"
            x-text="(categoryName || brandName || '{{ addslashes($categoryName ?? $brandName ?? '') }}') + ' Hakkında Sıkça Sorulan Sorular'"
        >
            {{ $categoryName ?? $brandName ?? ($initialCategoryData['name'] ?? '') }} Hakkında Sıkça Sorulan Sorular
        </h2>

        <template x-if="phase === 'boot'">
            <div class="faq-items-ssr">
                @if (isset($initialCategoryData['faq_items']) && is_array($initialCategoryData['faq_items']))
                    @foreach ($initialCategoryData['faq_items'] as $item)
                        <div class="faq-item">
                            <button type="button" class="faq-question">
                                <span class="faq-question-text">{{ $item['question'] ?? '' }}</span>
                                <span class="icon">❯</span>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
        </template>

        <template x-if="phase !== 'boot'">
            <template x-for="(item, index) in categoryFaqItems" :key="index">
                <div class="faq-item">
                    <button
                        type="button"
                        class="faq-question"
                        @click="open = open === index ? null : index"
                    >
                        <span class="faq-question-text" x-text="item.question"></span>
                        <span class="icon" :class="{ 'rotate': open === index }">
                            ❯
                        </span>
                    </button>

                    <div
                        class="faq-answer"
                        x-show="open === index"
                        x-transition
                    >
                        <p class="mb-0" x-text="item.answer"></p>
                    </div>
                </div>
            </template>
        </template>
    </section>
</div>
