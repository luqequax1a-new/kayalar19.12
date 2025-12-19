<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox('storefront_product_page_custom_tab_2_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_product_page_custom_tab_2'), $errors, $settings) }}
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="panel-wrap">
            <div class="row">
                <div class="col-md-8">
                    {{ Form::text('translatable[storefront_product_page_custom_tab_2_title]', trans('storefront::attributes.storefront_product_page_custom_tab_2_title'), $errors, $settings) }}
                    {{ Form::wysiwyg('storefront_product_page_custom_tab_2_content', trans('storefront::attributes.storefront_product_page_custom_tab_2_content'), $errors, $settings, ['rows' => 6]) }}
                </div>
            </div>
        </div>
    </div>
</div>
