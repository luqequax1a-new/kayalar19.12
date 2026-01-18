@if (isset($categories) && $categories->isNotEmpty())
    @include('storefront::public.products.index.browse_categories')
@endif

<!-- Price Filter Section -->
<div class="filter-section filter-price-section">
    <h6 class="filter-title" @click="toggleAccordion('price')" :class="{ 'is-closed': !isAccordionOpen('price') }">{{ trans('storefront::products.price') }}</h6>

    <div class="filter-price-content" x-show="isAccordionOpen('price')" x-collapse>
        <form @submit.prevent="fetchProducts">
            <div class="price-input-wrapper">
                <div class="price-field">
                    <span>{{ setting('currency') }}</span>
                    <input
                        type="number"
                        id="price-from"
                        class="form-control"
                        :value="queryParams.fromPrice"
                        @change="updatePriceRange($event.target.value, null)"
                        placeholder="Min"
                    >
                </div>

                <div class="price-separator">-</div>

                <div class="price-field">
                    <span>{{ setting('currency') }}</span>
                    <input
                        type="number"
                        id="price-to"
                        class="form-control"
                        :value="queryParams.toPrice"
                        @change="updatePriceRange(null, $event.target.value)"
                        placeholder="Max"
                    >
                </div>
            </div>

            <div class="price-slider-wrap">
                <div x-ref="priceRange" class="price-slider"></div>
            </div>
        </form>
    </div>
</div>

<!-- Brands Filter Section -->
<div class="filter-section" x-show="brands && brands.length > 0">
    <h6 class="filter-title" @click="toggleAccordion('brands')" :class="{ 'is-closed': !isAccordionOpen('brands') }">{{ trans('storefront::products.brand') }}</h6>

    <div class="filter-list custom-scrollbar" x-show="isAccordionOpen('brands')" x-collapse>
        <template x-for="brand in brands" :key="brand.id">
            <label :for="'brand-' + brand.id" class="filter-checkbox-item" :class="{ 'active': queryParams.brand && queryParams.brand.includes(brand.slug) }">
                <div class="checkbox-box">
                    <input
                        type="checkbox"
                        :id="'brand-' + brand.id"
                        :checked="queryParams.brand && queryParams.brand.includes(brand.slug)"
                        @change="toggleBrandFilter(brand.slug)"
                    >
                    <span class="checkmark"></span>
                </div>
                <span class="filter-label-text" x-text="brand.name"></span>
            </label>
        </template>
    </div>
</div>

<!-- Rating Filter Section -->
<div class="filter-section">
    <h6 class="filter-title" @click="toggleAccordion('rating')" :class="{ 'is-closed': !isAccordionOpen('rating') }">{{ trans('storefront::products.rating') }}</h6>

    <div class="filter-list" x-show="isAccordionOpen('rating')" x-collapse>
        <template x-for="r in [4, 3, 2, 1]" :key="r">
            <label :for="'rating-' + r" class="filter-checkbox-item" :class="{ 'active': queryParams.rating == r }">
                <div class="checkbox-box">
                    <input
                        type="radio"
                        name="rating"
                        :id="'rating-' + r"
                        :checked="queryParams.rating == r"
                        @click="toggleRatingFilter(r)"
                    >
                    <span class="checkmark"></span>
                </div>
                <div class="rating-stars">
                    <template x-for="i in 5">
                        <i class="las la-star" :class="i <= r ? 'active' : ''"></i>
                    </template>
                    <span class="and-up-text">{{ trans('storefront::products.and_up') }}</span>
                </div>
            </label>
        </template>


    </div>
</div>

<!-- Attribute Filters -->
<template x-for="attribute in attributeFilters" :key="attribute.id">
    <div class="filter-section">
        <h6 class="filter-title" @click="toggleAccordion('attr_' + attribute.id)" :class="{ 'is-closed': !isAccordionOpen('attr_' + attribute.id) }" x-text="attribute.name"></h6>

        <div x-show="isAccordionOpen('attr_' + attribute.id)" x-collapse>
            <template x-if="attribute.filterable_type === 'range'">
                <div class="filter-price-content attribute-range-content">
                    <form @submit.prevent="fetchProducts">
                        <div class="price-input-wrapper">
                            <div class="price-field">
                                <input
                                    type="number"
                                    class="form-control"
                                    :value="queryParams.attribute[attribute.slug] ? queryParams.attribute[attribute.slug].min : attribute.min"
                                    @change="updateAttributeRange(attribute.slug, $event.target.value, (queryParams.attribute[attribute.slug] ? queryParams.attribute[attribute.slug].max : attribute.max))"
                                    :placeholder="attribute.min"
                                >
                            </div>

                            <div class="price-separator">-</div>

                            <div class="price-field">
                                <input
                                    type="number"
                                    class="form-control"
                                    :value="queryParams.attribute[attribute.slug] ? queryParams.attribute[attribute.slug].max : attribute.max"
                                    @change="updateAttributeRange(attribute.slug, (queryParams.attribute[attribute.slug] ? queryParams.attribute[attribute.slug].min : attribute.min), $event.target.value)"
                                    :placeholder="attribute.max"
                                >
                            </div>
                        </div>

                        <div class="price-slider-wrap">
                            <div 
                                class="price-slider attribute-slider" 
                                :data-attribute-slider="attribute.slug"
                                x-init="initAttributeRangeFilter($el, attribute)"
                            ></div>
                        </div>
                    </form>
                </div>
            </template>

            <template x-if="attribute.filterable_type === 'dropdown'">
                <div class="filter-dropdown-wrap">
                    <select 
                        class="form-control custom-select-filter" 
                        @change="toggleAttributeFilter(attribute.slug, $event.target.value)"
                    >
                        <option value="">{{ trans('storefront::products.all') }}</option>
                        <template x-for="value in attribute.values" :key="value.id">
                            <option :value="value.value" :selected="isFilteredByAttribute(attribute.slug, value.value)" x-text="value.value"></option>
                        </template>
                    </select>
                </div>
            </template>

            <template x-if="attribute.filterable_type === 'color'">
                <div class="filter-colors-wrap">
                    <template x-for="value in attribute.values" :key="value.id">
                        <div 
                            class="color-swatch-item" 
                            :class="{ 'active': isFilteredByAttribute(attribute.slug, value.value) }"
                            :title="value.value"
                            @click="toggleAttributeFilter(attribute.slug, value.value)"
                        >
                            <span :style="'background-color: ' + value.color"></span>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="attribute.filterable_type === 'checkbox' || attribute.filterable_type === 'radio'">
                <div class="filter-list custom-scrollbar">
                    <template x-for="value in attribute.values" :key="value.id">
                        <label :for="'attribute-' + value.id" class="filter-checkbox-item" :class="{ 'active': isFilteredByAttribute(attribute.slug, value.value) }">
                            <div class="checkbox-box">
                                <input
                                    :type="attribute.filterable_type === 'radio' ? 'radio' : 'checkbox'"
                                    :name="attribute.slug"
                                    :id="'attribute-' + value.id"
                                    :checked="isFilteredByAttribute(attribute.slug, value.value)"
                                    @change="toggleAttributeFilter(attribute.slug, value.value)"
                                >
                                <span class="checkmark" :class="{ 'radio': attribute.filterable_type === 'radio' }"></span>
                            </div>
                            <span class="filter-label-text" x-text="value.value"></span>
                        </label>
                    </template>
                </div>
            </template>
        </div>
    </div>
</template>

<div class="ikas-clear-wrap">
    <button type="button" @click="resetFilters" class="btn-ikas-clear">
        Filtreleri temizle
    </button>
</div>
