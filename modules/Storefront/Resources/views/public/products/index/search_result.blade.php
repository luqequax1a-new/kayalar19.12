<div class="search-result">
    <div class="search-result-top">
        <div class="content-left">
            <template x-if="queryParams.query">
                <h1 style="color: #000;">
                    {{ trans('storefront::products.search_results_for') }}
                    
                    <span x-text="queryParams.query"></span>
                </h1>
            </template>

            <template x-if="!queryParams.query && queryParams.brand">
                <h1 style="color: #000;" x-text="initialBrandName"></h1>
            </template>

            <template x-if="!queryParams.query && !queryParams.brand && queryParams.category">
                <h1 style="color: #000;" x-text="categoryName"></h1>
            </template>
            
            <template x-if="!queryParams.query && !queryParams.brand && !queryParams.category && queryParams.tag">
                <h1 style="color: #000;" x-text="initialTagName"></h1>
            </template>
            
            <template x-if="!queryParams.query && !queryParams.brand && !queryParams.category && !queryParams.tag">
                <h1 style="color: #000;">{{ trans('storefront::products.shop') }}</h1>
            </template>
        </div>

        <div class="content-right">
            <div class="sorting-bar">
                <div class="mobile-view-filter" @click.stop="$store.layout.openSidebarFilter()">
                    <i class="las la-sliders-h"></i>
    
                    {{ trans('storefront::products.filters') }}
                </div>

                <div class="view-type">
                    <button
                        type="submit"
                        class="btn btn-grid-view"
                        :class="{ active: viewMode === 'grid' }"
                        title="{{ trans('storefront::products.grid_view') }}"
                        @click="viewMode = 'grid'"
                    >
                        <i class="las la-th-large"></i>
                    </button>

                    <button
                        type="submit"
                        class="btn btn-list-view"
                        :class="{ active: viewMode === 'list' }"
                        title="{{ trans('storefront::products.list_view') }}"
                        @click="viewMode = 'list'"
                    >
                        <i class="las la-list"></i>
                    </button>
                </div>

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

                    <div
                        x-data="CustomPageSelect"
                        class="dropdown custom-dropdown"
                        @click.away="hideDropdown"
                    >
                        <div
                            class="btn btn-secondary dropdown-toggle"
                            :class="activeClass"
                            @click="toggleOpen"
                        >
                            <span x-text="selected">{{ request('perPage', 20) }}</span>
    
                            <i class="las la-angle-down"></i>
                        </div>
    
                        <ul
                            x-cloak
                            x-show="open"
                            x-transition
                            class="dropdown-menu"
                            :class="activeClass"
                        >
                            @foreach (trans('storefront::products.per_page_options') as $key => $value)
                                <li
                                    class="dropdown-item"
                                    data-value="{{ $value }}"
                                    @click="changeValue"
                                >
                                    {{ $value }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        class="search-result-middle"
        :class="{
            empty: phase === 'ready' && total === 0
        }"
    >  
        <div id="productsMount" x-ref="productsMount" x-html="productsHtml"></div>
        
        <template x-if="phase === 'ready' && total === 0">
            <div class="empty-message">
                @include('storefront::public.products.index.empty_results_logo')

                <h2>{{ trans('storefront::products.no_products_found') }}</h2>
            </div>
        </template>
    </div>

    <template x-if="phase === 'ready' && total > 0">
        <div class="search-result-bottom">
            <span class="showing-results" x-text="showingText"></span>

            <div id="paginationMount" x-ref="paginationMount" x-html="paginationHtml"></div>
        </div>
    </template>

    <section
        class="category-description mt-5 mb-4"
        x-show="categoryDescriptionHtml"
        x-html="categoryDescriptionHtml"
    ></section>

    <section
        class="fc-faq-section mt-5"
        x-data="{ open: null }"
        x-show="categoryFaqItems.length"
    >
        <h2
            class="faq-title mb-3"
            x-text="(categoryName || '{{ addslashes($category->name ?? '') }}') + ' Hakkında Sıkça Sorulan Sorular'"
        ></h2>

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
    </section>
</div>
