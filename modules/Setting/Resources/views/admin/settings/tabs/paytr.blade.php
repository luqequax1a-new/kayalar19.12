<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('paytr_enabled', trans('setting::attributes.paytr_enabled'), trans('setting::settings.form.enable_paytr'), $errors, $settings) }}
        {{ Form::text('translatable[paytr_label]', trans('setting::attributes.paytr_label'), $errors, $settings, ['required' => true]) }}
        {{ Form::textarea('translatable[paytr_description]', trans('setting::attributes.paytr_description'), $errors, $settings, ['rows' => 3, 'required' => true]) }}
        {{ Form::checkbox('paytr_test_mode', trans('setting::attributes.paytr_test_mode'), trans('setting::settings.form.use_sandbox_for_test_payments'), $errors, $settings) }}

        <div class="{{ old('paytr_enabled', array_get($settings, 'paytr_enabled')) ? '' : 'hide' }}" id="paytr-fields">
            {{ Form::text('paytr_merchant_id', trans('setting::attributes.paytr_merchant_id'), $errors, $settings, ['required' => true]) }}
            {{ Form::text('paytr_merchant_key', trans('setting::attributes.paytr_merchant_key'), $errors, $settings, ['required' => true]) }}
            {{ Form::text('paytr_merchant_salt', trans('setting::attributes.paytr_merchant_salt'), $errors, $settings, ['required' => true]) }}
        </div>
    </div>
</div>
