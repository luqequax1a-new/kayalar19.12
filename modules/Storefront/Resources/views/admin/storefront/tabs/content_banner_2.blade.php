<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox('storefront_content_banner_2_enabled', trans('storefront::attributes.section_status'), 'Content Banner 2 bölümünü etkinleştir', $errors, $settings) }}
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="panel-wrap">
            <div class="row">
                <div class="col-md-8">
                    @include('media::admin.image_picker.single', [
                        'title' => trans('storefront::storefront.form.banner'),
                        'inputName' => 'storefront_content_banner_2_image',
                        'file' => $image,
                    ])

                    {{ Form::text('storefront_content_banner_2_title', 'Başlık', $errors, $settings) }}

                    {{ Form::textarea('storefront_content_banner_2_content', 'İçerik', $errors, $settings, ['rows' => 5]) }}

                    {{ Form::text('storefront_content_banner_2_button_text', trans('storefront::storefront.form.button_text'), $errors, $settings) }}

                    {{ Form::text('storefront_content_banner_2_button_url', 'Buton Linki', $errors, $settings) }}
                </div>
            </div>
        </div>
    </div>
</div>
