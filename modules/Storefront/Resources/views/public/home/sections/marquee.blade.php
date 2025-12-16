@php
    $enabled = (bool) setting('storefront_home_marquee_enabled', false);

    $textRaw = (string) setting('storefront_home_marquee_text', '');
    $lines = collect(preg_split("/\r\n|\r|\n/", $textRaw))
        ->map(fn ($v) => trim((string) $v))
        ->filter(fn ($v) => $v !== '')
        ->values();

    $separator = (string) setting('storefront_home_marquee_separator', '•');
    $separator = trim($separator) === '' ? '•' : trim($separator);

    $message = $lines->implode(" {$separator} ");

    $repeat = 20;

    $fontSize = trim((string) setting('storefront_home_marquee_font_size', '16px'));
    if ($fontSize !== '' && is_numeric($fontSize)) {
        $fontSize = $fontSize . 'px';
    }

    $speedRaw = trim((string) setting('storefront_home_marquee_speed', '200'));
    $speedNormalized = strtolower($speedRaw);
    $speedNormalized = preg_replace('/\s+/', '', $speedNormalized);
    $speedNormalized = rtrim($speedNormalized, 's');

    if ($speedNormalized === '' || is_null($speedNormalized)) {
        $speedSeconds = 200.0;
    } elseif (is_numeric($speedNormalized)) {
        $speedSeconds = (float) $speedNormalized;
    } else {
        $onlyNumber = preg_replace('/[^0-9\.]/', '', (string) $speedNormalized);
        $speedSeconds = is_numeric($onlyNumber) ? (float) $onlyNumber : 200.0;
    }

    $speedSeconds = max(6.0, $speedSeconds);

    $bgColor = trim((string) setting('storefront_home_marquee_bg_color', '#ee8bb9ff'));
    $textColor = trim((string) setting('storefront_home_marquee_text_color', '#ffffffff'));

    $marginTop = trim((string) setting('storefront_home_marquee_margin_top', '0'));
    if ($marginTop !== '' && is_numeric($marginTop)) {
        $marginTop = $marginTop . 'px';
    }
    $marginTop = $marginTop === '' ? '0px' : $marginTop;

    $marginBottom = trim((string) setting('storefront_home_marquee_margin_bottom', '0'));
    if ($marginBottom !== '' && is_numeric($marginBottom)) {
        $marginBottom = $marginBottom . 'px';
    }
    $marginBottom = $marginBottom === '' ? '0px' : $marginBottom;

    $style = implode('; ', array_filter([
        $bgColor ? "--announcement-bg: {$bgColor}" : null,
        $textColor ? "--announcement-color: {$textColor}" : null,
        $fontSize ? "--announcement-font-size: {$fontSize}" : null,
        "--announcement-speed: {$speedSeconds}s",
        $marginTop ? "--home-marquee-mt: {$marginTop}" : null,
        $marginBottom ? "--home-marquee-mb: {$marginBottom}" : null,
    ]));

    $direction = is_rtl() ? 'rtl' : 'ltr';
@endphp

@if ($enabled && trim($message) !== '')
    <section class="home-marquee" style="margin-top: var(--home-marquee-mt, 0px); margin-bottom: var(--home-marquee-mb, 0px); {{ $style }}">
        <div class="announcement-bar" role="region" aria-label="Marquee">
            <div class="marquee-container" data-direction="{{ $direction }}">
                <div class="marquee-wrapper">
                    <div class="marquee-textcontainer" aria-hidden="true">
                        @foreach (range(1, $repeat) as $i)
                            <span class="marquee-item">{{ $message }}</span>
                        @endforeach
                    </div>

                    <div class="marquee-textcontainer" aria-hidden="true">
                        @foreach (range(1, $repeat) as $i)
                            <span class="marquee-item">{{ $message }}</span>
                        @endforeach
                    </div>

                    <div class="announcement-bar__sr">{{ $message }}</div>
                </div>
            </div>
        </div>
    </section>
@endif
