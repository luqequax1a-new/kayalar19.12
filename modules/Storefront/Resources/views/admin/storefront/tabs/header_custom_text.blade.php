<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('storefront_header_custom_text_enabled', trans('storefront::attributes.storefront_header_custom_text_enabled'), trans('storefront::attributes.enabled'), $errors, $settings) }}

        {{ Form::textarea('storefront_header_custom_text_content', trans('storefront::attributes.storefront_header_custom_text_content'), $errors, $settings, ['rows' => 6]) }}

        {{ Form::text('storefront_header_custom_text_font_size', trans('storefront::attributes.storefront_header_custom_text_font_size'), $errors, $settings) }}

        {{ Form::color('storefront_header_custom_text_bg_color', trans('storefront::attributes.storefront_header_custom_text_bg_color'), $errors, $settings) }}

        {{ Form::color('storefront_header_custom_text_text_color', trans('storefront::attributes.storefront_header_custom_text_text_color'), $errors, $settings) }}

        {{ Form::checkbox('storefront_header_custom_text_show_mobile', trans('storefront::attributes.storefront_header_custom_text_show_mobile'), trans('storefront::attributes.enabled'), $errors, $settings) }}
        {{ Form::checkbox('storefront_header_custom_text_show_tablet', trans('storefront::attributes.storefront_header_custom_text_show_tablet'), trans('storefront::attributes.enabled'), $errors, $settings) }}
        {{ Form::checkbox('storefront_header_custom_text_show_desktop', trans('storefront::attributes.storefront_header_custom_text_show_desktop'), trans('storefront::attributes.enabled'), $errors, $settings) }}
    </div>
</div>
