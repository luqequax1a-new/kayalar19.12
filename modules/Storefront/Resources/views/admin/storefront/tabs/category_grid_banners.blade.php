<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox(
                'storefront_category_grid_banners_enabled',
                trans('storefront::attributes.section_status'),
                trans('storefront::storefront.form.enable_category_grid_banners_section'),
                $errors,
                $settings
            ) }}
        </div>
    </div>

    <div class="row m-t-20">
        <div class="col-md-8">
            {{ Form::text('translatable[storefront_category_grid_banners_title]', trans('storefront::attributes.section_title'), $errors, $settings) }}

            {{ Form::select('storefront_category_grid_banners_title_tag', 'Title Tag (H / Heading)', $errors, [
                'p' => 'P',
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
            ], $settings) }}

            {{ Form::color('storefront_category_grid_banners_title_color', 'Title Color', $errors, $settings) }}

            {{ Form::select('storefront_category_grid_banners_title_align', 'Title Alignment', $errors, [
                'left' => 'Left',
                'center' => 'Center',
                'right' => 'Right',
            ], $settings) }}

            {{ Form::number('storefront_category_grid_banners_title_size', 'Title Font Size (px)', $errors, $settings) }}

            {{ Form::select('storefront_category_grid_banners_title_weight', 'Title Weight', $errors, [
                '' => 'Default',
                '300' => 'Light (300)',
                '400' => 'Normal (400)',
                '500' => 'Medium (500)',
                '600' => 'Semi Bold (600)',
                '700' => 'Bold (700)',
            ], $settings) }}

            {{ Form::number('storefront_category_grid_banners_title_margin_top', 'Title Margin Top (px)', $errors, $settings) }}

            {{ Form::number('storefront_category_grid_banners_title_margin_bottom', 'Title Margin Bottom (px)', $errors, $settings) }}
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="panel-wrap">
            @foreach (range(1, 20) as $i)
                @include('storefront::admin.storefront.tabs.partials.single_banner', [
                    'label'  => trans('storefront::storefront.form.category_grid_banner_' . $i),
                    'name'   => 'storefront_category_grid_banners_' . $i,
                    'banner' => $banners['banner_' . $i],
                ])

                {{ Form::text(
                    "storefront_category_grid_banners_{$i}_button_text",
                    trans('storefront::storefront.form.button_text'),
                    $errors,
                    $settings
                ) }}
            @endforeach
        </div>
    </div>
</div>
