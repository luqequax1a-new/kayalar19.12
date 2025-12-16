<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('storefront_carousel_section_2_enabled', trans('storefront::attributes.section_status'), 'Enable carousel product 2 section', $errors, $settings) }}
        {{ Form::text('translatable[storefront_carousel_section_2_title]', trans('storefront::attributes.section_title'), $errors, $settings) }}

        {{ Form::select('storefront_carousel_section_2_title_tag', 'Title Tag (H Tag)', $errors, [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
        ], $settings) }}

        {{ Form::select('storefront_carousel_section_2_title_align', 'Title Alignment', $errors, [
            'left' => 'Left',
            'center' => 'Center',
            'right' => 'Right',
        ], $settings) }}

        {{ Form::select('storefront_carousel_section_2_title_weight', 'Title Weight', $errors, [
            '100' => '100',
            '200' => '200',
            '300' => '300',
            '400' => '400 (Normal)',
            '500' => '500',
            '600' => '600',
            '700' => '700 (Bold)',
            '800' => '800',
            '900' => '900',
        ], $settings) }}

        {{ Form::number('storefront_carousel_section_2_title_size', 'Title Font Size (px)', $errors, $settings) }}

        {{ Form::color('storefront_carousel_section_2_title_color', 'Title Color', $errors, $settings) }}

        {{ Form::select('storefront_carousel_section_2_title_decoration', 'Title Text Decoration', $errors, [
            '' => 'None',
            'underline' => 'Underline',
            'overline' => 'Overline',
            'line-through' => 'Line Through',
        ], $settings) }}

        {{ Form::select('storefront_carousel_section_2_title_style', 'Title Font Style', $errors, [
            '' => 'Normal',
            'italic' => 'Italic',
        ], $settings) }}

        {{ Form::select('storefront_carousel_section_2_title_family', 'Title Font Family', $errors, [
            '' => 'Default',
            'Poppins, sans-serif' => 'Poppins',
            'Roboto, sans-serif' => 'Roboto',
            'Open Sans, sans-serif' => 'Open Sans',
            'Montserrat, sans-serif' => 'Montserrat',
            'Nunito, sans-serif' => 'Nunito',
        ], $settings) }}

        {{ Form::checkbox('storefront_carousel_section_2_title_divider', 'Title Divider', 'Show divider line under title', $errors, $settings) }}

        {{ Form::number('storefront_carousel_section_2_title_margin_top', 'Title Margin Top (px)', $errors, $settings) }}

        {{ Form::number('storefront_carousel_section_2_title_margin_bottom', 'Title Margin Bottom (px)', $errors, $settings) }}

        {{ Form::checkbox('storefront_carousel_section_2_show_dots', trans('storefront::attributes.storefront_swiper_show_dots'), trans('storefront::attributes.enabled'), $errors, $settings) }}

        {{ Form::checkbox('storefront_carousel_section_2_show_arrows', trans('storefront::attributes.storefront_swiper_show_arrows'), trans('storefront::attributes.enabled'), $errors, $settings) }}

        @include('storefront::admin.storefront.tabs.partials.products', [
            'fieldNamePrefix' => 'storefront_carousel_section_2',
            'products' => $products,
            'tags' => $tags ?? [],
        ])

        {{ Form::select('storefront_carousel_section_2_sort_by', 'Sıralama', $errors, [
            '' => 'Varsayılan',
            'latest' => 'En son eklenen',
            'random' => 'Rastgele',
        ], $settings) }}

        {{ Form::checkbox('storefront_carousel_section_2_only_in_stock', 'Yalnızca stokta olan ürünleri göster', 'Yalnızca stokta olan ürünleri getir', $errors, $settings) }}

        @php($perRowOptions = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
        ])

        {{ Form::select('storefront_carousel_section_2_per_row_mobile', 'Mobilde satır başına ürün (telefon)', $errors, $perRowOptions, $settings) }}
        {{ Form::select('storefront_carousel_section_2_per_row_tablet', 'Tablette satır başına ürün', $errors, $perRowOptions, $settings) }}
        {{ Form::select('storefront_carousel_section_2_per_row_desktop', 'Masaüstü satır başına ürün', $errors, $perRowOptions, $settings) }}

        {{ Form::checkbox('storefront_carousel_section_2_autoplay', 'Slider Otomatik Oynatma', 'Autoplay', $errors, $settings) }}

        {{ Form::number('storefront_carousel_section_2_autoplay_speed', 'Autoplay Hızı (ms)', $errors, $settings) }}
    </div>
</div>

@include('admin::partials.selectize_remote')
