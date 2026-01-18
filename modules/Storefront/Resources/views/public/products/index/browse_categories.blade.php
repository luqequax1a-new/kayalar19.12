<div class="filter-section browse-categories-section">
    <h6 class="filter-title" @click="toggleAccordion('category')" :class="{ 'is-closed': !isAccordionOpen('category') }">{{ trans('storefront::products.category') }}</h6>
    
    <div class="browse-categories-tree" x-show="isAccordionOpen('category')" x-collapse>
        @include('storefront::public.products.index.browse_sub_categories', ['subCategories' => $categories, 'isRoot' => true])
    </div>
</div>
