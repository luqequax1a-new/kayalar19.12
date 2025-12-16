@if (! empty($categoryGridBanners))
    <section class="category-grid-banners">
        <div class="container">
            @php
                $gridTitle = setting('storefront_category_grid_banners_title');
            @endphp

            @if (! empty($gridTitle))
                @php
                    $tag = setting('storefront_category_grid_banners_title_tag', 'h3');
                    $color = setting('storefront_category_grid_banners_title_color');
                    $size = setting('storefront_category_grid_banners_title_size');
                    $weight = setting('storefront_category_grid_banners_title_weight');
                    $align = setting('storefront_category_grid_banners_title_align', 'left');
                    $marginTop = setting('storefront_category_grid_banners_title_margin_top');
                    $marginBottom = setting('storefront_category_grid_banners_title_margin_bottom');

                    $wrapperStyles = [];
                    $titleStyles = [];

                    if ($marginTop !== null && $marginTop !== '') {
                        $wrapperStyles[] = "margin-top: {$marginTop}px";
                    }

                    if ($marginBottom !== null && $marginBottom !== '') {
                        $wrapperStyles[] = "margin-bottom: {$marginBottom}px";
                    }

                    if (in_array($align, ['left', 'center', 'right'], true)) {
                        $wrapperStyles[] = "text-align: {$align}";
                    }

                    if ($color) {
                        $titleStyles[] = "color: {$color}";
                    }

                    if ($size) {
                        $titleStyles[] = "font-size: {$size}px";
                    }

                    if ($weight) {
                        $titleStyles[] = "font-weight: {$weight}";
                    }

                    $wrapperStyleAttr = empty($wrapperStyles) ? '' : ' style="' . implode('; ', $wrapperStyles) . '"';
                    $titleStyleAttr = empty($titleStyles) ? '' : ' style="' . implode('; ', $titleStyles) . '"';
                @endphp

                <div class="category-grid-header"{!! $wrapperStyleAttr !!}>
                    {!! "<{$tag}{$titleStyleAttr}>" . e($gridTitle) . "</{$tag}>" !!}
                </div>
            @endif

            <div class="category-grid">
                @foreach ($categoryGridBanners as $index => $banner)
                    @php
                        $image = $banner->image;
                    @endphp

                    @continue(empty($image) || ! $image->path)

                    @php
                        $imgWebp = $image->grid_webp_url ?? null;
                        $imgJpeg = $image->grid_jpeg_url ?? $image->path;
                    @endphp

                    <a
                        href="{{ $banner->call_to_action_url ?: '#' }}"
                        class="category-grid-item"
                        target="{{ $banner->open_in_new_window ? '_blank' : '_self' }}"
                    >
                        <div class="category-grid-image">
                            <picture>
                                @if ($imgWebp)
                                    <source srcset="{{ $imgWebp }}" type="image/webp">
                                @endif

                                <img
                                    src="{{ $imgJpeg }}"
                                    alt="Category"
                                    loading="lazy"
                                />
                            </picture>

                            <div class="category-grid-overlay">
                                @php
                                    $btnText = setting('storefront_category_grid_banners_' . $loop->iteration . '_button_text')
                                        ?: trans('storefront::storefront.buttons.category_grid_default');
                                @endphp

                                <span class="category-grid-button">
                                    {{ $btnText }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
