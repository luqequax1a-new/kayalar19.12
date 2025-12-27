@hasAccess('admin.products.index')
    <div class="form-group">
        <label for="{{ "{$fieldNamePrefix}_product_type" }}" class="col-md-3 control-label text-left">
            {{ trans('storefront::attributes.type') }}
        </label>

        <div class="col-md-9">
            <select name="{{ "{$fieldNamePrefix}_product_type" }}" class="form-control custom-select-black product-type" id="{{ "{$fieldNamePrefix}_product_type" }}">
                <option value="">{{ trans('storefront::storefront.form.please_select') }}</option>

                <option value="all_products" {{ setting("{$fieldNamePrefix}_product_type") === 'all_products' ? 'selected' : '' }}>
                    Tüm ürünler
                </option>

                @hasAccess('admin.categories.index')
                    <option value="category_products" {{ setting("{$fieldNamePrefix}_product_type") === 'category_products' ? 'selected' : '' }}>
                        {{ trans('storefront::storefront.form.product_types.category_products') }}
                    </option>
                @endHasAccess

                @unless ($featuredCategories ?? false)
                    <option value="latest_products" {{ setting("{$fieldNamePrefix}_product_type") === 'latest_products' ? 'selected' : '' }}>
                        {{ trans('storefront::storefront.form.product_types.latest_products') }}
                    </option>

                    <option value="recently_viewed_products" {{ setting("{$fieldNamePrefix}_product_type") === 'recently_viewed_products' ? 'selected' : '' }}>
                        {{ trans('storefront::storefront.form.product_types.recently_viewed_products') }}
                    </option>
                @endunless

                @if (in_array(($fieldNamePrefix ?? ''), ['storefront_carousel_section', 'storefront_carousel_section_2'], true))
                    <option value="tag_products" {{ setting("{$fieldNamePrefix}_product_type") === 'tag_products' ? 'selected' : '' }}>
                        Etikete göre ürünler
                    </option>
                @endif

                <option value="custom_products" {{ setting("{$fieldNamePrefix}_product_type") === 'custom_products' ? 'selected' : '' }}>
                    {{ trans('storefront::storefront.form.product_types.custom_products') }}
                </option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="{{ "{$fieldNamePrefix}_variants_mode" }}" class="col-md-3 control-label text-left">
            Varyantları ayrı göster
        </label>

        <div class="col-md-9">
            <select name="{{ "{$fieldNamePrefix}_variants_mode" }}" class="form-control custom-select-black" id="{{ "{$fieldNamePrefix}_variants_mode" }}">
                <option value="inherit" {{ (setting("{$fieldNamePrefix}_variants_mode") ?: 'inherit') === 'inherit' ? 'selected' : '' }}>
                    Ürün ayarını kullan
                </option>
                <option value="force_on" {{ setting("{$fieldNamePrefix}_variants_mode") === 'force_on' ? 'selected' : '' }}>
                    Evet (zorla)
                </option>
                <option value="force_off" {{ setting("{$fieldNamePrefix}_variants_mode") === 'force_off' ? 'selected' : '' }}>
                    Hayır (zorla)
                </option>
            </select>
        </div>
    </div>

    @if (auth()->user()->hasAccess('admin.categories.index') && ! ($featuredCategories ?? false))
        <div class="category-products {{ setting("{$fieldNamePrefix}_product_type") === 'category_products' ? '' : 'hide' }}">
            {{ Form::select("{$fieldNamePrefix}_category_id", trans('storefront::attributes.category'), $errors, $categories, $settings) }}
        </div>
    @endif

    @if (($fieldNamePrefix ?? '') === 'storefront_carousel_section')
        <div class="tag-products {{ setting("{$fieldNamePrefix}_product_type") === 'tag_products' ? '' : 'hide' }}">
            {{ Form::select("{$fieldNamePrefix}_tags", 'Etiketler', $errors, $tags ?? [], $settings, ['class' => 'selectize prevent-creation', 'multiple' => true]) }}
        </div>
    @endif

    <div class="products-limit {{ in_array(setting("{$fieldNamePrefix}_product_type"), ['all_products','latest_products', 'recently_viewed_products','category_products','tag_products']) ? '' : 'hide' }}">
        {{ Form::number("{$fieldNamePrefix}_products_limit", trans('storefront::attributes.products_limit'), $errors, $settings) }}
    </div>

    <div class="custom-products {{ setting("{$fieldNamePrefix}_product_type") === 'custom_products' ? '' : 'hide' }}">
        {{ Form::select("{$fieldNamePrefix}_products", trans('storefront::attributes.products'), $errors, $products, $settings, ['class' => 'selectize prevent-creation', 'data-url' => route('admin.products.index'), 'multiple' => true]) }}
    </div>
@endHasAccess
