<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('storefront_home_marquee_enabled', trans('storefront::attributes.storefront_home_marquee_enabled'), trans('storefront::attributes.enabled'), $errors, $settings) }}

        {{ Form::textarea('storefront_home_marquee_text', trans('storefront::attributes.storefront_home_marquee_text'), $errors, $settings, ['rows' => 4]) }}

        {{ Form::text('storefront_home_marquee_separator', trans('storefront::attributes.storefront_home_marquee_separator'), $errors, $settings) }}

        {{ Form::text('storefront_home_marquee_font_size', trans('storefront::attributes.storefront_home_marquee_font_size'), $errors, $settings) }}

        {{ Form::text('storefront_home_marquee_speed', trans('storefront::attributes.storefront_home_marquee_speed'), $errors, $settings) }}

        {{ Form::color('storefront_home_marquee_bg_color', trans('storefront::attributes.storefront_home_marquee_bg_color'), $errors, $settings) }}

        {{ Form::color('storefront_home_marquee_text_color', trans('storefront::attributes.storefront_home_marquee_text_color'), $errors, $settings) }}

        {{ Form::text('storefront_home_marquee_margin_top', trans('storefront::attributes.storefront_home_marquee_margin_top'), $errors, $settings) }}

        {{ Form::text('storefront_home_marquee_margin_bottom', trans('storefront::attributes.storefront_home_marquee_margin_bottom'), $errors, $settings) }}
    </div>
</div>
