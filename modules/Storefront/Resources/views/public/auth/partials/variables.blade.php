<style>
    @php($themeColorString = trim((string) (optional($themeColor)->toString() ?: '')) ?: '#0068e1')
    :root {
        --base-font-family: "{{ setting('storefront_display_font', 'Poppins') }}", sans-serif;
        --color-primary: {{ tinycolor($themeColorString)->toHexString() }};
        --color-primary-darken-10: {{ generate_color_shade($themeColorString, 0.1) }};
        --color-primary-alpha-10: {{ tinycolor($themeColorString)->setAlpha(0.1)->toString() }};
        --color-primary-alpha-90: {{ tinycolor($themeColorString)->setAlpha(0.9)->toString() }};
    }
</style>
