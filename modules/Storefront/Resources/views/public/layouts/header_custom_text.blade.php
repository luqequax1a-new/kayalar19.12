@php
    $enabled = (bool) setting('storefront_header_custom_text_enabled', false);
    $content = (string) setting('storefront_header_custom_text_content', '');

    $fontSize = trim((string) setting('storefront_header_custom_text_font_size', '14px'));
    if ($fontSize !== '' && is_numeric($fontSize)) {
        $fontSize = $fontSize . 'px';
    }

    $bgColor = trim((string) setting('storefront_header_custom_text_bg_color', '#ffffff'));
    $textColor = trim((string) setting('storefront_header_custom_text_text_color', '#111827'));

    $showMobile = filter_var(setting('storefront_header_custom_text_show_mobile', 1), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $showTablet = filter_var(setting('storefront_header_custom_text_show_tablet', 1), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $showDesktop = filter_var(setting('storefront_header_custom_text_show_desktop', 1), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    $showMobile = is_null($showMobile) ? true : $showMobile;
    $showTablet = is_null($showTablet) ? true : $showTablet;
    $showDesktop = is_null($showDesktop) ? true : $showDesktop;

    $visibilityClasses = implode(' ', array_filter([
        $showMobile ? null : 'header-custom-text--hide-mobile',
        $showTablet ? null : 'header-custom-text--hide-tablet',
        $showDesktop ? null : 'header-custom-text--hide-desktop',
    ]));

    $style = implode('; ', array_filter([
        $bgColor ? "--header-custom-text-bg: {$bgColor}" : null,
        $textColor ? "--header-custom-text-color: {$textColor}" : null,
        $fontSize ? "--header-custom-text-font-size: {$fontSize}" : null,
    ]));
@endphp

@if ($enabled && trim($content) !== '')
    <div class="header-custom-text {{ $visibilityClasses }}" style="{{ $style }}">
        <div class="header-custom-text__inner">
            {!! $content !!}
        </div>
    </div>
@endif
