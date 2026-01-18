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
                                        <p class="form-control-static" style="display: flex; align-items: center; gap: 10px;">
                                            <span class="label label-default">{{ data_get($feedMeta, 'google.generated_at_formatted', data_get($feedMeta, 'google.generated_at', '-')) }}</span>
                                            <span class="label label-info">{{ trans('product_feeds::messages.fields.items') }}: {{ data_get($feedMeta, 'google.items_count', '-') }}</span>
                                            <button type="button" class="btn btn-link btn-xs" style="padding: 0; text-decoration: none;" 
                                                onclick="document.getElementById('feed-cache-refresh-google-form').submit();"
                                                title="{{ trans('product_feeds::messages.fields.cache_refresh_google') }}">
                                                <i class="fa fa-refresh"></i>
                                            </button>
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
                                        <p class="form-control-static" style="display: flex; align-items: center; gap: 10px;">
                                            <span class="label label-default">{{ data_get($feedMeta, 'meta.generated_at_formatted', data_get($feedMeta, 'meta.generated_at', '-')) }}</span>
                                            <span class="label label-info">{{ trans('product_feeds::messages.fields.items') }}: {{ data_get($feedMeta, 'meta.items_count', '-') }}</span>
                                            <button type="button" class="btn btn-link btn-xs" style="padding: 0; text-decoration: none;" 
                                                onclick="document.getElementById('feed-cache-refresh-meta-form').submit();"
                                                title="{{ trans('product_feeds::messages.fields.cache_refresh_meta') }}">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </p>
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.trendyol') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.trendyol_enabled') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="trendyol[enabled]" value="0">
                                        <input type="checkbox" name="trendyol[enabled]" value="1" {{ $settings['trendyol']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.trendyol_feed_url') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static"><code>{{ url('/feeds/trendyol.xml') }}</code></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.last_generated') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static" style="display: flex; align-items: center; gap: 10px;">
                                            <span class="label label-default">{{ data_get($feedMeta, 'trendyol.generated_at_formatted', data_get($feedMeta, 'trendyol.generated_at', '-')) }}</span>
                                            <span class="label label-info">{{ trans('product_feeds::messages.fields.items') }}: {{ data_get($feedMeta, 'trendyol.items_count', '-') }}</span>
                                            <button type="button" class="btn btn-link btn-xs" style="padding: 0; text-decoration: none;" 
                                                onclick="document.getElementById('feed-cache-refresh-trendyol-form').submit();"
                                                title="{{ trans('product_feeds::messages.fields.cache_refresh_trendyol') }}">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.trendyol_supplier_id') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="text" name="trendyol[supplier_id]" class="form-control" value="{{ $settings['trendyol']['supplier_id'] }}">
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.hepsiburada') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.hepsiburada_enabled') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="hepsiburada[enabled]" value="0">
                                        <input type="checkbox" name="hepsiburada[enabled]" value="1" {{ $settings['hepsiburada']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.hepsiburada_feed_url') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static"><code>{{ url('/feeds/hepsiburada.xml') }}</code></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.last_generated') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static" style="display: flex; align-items: center; gap: 10px;">
                                            <span class="label label-default">{{ data_get($feedMeta, 'hepsiburada.generated_at_formatted', data_get($feedMeta, 'hepsiburada.generated_at', '-')) }}</span>
                                            <span class="label label-info">{{ trans('product_feeds::messages.fields.items') }}: {{ data_get($feedMeta, 'hepsiburada.items_count', '-') }}</span>
                                            <button type="button" class="btn btn-link btn-xs" style="padding: 0; text-decoration: none;" 
                                                onclick="document.getElementById('feed-cache-refresh-hepsiburada-form').submit();"
                                                title="{{ trans('product_feeds::messages.fields.cache_refresh_hepsiburada') }}">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </p>
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.pinterest') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.pinterest_enabled') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="pinterest[enabled]" value="0">
                                        <input type="checkbox" name="pinterest[enabled]" value="1" {{ $settings['pinterest']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.pinterest_feed_url') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static"><code>{{ url('/feeds/pinterest.xml') }}</code></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.last_generated') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static" style="display: flex; align-items: center; gap: 10px;">
                                            <span class="label label-default">{{ data_get($feedMeta, 'pinterest.generated_at_formatted', data_get($feedMeta, 'pinterest.generated_at', '-')) }}</span>
                                            <span class="label label-info">{{ trans('product_feeds::messages.fields.items') }}: {{ data_get($feedMeta, 'pinterest.items_count', '-') }}</span>
                                            <button type="button" class="btn btn-link btn-xs" style="padding: 0; text-decoration: none;" 
                                                onclick="document.getElementById('feed-cache-refresh-pinterest-form').submit();"
                                                title="{{ trans('product_feeds::messages.fields.cache_refresh_pinterest') }}">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.pinterest_format') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <select name="pinterest[format]" class="form-control">
                                            <option value="tsv" {{ $settings['pinterest']['format'] === 'tsv' ? 'selected' : '' }}>TSV</option>
                                            <option value="csv" {{ $settings['pinterest']['format'] === 'csv' ? 'selected' : '' }}>CSV</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.tiktok') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.tiktok_enabled') }}</label>
                                    <div class="col-sm-8 col-md-5">
                                        <input type="hidden" name="tiktok[enabled]" value="0">
                                        <input type="checkbox" name="tiktok[enabled]" value="1" {{ $settings['tiktok']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.tiktok_feed_url') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static"><code>{{ url('/feeds/tiktok.json') }}</code></p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.last_generated') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <p class="form-control-static" style="display: flex; align-items: center; gap: 10px;">
                                            <span class="label label-default">{{ data_get($feedMeta, 'tiktok.generated_at_formatted', data_get($feedMeta, 'tiktok.generated_at', '-')) }}</span>
                                            <span class="label label-info">{{ trans('product_feeds::messages.fields.items') }}: {{ data_get($feedMeta, 'tiktok.items_count', '-') }}</span>
                                            <button type="button" class="btn btn-link btn-xs" style="padding: 0; text-decoration: none;" 
                                                onclick="document.getElementById('feed-cache-refresh-tiktok-form').submit();"
                                                title="{{ trans('product_feeds::messages.fields.cache_refresh_tiktok') }}">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </p>
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
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_trendyol') }}</label>
                                    <div class="col-sm-4 col-md-3">
                                        <input type="number" min="0" name="cache[trendyol]" class="form-control" value="{{ $settings['cache']['trendyol'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_hepsiburada') }}</label>
                                    <div class="col-sm-4 col-md-3">
                                        <input type="number" min="0" name="cache[hepsiburada]" class="form-control" value="{{ $settings['cache']['hepsiburada'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_pinterest') }}</label>
                                    <div class="col-sm-4 col-md-3">
                                        <input type="number" min="0" name="cache[pinterest]" class="form-control" value="{{ $settings['cache']['pinterest'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_tiktok') }}</label>
                                    <div class="col-sm-4 col-md-3">
                                        <input type="number" min="0" name="cache[tiktok]" class="form-control" value="{{ $settings['cache']['tiktok'] }}">
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
                                        <p class="form-control-static"><strong>Trendyol:</strong> {{ url('/feeds/cron/trendyol') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="form-control-static"><strong>Hepsiburada:</strong> {{ url('/feeds/cron/hepsiburada') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="form-control-static"><strong>Pinterest:</strong> {{ url('/feeds/cron/pinterest') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="form-control-static"><strong>TikTok:</strong> {{ url('/feeds/cron/tiktok') . '?token=' . $settings['cache']['token'] }}</p>
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

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_trendyol') }}</label>
                                    <div class="col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-trendyol-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_trendyol') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_hepsiburada') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-hepsiburada-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_hepsiburada') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_pinterest') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-pinterest-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_pinterest') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_tiktok') }}</label>
                                    <div class="col-sm-8 col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-tiktok-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_tiktok') }}
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
    <form id="feed-cache-refresh-trendyol-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'trendyol']) }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-hepsiburada-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'hepsiburada']) }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-pinterest-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'pinterest']) }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-tiktok-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'tiktok']) }}" style="display:none;">
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
                var currentLabel = '';
                
                @if($settings['google']['category'])
                    currentLabel = decodeHtml({!! json_encode($googleCategoryLabel ?? '') !!});
                @endif

                if (currentLabel) {
                    selectize.addOption({ id: currentVal, text: currentLabel });
                    selectize.setValue(currentVal);
                }
            }

            function decodeHtml(str) {
                var txt = document.createElement('textarea');
                txt.innerHTML = str;
                return txt.value;
            }
        })();
    </script>
@endpush
