<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox(
                'storefront_koleysiyon_grid_enabled',
                trans('storefront::attributes.section_status'),
                'Koleysiyon Grid sectionunu etkinleştir',
                $errors,
                $settings
            ) }}
        </div>
    </div>

    <div class="row m-t-20">
        <div class="col-md-8">
            {{ Form::text('storefront_koleysiyon_grid_title', 'Section Başlığı', $errors, $settings) }}

            {{ Form::textarea('storefront_koleysiyon_grid_subtitle', 'Kısa Açıklama', $errors, $settings, ['rows' => 2]) }}
        </div>
    </div>

    <div class="tab-content clearfix m-t-20">
        <div class="panel-wrap">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Koleksiyon Kart 1</h4>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            @include('media::admin.image_picker.single', [
                                'title' => 'Görsel 1',
                                'inputName' => 'storefront_koleysiyon_grid_card_1_image',
                                'file' => $card1Image ?? null,
                            ])
                        </div>

                        <div class="col-md-8">
                            {{ Form::text('storefront_koleysiyon_grid_card_1_title', 'Başlık', $errors, $settings) }}

                            {{ Form::textarea('storefront_koleysiyon_grid_card_1_text', 'Kısa Açıklama', $errors, $settings, ['rows' => 2]) }}

                            {{ Form::text('storefront_koleysiyon_grid_card_1_button_text', 'Buton Yazısı', $errors, $settings) }}

                            {{ Form::text('storefront_koleysiyon_grid_card_1_button_url', 'Buton Linki (URL)', $errors, $settings) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Koleksiyon Kart 2</h4>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            @include('media::admin.image_picker.single', [
                                'title' => 'Görsel 2',
                                'inputName' => 'storefront_koleysiyon_grid_card_2_image',
                                'file' => $card2Image ?? null,
                            ])
                        </div>

                        <div class="col-md-8">
                            {{ Form::text('storefront_koleysiyon_grid_card_2_title', 'Başlık', $errors, $settings) }}

                            {{ Form::textarea('storefront_koleysiyon_grid_card_2_text', 'Kısa Açıklama', $errors, $settings, ['rows' => 2]) }}

                            {{ Form::text('storefront_koleysiyon_grid_card_2_button_text', 'Buton Yazısı', $errors, $settings) }}

                            {{ Form::text('storefront_koleysiyon_grid_card_2_button_url', 'Buton Linki (URL)', $errors, $settings) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Koleksiyon Kart 3</h4>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            @include('media::admin.image_picker.single', [
                                'title' => 'Görsel 3',
                                'inputName' => 'storefront_koleysiyon_grid_card_3_image',
                                'file' => $card3Image ?? null,
                            ])
                        </div>

                        <div class="col-md-8">
                            {{ Form::text('storefront_koleysiyon_grid_card_3_title', 'Başlık', $errors, $settings) }}

                            {{ Form::textarea('storefront_koleysiyon_grid_card_3_text', 'Kısa Açıklama', $errors, $settings, ['rows' => 2]) }}

                            {{ Form::text('storefront_koleysiyon_grid_card_3_button_text', 'Buton Yazısı', $errors, $settings) }}

                            {{ Form::text('storefront_koleysiyon_grid_card_3_button_url', 'Buton Linki (URL)', $errors, $settings) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Koleksiyon Kart 4</h4>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            @include('media::admin.image_picker.single', [
                                'title' => 'Görsel 4',
                                'inputName' => 'storefront_koleysiyon_grid_card_4_image',
                                'file' => $card4Image ?? null,
                            ])
                        </div>

                        <div class="col-md-8">
                            {{ Form::text('storefront_koleysiyon_grid_card_4_title', 'Başlık', $errors, $settings) }}

                            {{ Form::textarea('storefront_koleysiyon_grid_card_4_text', 'Kısa Açıklama', $errors, $settings, ['rows' => 2]) }}

                            {{ Form::text('storefront_koleysiyon_grid_card_4_button_text', 'Buton Yazısı', $errors, $settings) }}

                            {{ Form::text('storefront_koleysiyon_grid_card_4_button_url', 'Buton Linki (URL)', $errors, $settings) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
