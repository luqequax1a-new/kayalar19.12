<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox(
                'storefront_product_page_info_icons_enabled',
                trans('storefront::attributes.section_status'),
                trans('storefront::storefront.form.enable_product_page_info_icons'),
                $errors,
                $settings
            ) }}
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="panel-wrap">
            <div class="row">
                <div class="col-md-8">
                    @include('media::admin.image_picker.single', [
                        'title' => trans('storefront::storefront.form.icon_1'),
                        'inputName' => 'storefront_product_page_info_icons_icon_1_image',
                        'file' => $icon1,
                    ])

                    {{ Form::text('storefront_product_page_info_icons_icon_1_title', trans('storefront::storefront.form.title_1'), $errors, $settings) }}

                    {{ Form::text('storefront_product_page_info_icons_icon_1_text', trans('storefront::storefront.form.sub_text_1'), $errors, $settings) }}

                    @include('media::admin.image_picker.single', [
                        'title' => trans('storefront::storefront.form.icon_2'),
                        'inputName' => 'storefront_product_page_info_icons_icon_2_image',
                        'file' => $icon2,
                    ])

                    {{ Form::text('storefront_product_page_info_icons_icon_2_title', trans('storefront::storefront.form.title_2'), $errors, $settings) }}

                    {{ Form::text('storefront_product_page_info_icons_icon_2_text', trans('storefront::storefront.form.sub_text_2'), $errors, $settings) }}

                    @include('media::admin.image_picker.single', [
                        'title' => trans('storefront::storefront.form.icon_3'),
                        'inputName' => 'storefront_product_page_info_icons_icon_3_image',
                        'file' => $icon3,
                    ])

                    {{ Form::text('storefront_product_page_info_icons_icon_3_title', trans('storefront::storefront.form.title_3'), $errors, $settings) }}

                    {{ Form::text('storefront_product_page_info_icons_icon_3_text', trans('storefront::storefront.form.sub_text_3'), $errors, $settings) }}
                </div>
            </div>
        </div>
    </div>
</div>
