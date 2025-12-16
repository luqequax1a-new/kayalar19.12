<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox('storefront_info_icons_2_enabled', trans('storefront::attributes.section_status'), 'Bilgi İkonları 2 bölümünü etkinleştir', $errors, $settings) }}
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="panel-wrap">
            <div class="row">
                <div class="col-md-8">
                    @include('media::admin.image_picker.single', [
                        'title' => 'İkon 1',
                        'inputName' => 'storefront_info_icons_2_icon_1_image',
                        'file' => $icon1,
                    ])

                    {{ Form::text('storefront_info_icons_2_icon_1_title', 'Başlık 1', $errors, $settings) }}

                    {{ Form::text('storefront_info_icons_2_icon_1_text', 'Alt Metin 1', $errors, $settings) }}

                    @include('media::admin.image_picker.single', [
                        'title' => 'İkon 2',
                        'inputName' => 'storefront_info_icons_2_icon_2_image',
                        'file' => $icon2,
                    ])

                    {{ Form::text('storefront_info_icons_2_icon_2_title', 'Başlık 2', $errors, $settings) }}

                    {{ Form::text('storefront_info_icons_2_icon_2_text', 'Alt Metin 2', $errors, $settings) }}

                    @include('media::admin.image_picker.single', [
                        'title' => 'İkon 3',
                        'inputName' => 'storefront_info_icons_2_icon_3_image',
                        'file' => $icon3,
                    ])

                    {{ Form::text('storefront_info_icons_2_icon_3_title', 'Başlık 3', $errors, $settings) }}

                    {{ Form::text('storefront_info_icons_2_icon_3_text', 'Alt Metin 3', $errors, $settings) }}
                </div>
            </div>
        </div>
    </div>
</div>
