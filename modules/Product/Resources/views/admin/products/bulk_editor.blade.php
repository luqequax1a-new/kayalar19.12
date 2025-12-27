@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'Toplu Ürün Yönetimi')

    <li><a href="{{ route('admin.products.index') }}">{{ trans('product::products.products') }}</a></li>
    <li class="active">Toplu Güncelleme</li>
@endcomponent

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="bulk-editor-container">
                <div id="bulk-editor-alert" class="alert" style="display:none;"></div>

                <div class="row display-flex">
                    <div class="col-md-8">
                        <div class="bulk-card glass-card">
                            <div class="bulk-card-header">
                                <h4 class="m-0"><i class="fa fa-filter"></i> Filtreleme Koşulları</h4>
                                <div class="bulk-combine-toggle">
                                    <span class="mr-2">Birleştirme:</span>
                                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                        <label class="btn btn-default btn-xs active">
                                            <input type="radio" name="bulk-filter-combine" value="and" checked> VE
                                        </label>
                                        <label class="btn btn-default btn-xs">
                                            <input type="radio" name="bulk-filter-combine" value="or"> VEYA
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="bulk-card-body">
                                <div id="bulk-filter-rows" class="bulk-rows-container"></div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-round" id="bulk-add-filter">
                                        <i class="fa fa-plus"></i> Yeni Filtre Ekle
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="bulk-card glass-card mt-4">
                            <div class="bulk-card-header">
                                <h4 class="m-0"><i class="fa fa-magic"></i> Güncelleme Aksiyonları</h4>
                            </div>
                            <div class="bulk-card-body">
                                <div id="bulk-action-rows" class="bulk-rows-container"></div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-outline-success btn-sm btn-round" id="bulk-add-action">
                                        <i class="fa fa-plus"></i> Yeni Aksiyon Ekle
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="bulk-card glass-card sticky-card">
                            <div class="bulk-card-header">
                                <h4 class="m-0"><i class="fa fa-list"></i> Eşleşen Ürünler</h4>
                            </div>
                            <div class="bulk-card-body p-0">
                                <div id="bulk-matching-summary" class="bulk-summary-bar">
                                    Ürün Bulunamadı
                                </div>
                                <div class="bulk-preview-list-container">
                                    <ul id="bulk-matching-list" class="bulk-preview-list"></ul>
                                </div>
                            </div>
                            <div class="bulk-card-footer">
                                <button type="button" id="bulk-apply" class="btn btn-primary btn-block btn-lg" disabled>
                                    Güncellemeyi Uygula
                                </button>
                                <small class="text-muted text-center d-block mt-2">
                                    <i class="fa fa-info-circle"></i> Bu işlem geri alınamaz.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bulk-editor-container {
            padding: 10px 0;
            font-family: 'Inter', sans-serif;
        }

        .display-flex {
            display: flex;
            flex-wrap: wrap;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .bulk-card {
            margin-bottom: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .bulk-card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }

        .bulk-card-header h4 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .bulk-card-body {
            padding: 20px;
            flex: 1;
        }

        .bulk-card-footer {
            padding: 15px 20px;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
        }

        .bulk-rows-container {
            min-height: 50px;
        }

        .bulk-row-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            padding: 10px;
            background: #fff;
            border: 1px solid #eef0f2;
            border-radius: 8px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bulk-row-item select, .bulk-row-item input {
            border-radius: 6px !important;
            border: 1px solid #dcdfe6;
            transition: border-color 0.2s;
        }

        .bulk-row-item select:focus, .bulk-row-item input:focus {
            border-color: #409eff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(64,158,255,0.1);
        }

        .bulk-remove-btn {
            background: none;
            border: none;
            color: #f56c6c;
            font-size: 18px;
            cursor: pointer;
            padding: 0 5px;
            transition: color 0.2s;
        }

        .bulk-remove-btn:hover {
            color: #ff4949;
        }

        .bulk-summary-bar {
            padding: 12px 20px;
            font-weight: 600;
            text-align: center;
            background: #f8f9fa;
        }

        .bulk-summary-bar.alert-success { background: #e1f3d8; color: #67c23a; }
        .bulk-summary-bar.alert-warning { background: #fdf6ec; color: #e6a23c; }
        .bulk-summary-bar.alert-danger { background: #fef0f0; color: #f56c6c; }

        .bulk-preview-list-container {
            max-height: 450px;
            overflow-y: auto;
        }

        .bulk-preview-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .bulk-preview-item {
            display: flex;
            padding: 12px 15px;
            border-bottom: 1px solid #f0f2f5;
            align-items: center;
            transition: background 0.2s;
        }

        .bulk-preview-item:hover {
            background: #f9fbff;
        }

        .bulk-preview-img {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 12px;
            background: #f5f7fa;
            border: 1px solid #eee;
        }

        .bulk-preview-info {
            flex: 1;
            min-width: 0;
        }

        .preview-name {
            font-size: 13px;
            font-weight: 500;
            color: #2c3e50;
            margin: 0 0 2px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .preview-meta {
            font-size: 11px;
            color: #909399;
            margin: 0;
        }

        .preview-price {
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-align: right;
        }

        .preview-price .previous-price {
            text-decoration: line-through;
            color: #909399;
            font-size: 11px;
            font-weight: 400;
            margin-right: 6px;
            display: block;
        }

        .preview-price .special-price {
            color: #f56c6c;
            display: block;
        }

        .sticky-card {
            position: sticky;
            top: 20px;
        }

        .btn-round { border-radius: 20px; padding: 5px 15px; }
        .m-0 { margin: 0; }
        .mt-4 { margin-top: 20px; }
        .mt-3 { margin-top: 15px; }

        .form-control-sm { height: 30px; font-size: 12px; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const brandOptions = @json($brands ?? []);
            const flatCategories = @json($flatCategories ?? []);
            const categoryTreeData = @json($categoryTree ?? []);

            let filterIndex = 0;
            let actionIndex = 0;

            function renderFilterRow(id) {
                const row = document.createElement('div');
                row.className = 'bulk-row-item';
                row.dataset.id = id;

                row.innerHTML = `
                    <select class="form-control form-control-sm bulk-attr" style="width: 30%;">
                        <option value="name">Ürün Adı</option>
                        <option value="sku">SKU</option>
                        <option value="brand">Marka</option>
                        <option value="category">Kategori</option>
                        <option value="price">Fiyat</option>
                        <option value="qty">Stok Adedi</option>
                        <option value="status">Aktiflik Durumu</option>
                    </select>
                    <select class="form-control form-control-sm bulk-op" style="width: 25%;">
                    </select>
                    <div class="bulk-value-wrap" style="flex:1;">
                    </div>
                    <button type="button" class="bulk-remove-btn" title="Kaldır">&times;</button>
                `;

                document.getElementById('bulk-filter-rows').appendChild(row);

                row.querySelector('.bulk-attr').addEventListener('change', function () {
                    updateFilterRowFields(row, this.value);
                });

                row.querySelector('.bulk-remove-btn').addEventListener('click', function () {
                    row.remove();
                    triggerPreview();
                });

                updateFilterRowFields(row, row.querySelector('.bulk-attr').value);
            }

            function updateFilterRowFields(row, attr) {
                const opSelect = row.querySelector('.bulk-op');
                const valueWrap = row.querySelector('.bulk-value-wrap');
                valueWrap.innerHTML = '';

                if (attr === 'name' || attr === 'sku') {
                    opSelect.innerHTML = `
                        <option value="contains">İçerir</option>
                        <option value="not_contains">İçermez</option>
                        <option value="=">Eşittir</option>
                        <option value="!=">Eşit Değildir</option>
                    `;
                    valueWrap.innerHTML = '<input type="text" class="form-control form-control-sm bulk-val" placeholder="Değer..." />';
                } else if (attr === 'brand' || attr === 'status') {
                    opSelect.innerHTML = '<option value="=">Şu Olan</option><option value="!=">Şu Olmayan</option>';
                    const options = attr === 'brand' ? brandOptions : {1: 'Aktif', 0: 'Pasif'};
                    const sel = createSelect(options, 'Seçin');
                    valueWrap.appendChild(sel);
                } else if (attr === 'category') {
                    opSelect.innerHTML = '<option value="in">Şunların İçinde</option><option value="not_in">Şunların Dışında</option>';
                    const sel = createSelect(flatCategories, 'Tümü');
                    valueWrap.appendChild(sel);
                } else if (attr === 'price' || attr === 'qty') {
                    opSelect.innerHTML = `
                        <option value=">=">&ge; (Büyük Eşit)</option>
                        <option value="<=">&le; (Küçük Eşit)</option>
                        <option value=">">&gt; (Büyük)</option>
                        <option value="<">&lt; (Küçük)</option>
                        <option value="=">= (Eşit)</option>
                        <option value="!=">&ne; (Eşit Değil)</option>
                    `;
                    valueWrap.innerHTML = `<input type="number" step="0.01" class="form-control form-control-sm bulk-val" placeholder="${attr === 'price' ? '0.00' : '0'}" />`;
                }

                registerRowEvents(row);
                triggerPreview();
            }

            function renderActionRow(id) {
                const row = document.createElement('div');
                row.className = 'bulk-row-item';
                row.dataset.id = id;

                row.innerHTML = `
                    <select class="form-control form-control-sm bulk-action-attr" style="width: 25%;">
                        <option value="price">Satış Fiyatı</option>
                        <option value="special_price">Kampanyalı Fiyat</option>
                        <option value="qty">Stok Miktarı</option>
                        <option value="status">Aktiflik Durumu</option>
                        <option value="brand">Marka Atama</option>
                        <option value="primary_category">Kategori Değiştir</option>
                        <option value="manage_stock">Stok Yönetimi</option>
                        <option value="name">Ürün Adı</option>
                        <option value="short_description">Kısa Açıklama</option>
                        <option value="description">Ürün Açıklaması</option>
                        <option value="sku">SKU Kodu</option>
                    </select>
                    <div class="bulk-action-mode-wrap" style="width: 20%;"></div>
                    <div class="bulk-action-value-wrap" style="flex:1; display:flex; gap:5px;"></div>
                    <button type="button" class="bulk-remove-btn" title="Kaldır">&times;</button>
                `;

                document.getElementById('bulk-action-rows').appendChild(row);

                row.querySelector('.bulk-action-attr').addEventListener('change', function () {
                    updateActionRowFields(row, this.value);
                });

                row.querySelector('.bulk-remove-btn').addEventListener('click', function () {
                    row.remove();
                    updateApplyButtonState();
                });

                updateActionRowFields(row, row.querySelector('.bulk-action-attr').value);
            }

            function updateActionRowFields(row, attr) {
                const modeWrap = row.querySelector('.bulk-action-mode-wrap');
                const valueWrap = row.querySelector('.bulk-action-value-wrap');
                modeWrap.innerHTML = '';
                valueWrap.innerHTML = '';

                if (attr === 'price') {
                    modeWrap.innerHTML = `
                        <select class="form-control form-control-sm bulk-action-mode">
                            <option value="set">Yeni Değer Ata</option>
                            <option value="increase_percent">% Oranında Arttır</option>
                            <option value="decrease_percent">% Oranında Azalt</option>
                        </select>`;
                    valueWrap.innerHTML = '<input type="number" step="0.01" class="form-control form-control-sm bulk-action-value" placeholder="Miktar..." />';
                } else if (attr === 'special_price') {
                    modeWrap.innerHTML = `
                        <select class="form-control form-control-sm bulk-action-mode">
                            <option value="set">Fiyatı Belirle</option>
                            <option value="clear">Kampanyayı Kaldır</option>
                        </select>`;
                    valueWrap.innerHTML = '<input type="number" step="0.01" class="form-control form-control-sm bulk-action-value" placeholder="Miktar..." />';
                } else if (attr === 'qty') {
                    modeWrap.innerHTML = `
                        <select class="form-control form-control-sm bulk-action-mode">
                            <option value="set">Stok Belirle</option>
                            <option value="increase">Miktarı Arttır (+)</option>
                            <option value="decrease">Miktarı Azalt (-)</option>
                        </select>`;
                    valueWrap.innerHTML = '<input type="number" step="1" class="form-control form-control-sm bulk-action-value" placeholder="Adet..." />';
                } else if (attr === 'status' || attr === 'manage_stock') {
                    modeWrap.innerHTML = `<select class="form-control form-control-sm bulk-action-mode"><option value="set">Değeri Değiştir</option></select>`;
                    valueWrap.appendChild(createSelect({1: 'Aktif / Evet', 0: 'Pasif / Hayır'}, 'Durum Seçin', 'bulk-action-value'));
                } else if (attr === 'brand') {
                    modeWrap.innerHTML = `<select class="form-control form-control-sm bulk-action-mode"><option value="set">Markayı Güncelle</option><option value="clear">Markayı Kaldır</option></select>`;
                    valueWrap.appendChild(createSelect(brandOptions, 'Marka Seçin', 'bulk-action-value'));
                } else if (attr === 'primary_category') {
                    modeWrap.innerHTML = `<select class="form-control form-control-sm bulk-action-mode"><option value="set">Kategoriyi Değiştir</option></select>`;
                    valueWrap.appendChild(createSelect(flatCategories, 'Kategori Seçin', 'bulk-action-value'));
                } else if (attr === 'name' || attr === 'short_description' || attr === 'description') {
                    modeWrap.innerHTML = `
                        <select class="form-control form-control-sm bulk-action-mode">
                            <option value="set">Yeni Metin Ata</option>
                            <option value="search_replace">Metin Bul ve Değiştir</option>
                        </select>`;
                    
                    const updateTextUI = () => {
                        const mode = row.querySelector('.bulk-action-mode').value;
                        valueWrap.innerHTML = '';
                        if (mode === 'set') {
                            valueWrap.innerHTML = '<input type="text" class="form-control form-control-sm bulk-action-value" placeholder="Yeni metin..." />';
                        } else {
                            valueWrap.innerHTML = `
                                <input type="text" class="form-control form-control-sm bulk-search" placeholder="Aranan Kelime" style="width:50%">
                                <input type="text" class="form-control form-control-sm bulk-replace" placeholder="Yeni Kelime" style="width:50%">
                            `;
                        }
                        registerActionEvents(row);
                    };
                    
                    row.querySelector('.bulk-action-mode').addEventListener('change', updateTextUI);
                    updateTextUI();
                } else if (attr === 'sku') {
                    modeWrap.innerHTML = `
                        <select class="form-control form-control-sm bulk-action-mode">
                            <option value="set">Yeni SKU Belirle</option>
                            <option value="prefix">Başına Ekle (Prefix)</option>
                            <option value="suffix">Sonuna Ekle (Suffix)</option>
                        </select>`;
                    valueWrap.innerHTML = '<input type="text" class="form-control form-control-sm bulk-action-value" placeholder="Metin..." />';
                }

                registerActionEvents(row);
                updateApplyButtonState();
            }

            function createSelect(options, placeholder, className = 'bulk-val') {
                const sel = document.createElement('select');
                sel.className = `form-control form-control-sm ${className}`;
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = placeholder;
                sel.appendChild(empty);
                Object.keys(options).forEach(id => {
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = options[id];
                    sel.appendChild(opt);
                });
                return sel;
            }

            function registerRowEvents(row) {
                row.querySelectorAll('input, select').forEach(el => {
                    el.addEventListener('change', triggerPreview);
                    if (el.tagName === 'INPUT') {
                        el.addEventListener('keyup', debounce(triggerPreview, 400));
                    }
                });
            }

            function registerActionEvents(row) {
                row.querySelectorAll('input, select').forEach(el => {
                    el.addEventListener('change', updateApplyButtonState);
                    if (el.tagName === 'INPUT') {
                        el.addEventListener('keyup', debounce(updateApplyButtonState, 300));
                    }
                });
            }

            function collectFilters() {
                const rows = document.querySelectorAll('#bulk-filter-rows .bulk-row-item');
                const result = [];
                rows.forEach(row => {
                    const attr = row.querySelector('.bulk-attr').value;
                    const op = row.querySelector('.bulk-op').value;
                    const val = row.querySelector('.bulk-val')?.value;
                    if (attr && op && val !== undefined && val !== '') {
                        result.push({ attribute: attr, operator: op, value: val });
                    }
                });
                return result;
            }

            function collectActions() {
                const rows = document.querySelectorAll('#bulk-action-rows .bulk-row-item');
                const result = [];
                rows.forEach(row => {
                    const attr = row.querySelector('.bulk-action-attr').value;
                    const mode = row.querySelector('.bulk-action-mode').value;
                    let val = null;

                    if (mode === 'search_replace') {
                        val = {
                            search: row.querySelector('.bulk-search')?.value,
                            replace: row.querySelector('.bulk-replace')?.value
                        };
                    } else {
                        val = row.querySelector('.bulk-action-value')?.value;
                    }

                    if (attr && mode) {
                        result.push({ attribute: attr, mode: mode, value: val });
                    }
                });
                return result;
            }

            const triggerPreview = debounce(function () {
                const filters = collectFilters();
                const combine = document.querySelector('input[name="bulk-filter-combine"]:checked')?.value || 'and';

                axios.get('{{ route('admin.products.bulk_preview') }}', {
                    params: { filters, combine }
                }).then(res => {
                    const { total, items } = res.data;
                    const summary = document.getElementById('bulk-matching-summary');
                    const list = document.getElementById('bulk-matching-list');
                    list.innerHTML = '';

                    if (total === 0) {
                        summary.className = 'bulk-summary-bar alert-warning';
                        summary.textContent = 'Eşleşen Ürün Yok';
                    } else {
                        summary.className = 'bulk-summary-bar alert-success';
                        summary.textContent = `${total} Ürün Etkilenecek`;

                        items.forEach(item => {
                            const li = document.createElement('li');
                            li.className = 'bulk-preview-item';
                            
                            const priceHtml = item.has_special_price 
                                ? `<span class="previous-price">${item.price_formatted}</span><span class="special-price">${item.special_price_formatted}</span>`
                                : `<span>${item.price_formatted || ''}</span>`;

                            li.innerHTML = `
                                <img src="${item.image || '{{ asset('build/assets/image-placeholder.png') }}'}" class="bulk-preview-img">
                                <div class="bulk-preview-info">
                                    <p class="preview-name">${_.escape(item.name || 'İsimsiz')}</p>
                                    <p class="preview-meta">${_.escape(item.brand || '')} | ${_.escape(item.category || '')}</p>
                                </div>
                                <div class="preview-price">${priceHtml}</div>
                            `;
                            list.appendChild(li);
                        });
                    }
                    updateApplyButtonState();
                });
            }, 400);

            function updateApplyButtonState() {
                const actions = collectActions();
                const summary = document.getElementById('bulk-matching-summary');
                const hasMatch = summary.classList.contains('alert-success');
                document.getElementById('bulk-apply').disabled = !hasMatch || actions.length === 0;
            }

            function debounce(fn, wait) {
                let t;
                return function () {
                    const ctx = this, args = arguments;
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(ctx, args), wait);
                };
            }

            function showAlert(type, message) {
                const el = document.getElementById('bulk-editor-alert');
                el.className = `alert alert-${type}`;
                el.textContent = message;
                el.style.display = 'block';
                setTimeout(() => el.style.display = 'none', 5000);
            }

            document.getElementById('bulk-add-filter').addEventListener('click', () => renderFilterRow(filterIndex++));
            document.getElementById('bulk-add-action').addEventListener('click', () => renderActionRow(actionIndex++));
            document.querySelectorAll('input[name="bulk-filter-combine"]').forEach(el => el.addEventListener('change', triggerPreview));

            document.getElementById('bulk-apply').addEventListener('click', function () {
                const btn = this;
                if (!confirm('Emin misiniz? Bu işlem seçili filtreye göre tüm ürünleri etkileyecektir.')) return;

                btn.disabled = true;
                axios.post('{{ route('admin.products.bulk_update') }}', {
                    filters: collectFilters(),
                    actions: collectActions(),
                    combine: document.querySelector('input[name="bulk-filter-combine"]:checked').value
                }).then(res => {
                    showAlert('success', `${res.data.updated} ürün başarıyla güncellendi.`);
                    triggerPreview();
                }).catch(() => {
                    showAlert('danger', 'Güncelleme sırasında hata oluştu.');
                }).finally(() => {
                    btn.disabled = false;
                });
            });

            // Initial rows
            renderFilterRow(filterIndex++);
            renderActionRow(actionIndex++);

        })();
    </script>
@endpush
