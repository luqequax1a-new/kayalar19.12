<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('smart_shipping_enabled', trans('setting::attributes.smart_shipping_enabled'), trans('setting::settings.form.enable_smart_shipping'), $errors, $settings) }}
        {{ Form::text('smart_shipping_name', trans('setting::attributes.smart_shipping_name'), $errors, $settings, ['required' => true]) }}
        {{ Form::textarea('smart_shipping_description', trans('setting::attributes.smart_shipping_description'), $errors, $settings, ['rows' => 3]) }}
        {{ Form::number('smart_shipping_base_rate', trans('setting::attributes.smart_shipping_base_rate'), $errors, $settings, ['min' => 0, 'step' => '0.01', 'required' => true]) }}
        {{ Form::number('smart_shipping_free_threshold', trans('setting::attributes.smart_shipping_free_threshold'), $errors, $settings, ['min' => 0, 'step' => '0.01']) }}

        {{ Form::checkbox('smart_shipping_show_progress_bar', trans('setting::attributes.smart_shipping_show_progress_bar'), 'Sepette gösterilsin mi?', $errors, $settings) }}

        <div class="row">
            <div class="col-md-6">
                {{ Form::text('smart_shipping_button_1_text', trans('setting::attributes.smart_shipping_button_1_text'), $errors, $settings) }}
            </div>
            <div class="col-md-6">
                {{ Form::text('smart_shipping_button_1_link', trans('setting::attributes.smart_shipping_button_1_link'), $errors, $settings) }}
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                {{ Form::text('smart_shipping_button_2_text', trans('setting::attributes.smart_shipping_button_2_text'), $errors, $settings) }}
            </div>
            <div class="col-md-6">
                {{ Form::text('smart_shipping_button_2_link', trans('setting::attributes.smart_shipping_button_2_link'), $errors, $settings) }}
            </div>
        </div>
    </div>
</div>
