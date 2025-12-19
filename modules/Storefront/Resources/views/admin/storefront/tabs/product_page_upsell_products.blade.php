<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('storefront_product_page_upsell_products_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_product_page_upsell_products_section'), $errors, $settings) }}

        {{ Form::text('translatable[storefront_product_page_upsell_products_title]', trans('storefront::attributes.title'), $errors, $settings) }}
    </div>
</div>
