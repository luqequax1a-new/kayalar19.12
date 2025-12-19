<div id="custom_tab_2" class="tab-pane description custom-page-content">
    <div
        x-ref="customTab2Content"
        class="content"
        style="min-height: 260px;"
        :class="{
            active: showCustomTab2Content,
            'less-content': !showCustomTab2More
        }"
    >
        {!! setting('storefront_product_page_custom_tab_2_content') !!}
    </div>

    <button
        x-cloak
        type="button"
        class="btn btn-default btn-show-more"
        :class="{ 'show': showCustomTab2More }"
        @click="toggleCustomTab2Content"
        x-text="
            showCustomTab2Content ?
            '{{ trans('storefront::product.show_less') }}' :
            '{{ trans('storefront::product.show_more') }}'
        "
    >
    </button>
</div>
