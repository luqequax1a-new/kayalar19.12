@php
    // Ensure $errors is available in tab views, similar to other modules
    if (!isset($errors)) {
        $errors = app('view')->shared('errors') ?? new \Illuminate\Support\ViewErrorBag;
    }
@endphp

<div class="tab-pane active" id="general">
    {{ Form::text('name', trans('dynamic_category::attributes.name'), $errors, $dynamicCategory, ['required' => true]) }}

    {{ Form::wysiwyg('description', trans('dynamic_category::attributes.description'), $errors, $dynamicCategory) }}

    @if (auth()->user()->hasAccess('admin.media.index'))
        <div class="form-group">
            <label class="col-md-3 control-label">{{ trans('dynamic_category::attributes.image') }}</label>
            <div class="col-md-9">
                @include('media::admin.image_picker.single', [
                    'title' => trans('dynamic_category::attributes.image'),
                    'inputName' => 'image_id',
                    'file' => optional($dynamicCategory->image ?? null),
                ])
            </div>
        </div>
    @endif

    <div class="form-group {{ $errors->has('slug') ? 'has-error' : '' }}">
        <label for="slug" class="col-md-3 control-label">
            {{ trans('dynamic_category::attributes.slug') }}
        </label>
        <div class="col-md-9">
            <div class="input-group">
                <input type="text"
                       name="slug"
                       id="slug"
                       class="form-control"
                       value="{{ old('slug', optional($dynamicCategory)->slug) }}"
                       required>

                <span class="input-group-btn">
                    <button
                        type="button"
                        class="btn btn-default btn-generate-dynamic-slug"
                        title="Slug oluştur"
                        aria-label="Slug oluştur"
                    >
                        <i class="fa fa-magic" aria-hidden="true"></i>
                    </button>
                </span>
            </div>

            @if ($errors->has('slug'))
                <span class="help-block">{{ $errors->first('slug') }}</span>
            @endif
        </div>
    </div>

    {{ Form::checkbox('is_active', trans('dynamic_category::attributes.is_active'), trans('dynamic_category::dynamic_categories.form.enable_dynamic_category'), $errors, $dynamicCategory) }}

    {{ Form::text('meta_title', trans('dynamic_category::attributes.meta_title'), $errors, $dynamicCategory) }}
    {{ Form::textarea('meta_description', trans('dynamic_category::attributes.meta_description'), $errors, $dynamicCategory, ['rows' => 3]) }}

    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('dynamic_category::dynamic_categories.serp_preview_title') }}</label>
        <div class="col-md-9">
            <div id="serp-preview" class="serp-preview serp-preview--google">
                <div class="serp-preview__inner">
                    <div class="serp-preview__brand-row">
                        <img class="serp-preview__favicon" src="{{ url('/favicon.ico') }}" alt="" loading="lazy">
                        <div class="serp-preview__brand-text">
                            <div class="serp-preview__brand-main" data-serp-host>{{ parse_url(url('/'), PHP_URL_HOST) }}</div>
                            <div class="serp-preview__brand-sub" data-serp-breadcrumb></div>
                        </div>
                    </div>

                    <div class="serp-preview__result">
                        <div class="serp-title"></div>
                        <div class="serp-description"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script type="module">
        (function () {
            const baseUrl = '{{ url('/') }}';
            const categoryUrlTemplate = @json(route('categories.products.index', ['category' => 'SLUG_PLACEHOLDER']));

            const nameInput = document.querySelector('input[name="name"]');
            const slugInput = document.querySelector('input[name="slug"]');
            const metaTitleInput = document.querySelector('input[name="meta_title"]');
            const metaDescriptionInput = document.querySelector('textarea[name="meta_description"]');
            const slugGenerateButton = document.querySelector('.btn-generate-dynamic-slug');

            const serpTitle = document.querySelector('#serp-preview .serp-title');
            const serpDescription = document.querySelector('#serp-preview .serp-description');
            const serpHost = document.querySelector('#serp-preview [data-serp-host]');
            const serpBreadcrumb = document.querySelector('#serp-preview [data-serp-breadcrumb]');

            function parseHost(url) {
                try {
                    return (new URL(url)).host || '';
                } catch (_) {
                    return '';
                }
            }

            function stripProtocol(url) {
                try {
                    return (url || '').toString().replace(/^https?:\/\//i, '').replace(/\/$/, '');
                } catch (_) {
                    return url || '';
                }
            }

            function getTitle() {
                const metaTitle = metaTitleInput?.value?.trim();
                if (metaTitle) {
                    return metaTitle;
                }

                return nameInput?.value?.trim() || '';
            }

            function autoUpdateSlugFromName() {
                if (!nameInput || !slugInput) {
                    return;
                }

                const currentSlug = (slugInput.value || '').trim();
                const name = (nameInput.value || '').trim();

                // Eğer slug zaten doluysa, kullanıcı manuel müdahale etmiş olabilir; elle değiştirmeyelim.
                if (!name || currentSlug) {
                    return;
                }

                // Admin tarafındaki global generateSlug helper'ını kullan, yoksa basit bir fallback uygula.
                if (typeof window.generateSlug === 'function') {
                    slugInput.value = window.generateSlug(name);
                } else if (typeof generateSlug === 'function') {
                    slugInput.value = generateSlug(name);
                } else {
                    slugInput.value = name
                        .toLowerCase()
                        .normalize('NFD').replace(/\p{Diacritic}/gu, '')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            }

            function getUrl() {
                const rawSlug = slugInput?.value?.trim() || '';

                // When there is no slug yet, show a generic category URL without placeholder
                if (!rawSlug) {
                    return categoryUrlTemplate.replace('SLUG_PLACEHOLDER', '');
                }

                // Use the same route pattern as the real category products page
                const encoded = encodeURIComponent(rawSlug);
                return categoryUrlTemplate.replace('SLUG_PLACEHOLDER', encoded);
            }

            function getBreadcrumb() {
                const url = getUrl();
                const host = parseHost(url) || parseHost(baseUrl) || '';

                let path = '';
                try {
                    path = (new URL(url)).pathname || '';
                } catch (_) {
                    path = '';
                }

                const parts = path
                    .split('/')
                    .filter(Boolean)
                    .map((p) => decodeURIComponent(p));

                const breadcrumb = [host]
                    .concat(parts)
                    .join(' › ');

                return breadcrumb;
            }

            function getDescription() {
                return metaDescriptionInput?.value?.trim() || '';
            }

            function updateSerp() {
                if (!serpTitle || !serpDescription) {
                    return;
                }

                const url = getUrl();
                const host = parseHost(url) || parseHost(baseUrl) || '';

                serpTitle.textContent = getTitle();
                serpDescription.textContent = getDescription();

                if (serpHost) {
                    serpHost.textContent = host;
                }

                if (serpBreadcrumb) {
                    serpBreadcrumb.textContent = getBreadcrumb();
                }
            }

            [nameInput, slugInput, metaTitleInput, metaDescriptionInput].forEach((input) => {
                if (!input) return;
                const handler = () => {
                    if (input === nameInput) {
                        autoUpdateSlugFromName();
                    }
                    updateSerp();
                };

                input.addEventListener('input', handler);
                input.addEventListener('change', handler);
            });

            if (slugGenerateButton) {
                slugGenerateButton.addEventListener('click', function () {
                    if (!nameInput || !slugInput) return;

                    const name = (nameInput.value || '').trim();
                    if (!name) return;

                    // Üretici butonu her zaman isme göre slug'ı günceller.
                    if (typeof window.generateSlug === 'function') {
                        slugInput.value = window.generateSlug(name);
                    } else if (typeof generateSlug === 'function') {
                        slugInput.value = generateSlug(name);
                    } else {
                        slugInput.value = name
                            .toLowerCase()
                            .normalize('NFD').replace(/\p{Diacritic}/gu, '')
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                    }

                    updateSerp();
                });
            }

            updateSerp();
        })();
    </script>
@endpush

@push('styles')
    <style>
        #serp-preview.serp-preview--google {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 14px 16px 16px;
            max-width: 560px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .serp-preview__inner {
            font-size: 13px;
            color: #1f2933;
        }

        .serp-preview__brand-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .serp-preview__favicon {
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: radial-gradient(circle at 30% 30%, #4285f4, #3367d6);
            object-fit: cover;
        }

        .serp-preview__brand-text {
            line-height: 1.2;
        }

        .serp-preview__brand-main {
            font-size: 12px;
            color: #202124;
        }

        .serp-preview__brand-sub {
            font-size: 11px;
            color: #5f6368;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 520px;
        }

        #serp-preview .serp-title {
            font-size: 18px;
            line-height: 1.3;
            color: #1a0dab;
            margin-bottom: 3px;
            font-weight: 400;
            word-break: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        #serp-preview .serp-description {
            font-size: 12px;
            line-height: 1.5;
            color: #4b5563;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        #serp-preview .serp-title:empty,
        #serp-preview .serp-description:empty {
            min-height: 12px;
            background: repeating-linear-gradient(90deg, #f3f4f6, #f3f4f6 80px, #ffffff 80px, #ffffff 96px);
            border-radius: 4px;
        }
    </style>
@endpush
