<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox('storefront_product_page_custom_html_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_product_page_custom_html'), $errors, $settings) }}
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="panel-wrap">
            <div class="row">
                <div class="col-md-8">
                    {{ Form::textarea('storefront_product_page_custom_html_content', trans('storefront::attributes.storefront_product_page_custom_html_content'), $errors, $settings, ['rows' => 8]) }}
                </div>
            </div>
        </div>
    </div>
</div>
