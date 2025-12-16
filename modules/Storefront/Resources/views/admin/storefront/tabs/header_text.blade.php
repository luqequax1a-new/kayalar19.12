<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('storefront_announcement_bar_enabled', trans('storefront::attributes.storefront_announcement_bar_enabled'), trans('storefront::attributes.enabled'), $errors, $settings) }}

        {{ Form::textarea('storefront_announcement_bar_items', trans('storefront::attributes.storefront_announcement_bar_items'), $errors, $settings, ['rows' => 6]) }}

        {{ Form::text('storefront_announcement_bar_separator', trans('storefront::attributes.storefront_announcement_bar_separator'), $errors, $settings) }}

        {{ Form::text('storefront_announcement_bar_font_size', trans('storefront::attributes.storefront_announcement_bar_font_size'), $errors, $settings) }}

        {{ Form::text('storefront_announcement_bar_speed', trans('storefront::attributes.storefront_announcement_bar_speed'), $errors, $settings) }}

        {{ Form::color('storefront_announcement_bar_bg_color', trans('storefront::attributes.storefront_announcement_bar_bg_color'), $errors, $settings) }}

        {{ Form::color('storefront_announcement_bar_text_color', trans('storefront::attributes.storefront_announcement_bar_text_color'), $errors, $settings) }}

        {{ Form::checkbox('storefront_announcement_bar_show_mobile', trans('storefront::attributes.storefront_announcement_bar_show_mobile'), trans('storefront::attributes.enabled'), $errors, $settings) }}
        {{ Form::checkbox('storefront_announcement_bar_show_tablet', trans('storefront::attributes.storefront_announcement_bar_show_tablet'), trans('storefront::attributes.enabled'), $errors, $settings) }}
        {{ Form::checkbox('storefront_announcement_bar_show_desktop', trans('storefront::attributes.storefront_announcement_bar_show_desktop'), trans('storefront::attributes.enabled'), $errors, $settings) }}
    </div>
</div>
