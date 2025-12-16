@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product_feeds::messages.title'))

    <li class="active">{{ trans('product_feeds::messages.title') }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.product_feeds.settings.update') }}" class="form-horizontal">
        @csrf

        <div class="row" style="margin-bottom: 15px;">
            <div class="col-xs-12 text-right">
                <button type="submit" class="btn btn-primary" data-loading>
                    {{ trans('admin::admin.buttons.save') }}
                </button>
            </div>
        </div>

        <div class="accordion-content">
            <div class="accordion-box-content clearfix">
                <div class="col-xs-12">
                    <div class="accordion-box-content">
                        <div class="tab-content clearfix">
                            <div class="tab-pane fade in active">
                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.global') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.enable_all') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="global[enabled]" value="0">
                                        <input type="checkbox" name="global[enabled]" value="1" {{ $settings['global']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.default_brand_name') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="global[brand_name]" class="form-control" value="{{ $settings['global']['brand_name'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.default_country') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="global[country]" class="form-control" value="{{ $settings['global']['country'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.default_currency') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="global[currency]" class="form-control" value="{{ $settings['global']['currency'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.include_out_of_stock') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="global[include_out_of_stock]" value="0">
                                        <input type="checkbox" name="global[include_out_of_stock]" value="1" {{ $settings['global']['include_out_of_stock'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.include_unpublished') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="global[include_unpublished]" value="0">
                                        <input type="checkbox" name="global[include_unpublished]" value="1" {{ $settings['global']['include_unpublished'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.include_variants') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="global[include_variants]" value="0">
                                        <input type="checkbox" name="global[include_variants]" value="1" {{ $settings['global']['include_variants'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.feed_locale') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="global[locale]" class="form-control" value="{{ $settings['global']['locale'] }}">
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.google') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_enabled') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="google[enabled]" value="0">
                                        <input type="checkbox" name="google[enabled]" value="1" {{ $settings['google']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_feed_url') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static"><code>{{ url('/feeds/google-merchant.xml') }}</code></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.last_generated') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static">
                                            <span class="label label-default">{{ data_get($feedMeta, 'google.generated_at', '-') }}</span>
                                            <span class="label label-info">{{ trans('product_feeds::messages.fields.items') }}: {{ data_get($feedMeta, 'google.items_count', '-') }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_default_category') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <select
                                            name="google[category]"
                                            id="product-feeds-google-category"
                                        >
                                            @if($settings['google']['category'])
                                                @php
                                                    $googleCategoryLabel = (string) ($settings['google']['category'] ?? '');
                                                    for ($i = 0; $i < 5; $i++) {
                                                        $decoded = html_entity_decode($googleCategoryLabel, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                                                        if ($decoded === $googleCategoryLabel) {
                                                            break;
                                                        }

                                                        $googleCategoryLabel = $decoded;
                                                    }

                                                    $googleCategoryLabel = strip_tags($googleCategoryLabel);
                                                    $googleCategoryLabel = str_replace(['&gt;', '&amp;gt;', '&amp;amp;gt;', '&amp;amp;amp;gt;'], '>', $googleCategoryLabel);
                                                    $googleCategoryLabel = preg_replace('/\s*>\s*/u', ' > ', $googleCategoryLabel) ?? '';
                                                    $googleCategoryLabel = preg_replace('/\s+/u', ' ', $googleCategoryLabel) ?? '';
                                                    $googleCategoryLabel = trim($googleCategoryLabel);
                                                @endphp
                                                <option value="{{ $settings['google']['category'] }}">
                                                    {{ $googleCategoryLabel }}
                                                </option>
                                            @endif
                                        </select>
                                        <span class="help-block">
                                            {{ trans('product_feeds::messages.fields.google_taxonomy_help') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_missing_behavior') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <select name="google[missing_identifier_behavior]" class="form-control">
                                            <option value="empty" {{ $settings['google']['missing_identifier_behavior'] === 'empty' ? 'selected' : '' }}>{{ trans('product_feeds::messages.fields.google_missing_behavior_empty') }}</option>
                                            <option value="mpn_from_id" {{ $settings['google']['missing_identifier_behavior'] === 'mpn_from_id' ? 'selected' : '' }}>{{ trans('product_feeds::messages.fields.google_missing_behavior_mpn_from_id') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_use_store_tax') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="google[use_store_tax]" value="0">
                                        <input type="checkbox" name="google[use_store_tax]" value="1" {{ $settings['google']['use_store_tax'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_shipping_price') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="google[shipping_price]" class="form-control" value="{{ $settings['google']['shipping_price'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_currency') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="google[currency]" class="form-control" value="{{ $settings['google']['currency'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_price_includes_vat') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="google[price_includes_vat]" value="0">
                                        <input type="checkbox" name="google[price_includes_vat]" value="1" {{ $settings['google']['price_includes_vat'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_shipping_country') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="google[shipping_country]" class="form-control" value="{{ $settings['google']['shipping_country'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_shipping_service') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="google[shipping_service]" class="form-control" value="{{ $settings['google']['shipping_service'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_free_shipping_threshold') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="google[free_shipping_threshold]" class="form-control" value="{{ $settings['google']['free_shipping_threshold'] }}">
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.meta') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.meta_enabled') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="meta[enabled]" value="0">
                                        <input type="checkbox" name="meta[enabled]" value="1" {{ $settings['meta']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.meta_feed_url') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static"><code>{{ url('/feeds/meta-catalog.json') }}</code></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.last_generated') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static">
                                            <span class="label label-default">{{ data_get($feedMeta, 'meta.generated_at', '-') }}</span>
                                            <span class="label label-info">{{ trans('product_feeds::messages.fields.items') }}: {{ data_get($feedMeta, 'meta.items_count', '-') }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.meta_use_variants') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="meta[use_variants]" value="0">
                                        <input type="checkbox" name="meta[use_variants]" value="1" {{ $settings['meta']['use_variants'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.meta_currency') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="meta[currency]" class="form-control" value="{{ $settings['meta']['currency'] }}">
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.cache') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_enabled') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="cache[enabled]" value="0">
                                        <input type="checkbox" name="cache[enabled]" value="1" {{ $settings['cache']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_google') }}</label>
                                    <div class="col-sm-4 col-md-3">
                                        <input type="number" min="0" name="cache[google]" class="form-control" value="{{ $settings['cache']['google'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_meta') }}</label>
                                    <div class="col-sm-4 col-md-3">
                                        <input type="number" min="0" name="cache[meta]" class="form-control" value="{{ $settings['cache']['meta'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label"></label>
                                    <div class="col-md-7">
                                        <p class="form-control-static text-muted">
                                            {{ trans('product_feeds::messages.fields.cache_info') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_token') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $settings['cache']['token'] }}" readonly>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default" data-loading
                                                    onclick="document.getElementById('feed-cache-regenerate-token-form').submit();">
                                                    {{ trans('product_feeds::messages.fields.regenerate') }}
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_cron_url') }}</label>
                                    <div class="col-md-7">
                                        <p class="form-control-static"><strong>Google:</strong> {{ url('/feeds/cron/google') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="form-control-static"><strong>Meta:</strong> {{ url('/feeds/cron/meta') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="help-block">
                                            {{ trans('product_feeds::messages.fields.cache_cron_help') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('admin::admin.buttons.save') }}</label>
                                    <div class="col-md-7">
                                        <button type="submit" class="btn btn-primary" data-loading>
                                            {{ trans('admin::admin.buttons.save') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_google') }}</label>
                                    <div class="col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-google-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_google') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_meta') }}</label>
                                    <div class="col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-meta-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_meta') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form id="feed-cache-regenerate-token-form" method="POST" action="{{ route('admin.product_feeds.cache.regenerate_token') }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-google-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'google']) }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-meta-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'meta']) }}" style="display:none;">
        @csrf
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var el = document.getElementById('product-feeds-google-category');
            if (!el || !window.jQuery || !jQuery.fn.selectize) {
                return;
            }

            var $select = jQuery(el).selectize({
                delimiter: ',',
                persist: true,
                selectOnTab: true,
                allowEmptyOption: true,
                maxItems: 1,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: 'focus',
                openOnFocus: true,
                loadThrottle: 250,
                load: function (query, callback) {
                    var q = (query || '').trim();

                    function decodeHtml(str) {
                        var val = (str == null) ? '' : String(str);

                        for (var i = 0; i < 5; i++) {
                            var txt = document.createElement('textarea');
                            txt.innerHTML = val;
                            var next = txt.value;

                            if (next === val) {
                                break;
                            }

                            val = next;
                        }

                        val = val.replace(/<[^>]*>/g, '');
                        val = val.replace(/&gt;|&amp;gt;|&amp;amp;gt;|&amp;amp;amp;gt;/g, '>');
                        val = val.replace(/\s*>\s*/g, ' > ');
                        val = val.replace(/\s+/g, ' ').trim();
                        return val;
                    }

                    jQuery.ajax({
                        url: "{{ url('admin/google-taxonomy') }}",
                        data: q ? { q: q } : {},
                        success: function (resp) {
                            var results = resp && resp.results ? resp.results : [];

                            results = results.map(function (item) {
                                if (item && typeof item.text === 'string') {
                                    item.text = decodeHtml(item.text);
                                }

                                return item;
                            });

                            callback(results);
                        },
                        error: function () {
                            callback([]);
                        },
                    });
                },
            });

            // Ensure current value is shown nicely if present
            var currentVal = '{{ $settings['google']['category'] }}';
            if (currentVal) {
                var selectize = $select[0].selectize;
                var currentLabel = decodeHtml({!! json_encode($settings['google']['category'] ?? '') !!});

                selectize.addOption({ id: currentVal, text: currentLabel || currentVal });
                selectize.setValue(currentVal);
            }
        })();
    </script>
@endpush
