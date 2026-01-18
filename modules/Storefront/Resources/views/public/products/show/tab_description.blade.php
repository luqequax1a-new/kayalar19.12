<div id="description" class="tab-pane description custom-page-content active">
    <div 
        x-data="ShowMore(450)" 
        class="show-more-wrapper"
        :class="{ 'is-expanded': expanded, 'has-overflow': showButton }"
    >
        <div class="show-more-content custom-page-content" x-ref="content">
            {!! $product->description !!}
        </div>
        
        <div class="show-more-footer" x-show="showButton">
            <button 
                type="button" 
                class="btn-show-more" 
                @click="toggle"
                x-text="expanded ? '{{ trans('storefront::product.show_less') }}' : '{{ trans('storefront::product.show_more') }}'"
            >
                {{ trans('storefront::product.show_more') }}
            </button>
        </div>
    </div>
</div>
