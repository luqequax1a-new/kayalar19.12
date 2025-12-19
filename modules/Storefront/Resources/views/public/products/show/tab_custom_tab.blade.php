<div id="custom_tab" class="tab-pane description custom-page-content">
    <div
        x-ref="customTabContent"
        class="content"
        style="min-height: 260px;"
        :class="{
            active: showCustomTabContent,
            'less-content': !showCustomTabMore
        }"
    >
        {!! setting('storefront_product_page_custom_tab_content') !!}
    </div>

    <button
        x-cloak
        type="button"
        class="btn btn-default btn-show-more"
        :class="{ 'show': showCustomTabMore }"
        @click="toggleCustomTabContent"
        x-text="
            showCustomTabContent ?
            '{{ trans('storefront::product.show_less') }}' :
            '{{ trans('storefront::product.show_more') }}'
        "
    >
    </button>
</div>
