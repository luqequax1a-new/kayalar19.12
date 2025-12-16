@php
    $enabled = (bool) setting('storefront_announcement_bar_enabled', false);
    $itemsRaw = (string) setting('storefront_announcement_bar_items', '2000 TL ve üzeri kargo bedava\nAynı gün kargo\nKapıda ödeme imkanı\nKolay iade & değişim');

    $items = collect(preg_split("/\r\n|\r|\n/", $itemsRaw))
        ->map(fn ($v) => trim((string) $v))
        ->filter(fn ($v) => $v !== '')
        ->values();

    $separator = (string) setting('storefront_announcement_bar_separator', '•');
    $separator = trim($separator) === '' ? '•' : trim($separator);

    $message = $items->implode(" {$separator} ");
    $announcementText = $message;

    $repeat = (int) setting('storefront_announcement_bar_repeat', 20);
    $repeat = max(4, min(80, $repeat));

    $gap = trim((string) setting('storefront_announcement_bar_gap', '25px'));
    if ($gap !== '' && is_numeric($gap)) {
        $gap = $gap . 'px';
    }

    $fontSize = trim((string) setting('storefront_announcement_bar_font_size', '13px'));
    if ($fontSize !== '' && is_numeric($fontSize)) {
        $fontSize = $fontSize . 'px';
    }
    $speedRaw = trim((string) setting('storefront_announcement_bar_speed', '22'));
    $speedNormalized = strtolower($speedRaw);
    $speedNormalized = preg_replace('/\s+/', '', $speedNormalized);
    $speedNormalized = rtrim($speedNormalized, 's');

    if ($speedNormalized === '' || is_null($speedNormalized)) {
        $speedSeconds = 22.0;
    } elseif (is_numeric($speedNormalized)) {
        $speedSeconds = (float) $speedNormalized;
    } else {
        $onlyNumber = preg_replace('/[^0-9\.]/', '', (string) $speedNormalized);
        $speedSeconds = is_numeric($onlyNumber) ? (float) $onlyNumber : 22.0;
    }

    $speedSeconds = max(6.0, $speedSeconds);

    $bgColor = trim((string) setting('storefront_announcement_bar_bg_color', '#111827'));
    $textColor = trim((string) setting('storefront_announcement_bar_text_color', '#ffffff'));

    $showMobile = filter_var(setting('storefront_announcement_bar_show_mobile', 1), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $showTablet = filter_var(setting('storefront_announcement_bar_show_tablet', 1), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $showDesktop = filter_var(setting('storefront_announcement_bar_show_desktop', 1), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    $showMobile = is_null($showMobile) ? true : $showMobile;
    $showTablet = is_null($showTablet) ? true : $showTablet;
    $showDesktop = is_null($showDesktop) ? true : $showDesktop;

    $visibilityClasses = implode(' ', array_filter([
        $showMobile ? null : 'announcement-bar--hide-mobile',
        $showTablet ? null : 'announcement-bar--hide-tablet',
        $showDesktop ? null : 'announcement-bar--hide-desktop',
    ]));

    $style = implode('; ', array_filter([
        $bgColor ? "--announcement-bg: {$bgColor}" : null,
        $textColor ? "--announcement-color: {$textColor}" : null,
        $fontSize ? "--announcement-font-size: {$fontSize}" : null,
        $gap ? "--announcement-gap: {$gap}" : null,
        "--announcement-speed: {$speedSeconds}s",
    ]));

    $direction = is_rtl() ? 'rtl' : 'ltr';
@endphp

@if ($enabled && trim($announcementText) !== '')
    <div
        class="announcement-bar {{ $visibilityClasses }}"
        role="region"
        aria-label="Announcements"
        style="{{ $style }}"
    >
        <div class="marquee-container" data-direction="{{ $direction }}">
            <div class="marquee-wrapper">
                <div class="marquee-textcontainer" aria-hidden="true">
                    @foreach (range(1, $repeat) as $i)
                        <span class="marquee-item">{{ $announcementText }}</span>
                    @endforeach
                </div>

                <div class="marquee-textcontainer" aria-hidden="true">
                    @foreach (range(1, $repeat) as $i)
                        <span class="marquee-item">{{ $announcementText }}</span>
                    @endforeach
                </div>

                <div class="announcement-bar__sr">{{ $announcementText }}</div>
            </div>
        </div>
    </div>
@endif
