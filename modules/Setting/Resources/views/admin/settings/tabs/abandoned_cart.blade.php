<div class="row">
    <div class="col-md-9">
        <div class="box box-default">
            <div class="box-body">
                <div class="box-header" style="padding-left: 0; padding-top: 0;">
                    <h4 class="box-title" style="color: #3c8dbc; font-weight: 600;">Genel Ayarlar</h4>
                </div>

                {{ Form::checkbox('abandoned_cart_reminder_enabled', trans('setting::attributes.abandoned_cart_reminder_enabled'), trans('setting::settings.form.enable_abandoned_cart_reminder'), $errors, $settings) }}
                {{ Form::checkbox('abandoned_cart_coupon_enabled', trans('setting::attributes.abandoned_cart_coupon_enabled'), trans('setting::settings.form.abandoned_cart_coupon_help'), $errors, $settings) }}
                {{ Form::number('abandoned_cart_coupon_valid_days', trans('setting::attributes.abandoned_cart_coupon_valid_days'), $errors, $settings, ['min' => 1]) }}

                <div class="box-header" style="padding-left: 0; margin-top: 30px; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                    <h4 class="box-title" style="color: #3c8dbc; font-weight: 600;">1. Hatırlatma</h4>
                </div>
                {{ Form::checkbox('abandoned_cart_reminder_1_enabled', trans('setting::attributes.abandoned_cart_reminder_1_enabled'), trans('setting::settings.form.enable_abandoned_cart_reminder_1'), $errors, $settings) }}
                {{ Form::number('abandoned_cart_reminder_delay_hours', trans('setting::attributes.abandoned_cart_reminder_delay_hours'), $errors, $settings, ['min' => 1]) }}
                {{ Form::number('abandoned_cart_coupon_discount_percent', trans('setting::attributes.abandoned_cart_coupon_discount_percent'), $errors, $settings, ['min' => 0, 'max' => 100]) }}
                
                <div class="box-header" style="padding-left: 0; margin-top: 30px; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                    <h4 class="box-title" style="color: #3c8dbc; font-weight: 600;">2. Hatırlatma</h4>
                </div>
                {{ Form::checkbox('abandoned_cart_reminder_2_enabled', trans('setting::attributes.abandoned_cart_reminder_2_enabled'), trans('setting::settings.form.enable_abandoned_cart_reminder_2'), $errors, $settings) }}
                {{ Form::number('abandoned_cart_reminder_2_delay_hours', trans('setting::attributes.abandoned_cart_reminder_2_delay_hours'), $errors, $settings, ['min' => 1]) }}
                {{ Form::number('abandoned_cart_coupon_2_discount_percent', trans('setting::attributes.abandoned_cart_coupon_2_discount_percent'), $errors, $settings, ['min' => 0, 'max' => 100]) }}

                <div class="box-header" style="padding-left: 0; margin-top: 30px; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                    <h4 class="box-title" style="color: #3c8dbc; font-weight: 600;">3. Hatırlatma</h4>
                </div>
                {{ Form::checkbox('abandoned_cart_reminder_3_enabled', trans('setting::attributes.abandoned_cart_reminder_3_enabled'), trans('setting::settings.form.enable_abandoned_cart_reminder_3'), $errors, $settings) }}
                {{ Form::number('abandoned_cart_reminder_3_delay_hours', trans('setting::attributes.abandoned_cart_reminder_3_delay_hours'), $errors, $settings, ['min' => 1]) }}
                {{ Form::number('abandoned_cart_coupon_3_discount_percent', trans('setting::attributes.abandoned_cart_coupon_3_discount_percent'), $errors, $settings, ['min' => 0, 'max' => 100]) }}

                <div class="box-header" style="padding-left: 0; margin-top: 30px; border-top: 1px solid #f4f4f4; padding-top: 15px;">
                    <h4 class="box-title" style="color: #3c8dbc; font-weight: 600;">SMS Hatırlatma Ayarları</h4>
                </div>

                {{ Form::checkbox('abandoned_cart_sms_enabled', trans('setting::attributes.abandoned_cart_sms_enabled'), trans('setting::settings.form.enable_abandoned_cart_sms'), $errors, $settings) }}
                {{ Form::textarea('abandoned_cart_sms_message', trans('setting::attributes.abandoned_cart_sms_message'), $errors, $settings, ['rows' => 3]) }}
                
                <div class="alert alert-info" style="margin-top: 15px; background-color: #f0f7fd !important; border-color: #d0e3f0 !important; color: #31708f !important; border-left: 5px solid #31708f;">
                    <h4 style="font-size: 15px; margin-top: 0;"><i class="fa fa-info-circle"></i> SMS Şablon Değişkenleri</h4>
                    <p style="margin-bottom: 5px;">Aşağıdaki etiketleri mesaj metninde kullanabilirsiniz:</p>
                    <code>[customer_name]</code>, <code>[cart_url]</code>, <code>[store_name]</code>, <code>[coupon_code]</code>
                    <p style="margin-top: 10px; font-size: 12px; color: #666;">* SMS mesaj uzunluğunun 160 karakteri geçmemesi önerilir.</p>
                </div>
            </div>
        </div>
    </div>
</div>
