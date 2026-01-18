@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product::products.products'))

    <li class="active">{{ trans('product::products.products') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons')
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-actions btn-create">
            <i class="fa fa-plus"></i> {{ trans('admin::resource.create', ['resource' => trans('product::products.product')]) }}
        </a>

        <a href="{{ route('admin.products.bulk_editor') }}" class="btn btn-success btn-actions">
            <i class="fa fa-magic"></i> Toplu Yönetim
        </a>

        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Excel / CSV İşlemleri <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <li>
                    <a href="#" id="btn-export-products-excel">Ürünleri Excel Dışa Aktar</a>
                </li>
                <li>
                    <a href="#" id="btn-export-products-csv">Ürünleri CSV Dışa Aktar</a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                    <a href="#" onclick="excelOpenImportModal(); return false;">Excel ile Ürün Yükle / Güncelle</a>
                </li>
                <li>
                    <a href="{{ route('admin.products.csv.simple_import.form') }}">CSV ile Ürün Yükle / Güncelle</a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                    <a href="#" id="btn-export-trendyol">Trendyol Excel Dışa Aktar</a>
                </li>
                <li>
                    <a href="#" id="btn-export-hepsiburada">Hepsiburada Excel Dışa Aktar</a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                    <a href="#" id="btn-export-variants-csv">Varyantları CSV Dışa Aktar</a>
                </li>
            </ul>
        </div>
    @endslot
    @slot('resource', 'products')
    @slot('name', trans('product::products.product'))
    @slot('filters_form', '#product-filters')

    @slot('filters')
        <form id="product-filters" class="form-inline">
            <div class="form-group" style="margin-right: 8px;">
                <label for="filter-brand" style="margin-right:4px;">Marka</label>
                <select name="brand_id" id="filter-brand" class="form-control input-sm">
                    <option value="">Tümü</option>
                    @isset($brands)
                        @foreach ($brands as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="form-group" style="margin-right: 8px;">
                <label for="filter-category" style="margin-right:4px;">Kategori</label>
                <select name="category_id" id="filter-category" class="form-control input-sm">
                    <option value="">Tümü</option>
                    @isset($categories)
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <button type="button" id="btn-sort-mode" class="btn btn-default btn-sm" style="display:none;">
                <i class="fa fa-sort"></i> Sırala
            </button>
        </form>
    @endslot

    @slot('thead')
        @include('product::admin.products.partials.thead', ['name' => 'products-index'])
    @endslot
@endcomponent

@if (session()->has('exit_flash'))
    @push('notifications')
        <div class="alert alert-success fade in alert-dismissible clearfix">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM11.25 8C11.25 7.59 11.59 7.25 12 7.25C12.41 7.25 12.75 7.59 12.75 8V13C12.75 13.41 12.41 13.75 12 13.75C11.59 13.75 11.25 13.41 11.25 13V8ZM12.92 16.38C12.87 16.51 12.8 16.61 12.71 16.71C12.61 16.8 12.5 16.87 12.38 16.92C12.26 16.97 12.13 17 12 17C11.87 17 11.74 16.97 11.62 16.92C11.5 16.87 11.39 16.8 11.29 16.71C11.2 16.61 11.13 16.51 11.08 16.38C11.03 16.26 11 16.13 11 16C11 15.87 11.03 15.74 11.08 15.62C11.13 15.5 11.2 15.39 11.29 15.29C11.39 15.2 11.5 15.13 11.62 15.08C11.86 14.98 12.14 14.98 12.38 15.08C12.5 15.13 12.61 15.2 12.71 15.29C12.8 15.39 12.87 15.5 12.92 15.62C12.97 15.74 13 15.87 13 16C13 16.13 12.97 16.26 12.92 16.38Z" fill="#555555"/>
            </svg>
            
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M5.00082 14.9995L14.9999 5.00041" stroke="#555555" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14.9999 14.9996L5.00082 5.00049" stroke="#555555" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <span class="alert-text">{{ session('exit_flash') }}</span>
        </div>
    @endpush
@endif

    @push('scripts')
    <script type="module">
        const allBrands = @json($brands ?? []);
        DataTable.set('#products-table .table', {
            routePrefix: 'products',
            routes: {
                table: 'table',
                destroy: 'destroy',
            }
        });

        const dt = new DataTable('#products-table .table', {
            stateSave: false,
            order: [[2, 'desc']],
            lengthMenu: [[10, 20, 50, 100, 200, -1], [10, 20, 50, 100, 200, 'Tümü']],
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { 
                    data: 'sort_handle',
                    orderable: false, 
                    searchable: false, 
                    width: '30px', 
                    className: 'sort-handle-col',
                    visible: false,
                    defaultContent: ''
                },
                { data: 'id', width: '5%' },
                { data: 'thumbnail', orderable: false, searchable: false, width: '10%' },
                { data: 'name', name: 'translations.name', class: 'name', orderable: false, defaultContent: '' },
                { data: 'brand', name: 'brand.translations.name', orderable: false, searchable: false },
                { data: 'default_category', orderable: false, searchable: false },
                { data: 'price', searchable: false },
                { data: 'in_stock', name: 'in_stock', searchable: false, className: 'stock-cell' },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'actions', orderable: false, searchable: false },
            ],
            columnDefs: [
                {
                    targets: 1,
                    visible: false
                }
            ],
            drawCallback: function(settings) {
                console.log('DataTable draw callback - satır sayısı:', this.api().rows().count());
                console.log('Handle sayısı draw sonrası:', $('.sort-handle').length);
            }
        });

        const $brandFilter = $('#filter-brand');
        const $categoryFilter = $('#filter-category');
        let sortingMode = false;
        let sortableInstance = null;
        let currentCategoryId = null;

        $brandFilter.on('change', function () {
            if (!sortingMode) {
                DataTable.reload('#products-table .table');
            }
        });

        $categoryFilter.on('change', function () {
            const categoryId = $(this).val();
            currentCategoryId = categoryId;
            if (categoryId) {
                $('#btn-sort-mode').show();
            } else {
                $('#btn-sort-mode').hide();
                if (sortingMode) {
                    exitSortingMode();
                }
            }
            if (!sortingMode) {
                DataTable.reload('#products-table .table');
            }
        });

        $(document).on('click', '#btn-sort-mode', function(e) {
            console.log('=== SIRALA/KAYDET BUTONU TIKLANDI ===');
            console.log('sortingMode:', sortingMode);
            console.log('currentCategoryId:', currentCategoryId);
            console.log('Buton class:', $(this).attr('class'));
            
            // KAYDET modu (yeşil buton)
            if ($(this).hasClass('btn-success')) {
                console.log('>>> KAYDET MODU <<<');
                e.preventDefault();
                e.stopPropagation();
                
                if (!currentCategoryId || !sortingMode) {
                    console.log('Kategori yok veya sorting mode değil');
                    return;
                }
                
                const orderedIds = [];
                $('#products-table .table tbody tr').each(function() {
                    const id = $(this).find('.delete').data('id');
                    if (id) orderedIds.push(id);
                });
                
                console.log('Kaydedilecek ürün sayısı:', orderedIds.length);
                console.log('Ürün IDleri:', orderedIds);
                
                if (orderedIds.length === 0) {
                    alert('Sıralanacak ürün bulunamadı');
                    return;
                }
                
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...');
                
                axios.post(`${FleetCart.baseUrl}/admin/categories/${currentCategoryId}/product-order`, {
                    ordered_product_ids: orderedIds
                })
                .then(() => {
                    console.log('Kaydetme başarılı!');
                    exitSortingMode();
                    DataTable.reload('#products-table .table');
                })
                .catch((error) => {
                    console.error('Kaydetme hatası:', error);
                    alert('Sıralama kaydedilemedi: ' + (error.response?.data?.message || error.message));
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Kaydet');
                });
                
                return;
            }
            
            // SIRALA modu (gri buton)
            console.log('>>> SIRALA MODU <<<');
            
            if (!currentCategoryId) {
                console.log('Kategori seçilmemiş, return');
                return;
            }
            
            sortingMode = true;
            $(this).text('Kaydet').removeClass('btn-default').addClass('btn-success');
            
            console.log('Sütunları gösteriyorum...');
            
            // Handle kolonunu DataTables API ile göster (DOM show/hide kolon hizasını bozar)
            try {
                dt.api.column(1).visible(true);
                dt.api.columns.adjust();
            } catch (err) {
                console.error('Handle kolonu görünür yapılırken hata:', err);
            }
            
            console.log('TH sort-handle-col sayısı:', $('th.sort-handle-col').length);
            console.log('TD sort-handle-col sayısı:', $('td.sort-handle-col').length);
            console.log('sort-handle div sayısı:', $('.sort-handle').length);
            
            // Handle'ları zorla göster
            setTimeout(function() {
                $('.sort-handle').each(function(index) {
                    console.log('Handle ' + index + ':', $(this).length, 'display:', $(this).css('display'));
                    $(this).attr('style', 'display: block !important; cursor: move; text-align: center; padding: 8px;');
                });
                console.log('Görünen handle sayısı:', $('.sort-handle:visible').length);
            }, 200);
            
            // DataTable kontrollerini gizle
            $('.dataTables_length, .dataTables_filter, .dataTables_paginate').hide();
            $('#filter-brand').prop('disabled', true);
            $('#filter-category').prop('disabled', true);
            
            // Pagination'ı kapat - tüm ürünleri göster
            try {
                dt.api.page.len(-1).draw(false);
                dt.api.columns.adjust();
                console.log('DataTable pagination kapatıldı (Tümü)');
            } catch (err) {
                console.error('Pagination kapatma hatası:', err);
            }
            
            const $cancelBtn = $('<button type="button" class="btn btn-default btn-sm" id="btn-cancel-sort" style="margin-left:8px;"><i class="fa fa-times"></i> Vazgeç</button>');
            $(this).after($cancelBtn);
            
            console.log('window.Sortable var mı?', !!window.Sortable);
            console.log('sortableInstance var mı?', !!sortableInstance);
            
            // Sortable'ı biraz gecikmeyle oluştur (DataTable render tamamlansın)
            setTimeout(function() {
                console.log('=== SORTABLE OLUŞTURMA AŞAMASI ===');
                
                // Handle'ları tekrar kontrol et ve göster
                const $handles = $('.sort-handle');
                console.log('Toplam handle sayısı:', $handles.length);
                
                $handles.each(function(index) {
                    const $handle = $(this);
                    console.log('Handle ' + index + ' HTML:', $handle.html());
                    $handle.attr('style', 'display: block !important; cursor: move; text-align: center; padding: 8px;');
                });
                
                console.log('Görünen handle sayısı:', $('.sort-handle:visible').length);
                
                if (window.Sortable && !sortableInstance) {
                    const tbody = $('#products-table .table tbody')[0];
                    console.log('tbody bulundu mu?', !!tbody);
                    console.log('tbody içinde kaç satır var?', tbody ? tbody.querySelectorAll('tr').length : 0);
                    
                    if (tbody) {
                        sortableInstance = window.Sortable.create(tbody, {
                            animation: 150,
                            handle: '.sort-handle',
                            draggable: 'tr',
                            ghostClass: 'sortable-ghost',
                            chosenClass: 'sortable-chosen',
                            dragClass: 'sortable-drag',
                            filter: '.no-drag',
                            preventOnFilter: false,
                            onStart: function(evt) {
                                console.log('>>> SÜRÜKLEME BAŞLADI <<<', evt.oldIndex);
                                $(evt.item).css('opacity', '0.5');
                            },
                            onEnd: function(evt) {
                                console.log('>>> SÜRÜKLEME BİTTİ <<<');
                                console.log('Eski pozisyon:', evt.oldIndex);
                                console.log('Yeni pozisyon:', evt.newIndex);
                                $(evt.item).css('opacity', '1');
                            },
                            onMove: function(evt, originalEvent) {
                                console.log('Hareket ediyor...');
                                return true;
                            },
                            onChoose: function(evt) {
                                console.log('Satır seçildi:', evt.oldIndex);
                            }
                        });
                        console.log('Sortable instance oluşturuldu:', !!sortableInstance);
                        console.log('Sortable options:', sortableInstance.options);
                    } else {
                        console.error('TBODY BULUNAMADI!');
                    }
                } else {
                    if (!window.Sortable) {
                        console.error('window.Sortable YOK!');
                    }
                    if (sortableInstance) {
                        console.log('sortableInstance zaten var');
                    }
                }
            }, 500);
        });

        $(document).on('click', '#btn-cancel-sort', function() {
            console.log('Vazgeç butonuna tıklandı');
            exitSortingMode();
            DataTable.reload('#products-table .table');
        });

        function exitSortingMode() {
            console.log('Exit sorting mode çağrıldı');
            sortingMode = false;
            $('#btn-sort-mode').text('Sırala').removeClass('btn-success').addClass('btn-default');
            $('#btn-cancel-sort').remove();
            
            // Sort mode kapat: kolon görünürlüğü/paging ayarlarını DataTables API ile geri al
            try {
                dt.api.column(1).visible(false);
                dt.api.page.len(20).draw(false);
                dt.api.columns.adjust();
            } catch (err) {
                console.error('Sort mode çıkış DT API hatası:', err);
            }
            
            $('.dataTables_length, .dataTables_filter, .dataTables_paginate').show();
            $('#filter-brand').prop('disabled', false);
            $('#filter-category').prop('disabled', false);
            
            if (sortableInstance) {
                sortableInstance.destroy();
                sortableInstance = null;
            }
        }

        $(document).on('change', '.product-status-switch', function () {
            const checkbox = $(this);
            const id = checkbox.data('id');
            const is_active = checkbox.is(':checked') ? 1 : 0;

            checkbox.prop('disabled', true);

            axios
                .patch(`${FleetCart.baseUrl}/admin/products/${id}/status`, { is_active })
                .then(() => {
                    // no-op; switch state already reflects latest value
                })
                .catch((error) => {
                    checkbox.prop('checked', !is_active);
                })
                .finally(() => {
                    checkbox.prop('disabled', false);
                });
        });

        // Pricing drawer
        const pricingDrawer = document.getElementById('pricing-drawer');
        const pricingDrawerBackdrop = document.getElementById('pricing-drawer-backdrop');
        const pricingDrawerContent = document.getElementById('pricing-drawer-content');
        const pricingDrawerTitle = document.getElementById('pricing-drawer-title');
        const pricingDrawerSave = document.getElementById('pricing-drawer-save');
        let currentPricingProductId = null;

        function openPricingDrawer() {
            pricingDrawer.classList.add('open');
            pricingDrawerBackdrop.classList.add('open');
        }

        function closePricingDrawer() {
            pricingDrawer.classList.remove('open');
            pricingDrawerBackdrop.classList.remove('open');
            currentPricingProductId = null;
            pricingDrawerContent.innerHTML = '';
            pricingDrawerTitle.textContent = '';
        }

        const pricingDrawerCloseButtons = document.querySelectorAll('.pricing-close-trigger');
        pricingDrawerCloseButtons.forEach(btn => btn.addEventListener('click', closePricingDrawer));
        pricingDrawerBackdrop.addEventListener('click', closePricingDrawer);

        function buildPricingItems(product) {
            const items = [];
            const currencyStep = 0.01;
            if (product.variants && product.variants.length > 0) {
                product.variants.forEach(v => {
                    const img = (v.media && v.media[0]) ? mediaUrl(v.media[0]) : ((product.media && product.media[0]) ? mediaUrl(product.media[0]) : '');
                    const priceVal = typeof v.price !== 'undefined' && v.price !== null ? Number(v.price) : '';
                    const specialVal = typeof v.special_price !== 'undefined' && v.special_price !== null ? Number(v.special_price) : '';
                    items.push(`
                        <div class="inv-item inv-price-item">
                            <div class="inv-info">
                                <div class="inv-media"><div class="thumbnail-holder"><img src="${img}" alt="" /></div></div>
                                <div class="inv-text">
                                    <div class="inv-name">${_.escape(v.name || '')}</div>
                                </div>
                            </div>
                            <div class="inv-actions">
                                <div class="inv-input-wrap" style="flex-direction: column; align-items: center; text-align:center;">
                                    <span style="font-size:11px;color:#6b7280;margin-bottom:3px; display:block;">Fiyat</span>
                                    <input type="number" step="${currencyStep}" min="0" inputmode="decimal" class="inv-input variant-price-input" data-id="${v.id}" value="${priceVal !== '' ? priceVal : ''}" placeholder="0.00" />
                                </div>
                                <div class="inv-input-wrap" style="flex-direction: column; align-items: center; text-align:center; margin-left:8px;">
                                    <span style="font-size:11px;color:#6b7280;margin-bottom:3px; display:block;">Özel fiyat</span>
                                    <input type="number" step="${currencyStep}" min="0" inputmode="decimal" class="inv-input variant-special-price-input" data-id="${v.id}" value="${specialVal !== '' ? specialVal : ''}" placeholder="0.00" />
                                </div>
                            </div>
                        </div>
                    `);
                });
            } else {
                const img = (product.media && product.media[0]) ? mediaUrl(product.media[0]) : '';
                const priceVal = typeof product.price !== 'undefined' && product.price !== null ? Number(product.price) : '';
                const specialVal = typeof product.special_price !== 'undefined' && product.special_price !== null ? Number(product.special_price) : '';
                items.push(`
                    <div class="inv-item inv-single inv-price-item">
                        <div class="inv-info">
                            <div class="inv-media"><div class="thumbnail-holder"><img src="${img}" alt="" /></div></div>
                            <div class="inv-text">
                                <div class="inv-name">${_.escape(product.name || '')}</div>
                            </div>
                        </div>
                        <div class="inv-actions">
                            <div class="inv-input-wrap" style="flex-direction: column; align-items: center; text-align:center;">
                                <span style="font-size:11px;color:#6b7280;margin-bottom:3px; display:block;">Fiyat</span>
                                <input type="number" step="${currencyStep}" min="0" inputmode="decimal" class="inv-input product-price-input" value="${priceVal !== '' ? priceVal : ''}" placeholder="0.00" />
                            </div>
                            <div class="inv-input-wrap" style="flex-direction: column; align-items: center; text-align:center; margin-left:8px;">
                                <span style="font-size:11px;color:#6b7280;margin-bottom:3px; display:block;">Özel fiyat</span>
                                <input type="number" step="${currencyStep}" min="0" inputmode="decimal" class="inv-input product-special-price-input" value="${specialVal !== '' ? specialVal : ''}" placeholder="0.00" />
                            </div>
                        </div>
                    </div>
                `);
            }
            return items.join('');
        }

        $(document).on('click', '.price-cell', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            if (!id) return;
            currentPricingProductId = id;
            axios.get(`${FleetCart.baseUrl}/admin/products/${id}/pricing`).then(({ data }) => {
                const product = data.product;
                pricingDrawerTitle.textContent = product.name;
                pricingDrawerContent.innerHTML = buildPricingItems(product);
                openPricingDrawer();
            });
        });

        pricingDrawerSave.addEventListener('click', function () {
            if (!currentPricingProductId) return;
            const payload = {};
            const variantPriceInputs = pricingDrawerContent.querySelectorAll('.variant-price-input');
            const variantSpecialInputs = pricingDrawerContent.querySelectorAll('.variant-special-price-input');

            if (variantPriceInputs.length > 0) {
                payload.variants = {};
                variantPriceInputs.forEach(inp => {
                    const id = inp.getAttribute('data-id');
                    const specialInp = pricingDrawerContent.querySelector('.variant-special-price-input[data-id="' + id + '"]');
                    const vPriceRaw = (inp.value || '').trim();
                    const vSpecialRaw = (specialInp?.value || '').trim();
                    const vPayload = {};
                    if (vPriceRaw !== '') {
                        let pv = parseFloat(vPriceRaw.replace(',', '.'));
                        if (!isFinite(pv) || pv < 0) pv = 0;
                        vPayload.price = pv;
                    }
                    if (vSpecialRaw !== '') {
                        let spv = parseFloat(vSpecialRaw.replace(',', '.'));
                        if (!isFinite(spv) || spv < 0) spv = 0;
                        vPayload.special_price = spv;
                    } else {
                        vPayload.special_price = '';
                    }
                    payload.variants[id] = vPayload;
                });
            } else {
                const priceInp = pricingDrawerContent.querySelector('.product-price-input');
                const specialInp = pricingDrawerContent.querySelector('.product-special-price-input');
                if (priceInp && priceInp.value.trim() !== '') {
                    let pv = parseFloat(priceInp.value.replace(',', '.'));
                    if (!isFinite(pv) || pv < 0) pv = 0;
                    payload.price = pv;
                }
                if (specialInp) {
                    const raw = specialInp.value.trim();
                    if (raw !== '') {
                        let spv = parseFloat(raw.replace(',', '.'));
                        if (!isFinite(spv) || spv < 0) spv = 0;
                        payload.special_price = spv;
                    } else {
                        payload.special_price = '';
                    }
                }
            }

            axios.patch(`${FleetCart.baseUrl}/admin/products/${currentPricingProductId}/pricing`, payload).then(() => {
                closePricingDrawer();
                DataTable.reload('#products-table .table');
            });
        });

        // Inline brand select
        $(document).on('click', '.brand-cell', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const cell = $(this);
            const productId = cell.data('id');
            if (!productId) return;

            // Avoid opening multiple selects
            if (cell.data('editing')) {
                return;
            }
            cell.data('editing', true);

            const currentBrandId = String(cell.data('brand-id') ?? '');

            const select = $('<select/>', {
                class: 'form-control input-sm',
            });

            select.append($('<option/>', { value: '', text: 'Select' }));
            Object.keys(allBrands).forEach((id) => {
                select.append(
                    $('<option/>', {
                        value: id,
                        text: allBrands[id],
                    })
                );
            });

            select.val(currentBrandId !== '' ? currentBrandId : '');

            const originalText = cell.text();
            cell.empty().append(select);
            select.focus();

            function cleanup(text, brandId) {
                cell.data('editing', false);
                cell.data('brand-id', brandId ?? '');
                cell.text(text || '');
            }

            select.on('change', function () {
                const newBrandId = $(this).val();
                axios
                    .patch(`${FleetCart.baseUrl}/admin/products/${productId}/brand`, {
                        brand_id: newBrandId,
                    })
                    .then(() => {
                        const label = newBrandId && allBrands[newBrandId] ? allBrands[newBrandId] : '';
                        cleanup(label, newBrandId);
                    })
                    .catch(() => {
                        cleanup(originalText, currentBrandId);
                    });
            });

            select.on('blur', function () {
                // On blur without change, restore original
                if (cell.data('editing')) {
                    const brandId = String(cell.data('brand-id') ?? currentBrandId ?? '');
                    const label = brandId && allBrands[brandId] ? allBrands[brandId] : originalText;
                    cleanup(label, brandId);
                }
            });
        });

        $(document).on('click', '.action-delete', function (e) {
            e.preventDefault();
            const id = $(this).data('id');

            const confirmationModal = $('#confirmation-modal');
            confirmationModal.modal('show');
            const body = confirmationModal.find('.modal-body');

            body.find('.fc-delete-redirect').remove();

            confirmationModal
                .modal('show')
                .find('form')
                .off('submit')
                .on('submit', (ev) => {
                    ev.preventDefault();
                    confirmationModal.modal('hide');

                    axios
                        .delete(`${FleetCart.baseUrl}/admin/products/${id}`)
                        .then(() => {
                            window.location.reload();
                        })
                        .catch(() => {
                            window.location.reload();
                        });
                });
        });

        $(document).on('click', '.action-view, .action-delete, .action-edit, .product-status-switch, .price-cell', function (e) {
            e.stopPropagation();
        });

        $(document).on('click', '.switch label', function (e) {
            e.stopPropagation();
        });
        // Inventory drawer
        const drawer = document.getElementById('inventory-drawer');
        const drawerBackdrop = document.getElementById('inventory-drawer-backdrop');
        const drawerContent = document.getElementById('inventory-drawer-content');
        const drawerTitle = document.getElementById('inventory-drawer-title');
        const drawerSave = document.getElementById('inventory-drawer-save');
        let currentProductId = null;
        let currentRowEl = null;
        function mediaUrl(obj) {
            if (!obj) return '';
            const p = obj.path || obj;
            if (!p) return '';
            if (/^https?:\/\//.test(p)) return p;
            if (p.startsWith('/')) return FleetCart.baseUrl + p;
            if (p.startsWith('storage/')) return FleetCart.baseUrl + '/' + p;
            return FleetCart.baseUrl + '/storage/' + p;
        }
        function buildItems(product) {
            const unitSuffix = product.sale_unit_id ? (product.unit_suffix || '') : '';
            const allowDecimal = !!(product.unit_decimal);
            const unitMin = Number(product.unit_min ?? 0);
            const unitStep = allowDecimal ? Number(product.unit_step || 0.01) : 1;
            const inputMode = allowDecimal ? 'decimal' : 'numeric';
            const items = [];

            if (product.variants && product.variants.length > 0) {
                product.variants.forEach(v => {
                    const img = (v.media && v.media[0]) ? mediaUrl(v.media[0]) : ((product.media && product.media[0]) ? mediaUrl(product.media[0]) : '');
                    const sku = v.sku || v.sku_code || '';
                    items.push(`
                        <div class="inv-item" style="flex-direction: column; align-items: stretch; gap: 15px;">
                            <div class="inv-info">
                                <div class="inv-media"><div class="thumbnail-holder"><img src="${img}" alt="" /></div></div>
                                <div class="inv-text">
                                    <div class="inv-name">${_.escape(v.name || '')}</div>
                                    ${sku ? `<div class="inv-sku">${_.escape(sku)}</div>` : ''}
                                </div>
                            </div>
                            <div class="inv-actions" style="display: grid; grid-template-columns: 1fr 1fr 80px; gap: 10px; width: 100%;">
                                <div>
                                    <label style="font-size:11px;color:#6b7280;display:block;margin-bottom:3px;font-weight:normal;">Stok Takibi</label>
                                    <select class="form-control input-sm variant-manage-stock-select" data-id="${v.id}" style="height:34px; border-radius:8px;">
                                        <option value="1" ${v.manage_stock ? 'selected' : ''}>Açık</option>
                                        <option value="0" ${!v.manage_stock ? 'selected' : ''}>Kapalı</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:11px;color:#6b7280;display:block;margin-bottom:3px;font-weight:normal;">Durum</label>
                                    <select class="form-control input-sm variant-in-stock-select" data-id="${v.id}" style="height:34px; border-radius:8px;">
                                        <option value="1" ${v.in_stock ? 'selected' : ''}>Stokta</option>
                                        <option value="0" ${!v.in_stock ? 'selected' : ''}>Yok</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:11px;color:#6b7280;display:block;margin-bottom:3px;font-weight:normal;">Miktar</label>
                                    <input type="number" step="${unitStep}" min="${unitMin}" inputmode="${inputMode}" class="inv-input variant-qty-input" data-id="${v.id}" data-decimal="${allowDecimal ? 1 : 0}" value="${Number(v.qty || 0)}" ${unitSuffix ? `data-suffix="${unitSuffix}"` : ''} style="height:34px;" ${!v.manage_stock ? 'disabled' : ''} />
                                </div>
                            </div>
                        </div>
                    `);
                });
            } else {
                const img = (product.media && product.media[0]) ? mediaUrl(product.media[0]) : '';
                const sku = product.sku || product.sku_code || '';
                items.push(`
                    <div class="inv-item inv-single" style="flex-direction: column; align-items: stretch; gap: 20px; padding: 20px;">
                        <div class="inv-info">
                            <div class="inv-media"><div class="thumbnail-holder"><img src="${img}" alt="" /></div></div>
                            <div class="inv-text">
                                <div class="inv-name" style="font-size:15px;">${_.escape(product.name || '')}</div>
                                ${sku ? `<div class="inv-sku">${_.escape(sku)}</div>` : ''}
                            </div>
                        </div>
                        <div class="inv-actions-vertical" style="display: flex; flex-direction: column; gap:15px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div>
                                    <label style="font-size:12px;color:#64748b;display:block;margin-bottom:5px;font-weight:600;">Stok Takibi</label>
                                    <select class="form-control product-manage-stock-select" style="height:40px; border-radius:10px;">
                                        <option value="1" ${product.manage_stock ? 'selected' : ''}>Açık (Miktar ile yönet)</option>
                                        <option value="0" ${!product.manage_stock ? 'selected' : ''}>Kapalı (Sürekli Stokta)</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:12px;color:#64748b;display:block;margin-bottom:5px;font-weight:600;">Stok Durumu</label>
                                    <select class="form-control product-in-stock-select" style="height:40px; border-radius:10px;">
                                        <option value="1" ${product.in_stock ? 'selected' : ''}>Stokta Var</option>
                                        <option value="0" ${!product.in_stock ? 'selected' : ''}>Stokta Yok</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:5px;font-weight:600;">Stok Miktarı</label>
                                <div class="inv-input-wrap" style="width: 100%;">
                                    <input type="number" step="${unitStep}" min="${unitMin}" inputmode="${inputMode}" class="inv-input product-qty-input" data-decimal="${allowDecimal ? 1 : 0}" value="${Number(product.qty || 0)}" ${unitSuffix ? `data-suffix="${unitSuffix}"` : ''} style="height:40px; font-size:15px;" ${!product.manage_stock ? 'disabled' : ''} />
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary inv-inline-save" style="height:44px; font-weight:700; border-radius:12px; margin-top:10px;">{{ trans('admin::admin.buttons.save') }}</button>
                        </div>
                    </div>
                `);
            }

            return items.join('');
        }

        function openDrawer() {
            drawer.classList.add('open');
            drawerBackdrop.classList.add('open');
        }

        function closeDrawer() {
            drawer.classList.remove('open');
            drawerBackdrop.classList.remove('open');
            currentProductId = null;
            drawerContent.innerHTML = '';
            drawerTitle.textContent = '';
        }

        const drawerCloseButtons = document.querySelectorAll('.drawer-close-trigger');
        drawerCloseButtons.forEach(btn => btn.addEventListener('click', closeDrawer));
        drawerBackdrop.addEventListener('click', closeDrawer);

        $(document).on('click', '#products-table .table tbody tr td.stock-cell', function () {
            const $anchor = $(this).find('.inventory-click');
            if (!$anchor.length) return; // Only open if clickable

            const dtApi = dt.api;
            currentRowEl = $(this).closest('tr');
            const row = dtApi.row(currentRowEl).data() || {};
            const targetId = $anchor.data('id') || row.id;
            if (!targetId) return;
            axios.get(`${FleetCart.baseUrl}/admin/products/${targetId}/inventory`).then(({ data }) => {
                const product = data.product;
                currentProductId = product.id;
                drawerTitle.textContent = product.name;
                drawerContent.innerHTML = buildItems(product);
                drawerSave.style.display = (product.variants && product.variants.length > 0) ? '' : 'none';
                openDrawer();
            });
        });

        $(document).on('click', '.inventory-click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            if (!id) return;
            currentRowEl = $(this).closest('tr');
            axios.get(`${FleetCart.baseUrl}/admin/products/${id}/inventory`).then(({ data }) => {
                const product = data.product;
                currentProductId = product.id;
                drawerTitle.textContent = product.name;
                drawerContent.innerHTML = buildItems(product);
                drawerSave.style.display = (product.variants && product.variants.length > 0) ? '' : 'none';
                openDrawer();
            });
        });

        drawerSave.addEventListener('click', function () {
            if (!currentProductId) return;
            const payload = {};
            const variantInputs = drawerContent.querySelectorAll('.variant-qty-input');
            if (variantInputs.length > 0) {
                payload.variants = {};
                variantInputs.forEach(inp => {
                    const id = inp.getAttribute('data-id');
                    const allow = String(inp.getAttribute('data-decimal') || '0') === '1';
                    const manageStock = drawerContent.querySelector(`.variant-manage-stock-select[data-id="${id}"]`).value;
                    const inStock = drawerContent.querySelector(`.variant-in-stock-select[data-id="${id}"]`).value;
                    let v = parseFloat((inp.value || '0').replace(',', '.')) || 0;
                    if (!allow) v = Math.trunc(v);
                    payload.variants[id] = { 
                        qty: v,
                        manage_stock: manageStock,
                        in_stock: inStock
                    };
                });
            } else {
                const inp = drawerContent.querySelector('.product-qty-input');
                const manageStock = drawerContent.querySelector('.product-manage-stock-select').value;
                const inStock = drawerContent.querySelector('.product-in-stock-select').value;
                const allow = String(inp?.getAttribute('data-decimal') || '0') === '1';
                let v = parseFloat((inp?.value || '0').replace(',', '.')) || 0;
                if (!allow) v = Math.trunc(v);
                payload.qty = v;
                payload.manage_stock = manageStock;
                payload.in_stock = inStock;
            }

            axios.patch(`${FleetCart.baseUrl}/admin/products/${currentProductId}/inventory`, payload).then(() => {
                DataTable.reload('#products-table .table');
                closeDrawer();
            });
        });

        $(document).on('input', '.inv-input', function () {
            const allow = String(this.getAttribute('data-decimal') || '0') === '1';
            if (!allow) {
                const val = String(this.value || '');
                if (val.includes('.')) {
                    this.value = String(Math.trunc(Number(val)) || '');
                }
            }
        });

        $(document).on('change', '.variant-manage-stock-select, .product-manage-stock-select', function() {
            const isEnabled = $(this).val() === '1';
            const item = $(this).closest('.inv-item');
            item.find('.inv-input').prop('disabled', !isEnabled);
        });

        $(document).on('click', '.inv-inline-save', function () {
            if (!currentProductId) return;
            const payload = {};
            const inp = drawerContent.querySelector('.product-qty-input');
            const manageStock = drawerContent.querySelector('.product-manage-stock-select').value;
            const inStock = drawerContent.querySelector('.product-in-stock-select').value;
            const allow = String(inp?.getAttribute('data-decimal') || '0') === '1';
            let v = parseFloat((inp?.value || '0').replace(',', '.')) || 0;
            if (!allow) v = Math.trunc(v);
            payload.qty = v;
            payload.manage_stock = manageStock;
            payload.in_stock = inStock;

            axios.patch(`${FleetCart.baseUrl}/admin/products/${currentProductId}/inventory`, payload).then(() => {
                DataTable.reload('#products-table .table');
                closeDrawer();
            });
        });

        function getFilterParams() {
            const params = new URLSearchParams();
            const brandId = $('#filter-brand').val();
            if (brandId) params.append('brand_id', brandId);
            const categoryId = $('#filter-category').val();
            if (categoryId) params.append('category_id', categoryId);
            const sv = $("#products-table .dataTables_filter input[type='search']").val();
            if (sv) params.append('search', sv);
            return params.toString();
        }

        $('#btn-export-products-excel').on('click', function (e) {
            e.preventDefault();
            const qs = getFilterParams();
            window.location.href = `${FleetCart.baseUrl}/admin/products/excel/export${qs ? '?' + qs : ''}`;
        });

        $('#btn-export-trendyol').on('click', function (e) {
            e.preventDefault();
            $('#trendyol-template-modal').modal('show');
        });

        window.submitTrendyolExport = function() {
            const template = $('#trendyol_template').val();
            const qs = getFilterParams();
            const baseParams = qs ? `?${qs}&template=${template}` : `?template=${template}`;
            window.location.href = `${FleetCart.baseUrl}/admin/products/excel/export/trendyol${baseParams}`;
            $('#trendyol-template-modal').modal('hide');
        };

        $('#btn-export-hepsiburada').on('click', function (e) {
            e.preventDefault();
            const qs = getFilterParams();
            window.location.href = `${FleetCart.baseUrl}/admin/products/excel/export/hepsiburada${qs ? '?' + qs : ''}`;
        });

        // CSV Export (filter aware)
        $('#btn-export-products-csv').on('click', function (e) {
            e.preventDefault();
            const qs = getFilterParams();
            window.location.href = `${FleetCart.baseUrl}/admin/products/csv/export${qs ? '?' + qs : ''}`;
        });

        // Variant CSV Export (filter aware, same filters as products)
        $('#btn-export-variants-csv').on('click', function (e) {
            e.preventDefault();

            const params = new URLSearchParams();

            const brandId = $('#filter-brand').val();
            if (brandId) params.append('brand_id', brandId);

            const categoryId = $('#filter-category').val();
            if (categoryId) params.append('category_id', categoryId);

            const searchInput = $("#products-table .dataTables_filter input[type='search']");
            const searchVal = searchInput.length ? searchInput.val() : '';
            if (searchVal) params.append('search', searchVal);

            const url = `${FleetCart.baseUrl}/admin/products/variants/csv/export` + (params.toString() ? `?${params.toString()}` : '');
            window.location.href = url;
        });

        // CSV Import / Bulk Update Wizard
        let csvTempId = null;
        let csvMode = 'create';
        let csvIdentifier = 'id';
        let csvMapping = {};

        const $csvModal = $('#csv-import-modal');
        const $csvStep1 = $('#csv-step-1');
        const $csvStep2 = $('#csv-step-2');
        const $csvStep3 = $('#csv-step-3');

        function showCsvStep(step) {
            $csvStep1.toggleClass('hidden', step !== 1);
            $csvStep2.toggleClass('hidden', step !== 2);
            $csvStep3.toggleClass('hidden', step !== 3);
        }

        window.csvOpenImportModal = function () {
            csvTempId = null;
            csvMapping = {};
            $('#csv-file-input').val('');
            $('#csv-mode-select').val('create');
            $('#csv-identifier-select').val('id');
            $('#csv-delimiter-select').val('comma');
            $('#csv-mapping-table tbody').empty();
            $('#csv-preview-summary').empty();
            $('#csv-preview-rows').empty();
            showCsvStep(1);
            $csvModal.modal('show');
        };

        window.csvStep1Next = function () {
            const fileInput = document.getElementById('csv-file-input');
            if (!fileInput.files.length) {
                alert('Lütfen bir CSV dosyası seçin.');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            csvMode = $('#csv-mode-select').val() || 'create';
            const delimiter = $('#csv-delimiter-select').val() || 'comma';
            formData.append('mode', csvMode);
            formData.append('delimiter', delimiter);

            axios.post(`${FleetCart.baseUrl}/admin/products/csv/import/upload`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            }).then(({ data }) => {
                if (!data.success) return;
                csvTempId = data.temp_id;
                const headers = data.headers || [];
                const $tbody = $('#csv-mapping-table tbody');
                $tbody.empty();

                const availableFields = [
                    'id','sku','slug','name','description','short_description',
                    'price','special_price','special_price_type','special_price_start','special_price_end',
                    'selling_price','manage_stock','qty','in_stock','is_virtual','is_active',
                    'brand_id','tax_class_id','sale_unit_id','primary_category_id','google_product_category_id','google_product_category_path',
                    'list_variants_separately','category_ids','tag_ids','images',
                ];

                headers.forEach((h) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${_.escape(h)}</td>
                        <td>
                            <select class="form-control input-sm csv-field-select" data-csv-column="${_.escape(h)}">
                                <option value="">-- Alan seçin --</option>
                                ${availableFields.map(f => `<option value="${f}">${f}</option>`).join('')}
                            </select>
                        </td>
                    `;
                    $tbody.append(row);
                });

                showCsvStep(2);
            }).catch((error) => {
                if (error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                    let messages = [];
                    Object.values(error.response.data.errors).forEach((arr) => {
                        if (Array.isArray(arr)) {
                            messages = messages.concat(arr);
                        }
                    });
                    alert('Dosya yükleme doğrulama hatası:\n' + messages.join('\n'));
                } else {
                    alert('Dosya yükleme sırasında bir hata oluştu.');
                }
            });
        };

        window.csvStep2Prev = function () {
            showCsvStep(1);
        };

        $(document).on('click', '#csv-auto-map', function (e) {
            e.preventDefault();
            function normalizeKey(str) {
                return String(str || '')
                    .toLowerCase()
                    .replace(/[\s_\-]+/g, '')
                    .replace(/[ıİ]/g, 'i')
                    .replace(/[şŞ]/g, 's')
                    .replace(/[ğĞ]/g, 'g')
                    .replace(/[üÜ]/g, 'u')
                    .replace(/[öÖ]/g, 'o')
                    .replace(/[çÇ]/g, 'c');
            }

            function fieldAliases(field) {
                const base = normalizeKey(field);
                const aliases = [base];

                if (base === 'id' || base === 'productid' || base === 'urunid') {
                    aliases.push('id', 'productid');
                }
                if (base === 'sku' || base === 'productsku' || base === 'urunkodu') {
                    aliases.push('sku', 'productsku');
                }
                if (base === 'name' || base === 'productname' || base === 'urunadi') {
                    aliases.push('name', 'productname');
                }
                if (base === 'price' || base === 'fiyat' || base === 'sellingprice') {
                    aliases.push('price', 'sellingprice');
                }
                if (base === 'qty' || base === 'stok' || base === 'quantity' || base === 'stock') {
                    aliases.push('qty');
                }
                if (base === 'attributes' || base === 'ozellikler' || base === 'varyantlar') {
                    aliases.push('attributes');
                }

                return aliases;
            }

            $('#csv-mapping-table tbody .csv-field-select').each(function () {
                const $sel = $(this);
                const colRaw = String($sel.data('csv-column') || '');
                const colNorm = normalizeKey(colRaw);
                const options = $sel.get(0).options;

                let matched = false;

                for (let i = 0; i < options.length && !matched; i++) {
                    const opt = options[i];
                    if (!opt.value) continue;
                    const optNorm = normalizeKey(opt.value);
                    const aliases = fieldAliases(opt.value);
                    if (optNorm === colNorm || aliases.includes(colNorm)) {
                        $sel.val(opt.value);
                        matched = true;
                    }
                }
            });
        });

        window.csvStep2Next = function () {
            console.log('[CSV] Step 2 Next clicked');
            if (!csvTempId) {
                alert('Geçici dosya bulunamadı. Lütfen baştan deneyin.');
                return;
            }

            csvMapping = {};
            $('#csv-mapping-table tbody .csv-field-select').each(function () {
                const field = $(this).val();
                const col = $(this).data('csv-column');
                if (field) {
                    csvMapping[col] = field;
                }
            });

            if (!Object.keys(csvMapping).length) {
                console.log('[CSV] No mapping set, blocking preview');
                alert('Devam etmeden önce en az bir alan eşlemeniz gerekiyor.');
                return;
            }

            csvMode = $('#csv-mode-select').val() || 'create';
            csvIdentifier = $('#csv-identifier-select').val() || 'id';

            console.log('[CSV] Sending preview', {
                temp_id: csvTempId,
                mode: csvMode,
                identifier: csvIdentifier,
                mapping: csvMapping,
            });

            alert('[CSV DEBUG] Önizleme isteği gönderiliyor.\n' +
                'temp_id: ' + csvTempId + '\n' +
                'mode: ' + csvMode + '\n' +
                'identifier: ' + csvIdentifier + '\n' +
                'mapping keys: ' + Object.keys(csvMapping).join(', '));

            axios.post(`${FleetCart.baseUrl}/admin/products/csv/import/preview`, {
                temp_id: csvTempId,
                mode: csvMode,
                mapping: csvMapping,
                identifier: csvIdentifier,
            }).then(({ data }) => {
                if (!data.success) return;
                const preview = data.preview || {};
                const total = preview.total || 0;
                const valid = preview.valid || 0;
                const invalid = preview.invalid || 0;
                $('#csv-preview-summary').text(`Toplam ${total} satır, ${valid} geçerli, ${invalid} hatalı.`);

                const $tbody = $('#csv-preview-rows');
                $tbody.empty();
                (preview.rows || []).forEach((r) => {
                    const errors = (r.errors || []).join(' ');
                    const actionLabel = r.action === 'create' ? 'Yeni ürün oluşturulacak' : (r.action === 'update' ? 'Ürün güncellenecek' : 'Atlanacak');
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${r.index}</td>
                        <td>${_.escape(actionLabel)}</td>
                        <td>${_.escape(errors)}</td>
                    `;
                    $tbody.append(tr);
                });

                showCsvStep(3);
            }).catch((error) => {
                if (error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                    let messages = [];
                    Object.values(error.response.data.errors).forEach((arr) => {
                        if (Array.isArray(arr)) {
                            messages = messages.concat(arr);
                        }
                    });
                    alert('Önizleme doğrulama hatası:\n' + messages.join('\n'));
                } else {
                    alert('Önizleme sırasında bir hata oluştu.');
                }
            });
        };

        window.csvStep3Prev = function () {
            showCsvStep(2);
        };

        window.csvProcessStart = function () {
            if (!csvTempId) {
                alert('Geçici dosya bulunamadı.');
                return;
            }

            axios.post(`${FleetCart.baseUrl}/admin/products/csv/import/process`, {
                temp_id: csvTempId,
                mode: csvMode,
                mapping: csvMapping,
                identifier: csvIdentifier,
            }).then(() => {
                alert('İşlem kuyruğa alındı. Kuyruktaki işler tamamlandığında sonuçlar sistemde görüntülenecektir.');
                $csvModal.modal('hide');
            }).catch((error) => {
                if (error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                    let messages = [];
                    Object.values(error.response.data.errors).forEach((arr) => {
                        if (Array.isArray(arr)) {
                            messages = messages.concat(arr);
                        }
                    });
                    alert('İşlem başlatılamadı (doğrulama hatası):\n' + messages.join('\n'));
                } else {
                    alert('İşlem başlatılırken bir hata oluştu.');
                }
            });
        };

        // Variant CSV Import / Bulk Update Wizard
        let variantCsvTempId = null;
        let variantCsvMode = 'create';
        let variantCsvIdentifier = 'id';
        let variantCsvMapping = {};

        const $variantModal = $('#variant-csv-import-modal');
        const $variantStep1 = $('#variant-csv-step-1');
        const $variantStep2 = $('#variant-csv-step-2');
        const $variantStep3 = $('#variant-csv-step-3');

        function showVariantStep(step) {
            $variantStep1.toggleClass('hidden', step !== 1);
            $variantStep2.toggleClass('hidden', step !== 2);
            $variantStep3.toggleClass('hidden', step !== 3);
        }

        $(document).on('click', '#btn-open-variant-csv-import-modal', function (e) {
            e.preventDefault();
            variantCsvTempId = null;
            variantCsvMapping = {};
            $('#variant-csv-file-input').val('');
            $('#variant-csv-mode-create').prop('checked', true);
            $('#variant-csv-mode-update').prop('checked', false);
            $('#variant-csv-identifier-id').prop('checked', true);
            $('#variant-csv-identifier-sku').prop('checked', false);
            $('#variant-csv-delimiter-comma').prop('checked', true);
            $('#variant-csv-delimiter-semicolon').prop('checked', false);
            $('#variant-csv-mapping-table tbody').empty();
            $('#variant-csv-preview-summary').empty();
            $('#variant-csv-preview-rows').empty();
            showVariantStep(1);
            $variantModal.modal('show');
        });

        $(document).on('click', '#variant-csv-step-1-next', function (e) {
            e.preventDefault();
            const fileInput = document.getElementById('variant-csv-file-input');
            if (!fileInput.files.length) {
                alert('Lütfen bir CSV dosyası seçin.');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            variantCsvMode = $('#variant-csv-mode-update').is(':checked') ? 'update' : 'create';
            const delimiter = $('#variant-csv-delimiter-semicolon').is(':checked') ? 'semicolon' : 'comma';
            formData.append('mode', variantCsvMode);
            formData.append('delimiter', delimiter);

            axios.post(`${FleetCart.baseUrl}/admin/products/variants/csv/import/upload`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            }).then(({ data }) => {
                if (!data.success) return;
                variantCsvTempId = data.temp_id;
                const headers = data.headers || [];
                const $tbody = $('#variant-csv-mapping-table tbody');
                $tbody.empty();

                const availableVariantFields = [
                    'id', 'product_id', 'product_sku', 'sku', 'uid', 'uids', 'name',
                    'price', 'special_price', 'special_price_type', 'special_price_start', 'special_price_end',
                    'selling_price', 'manage_stock', 'qty', 'in_stock', 'is_default', 'is_active', 'position',
                    'attributes',
                ];

                headers.forEach((h) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${_.escape(h)}</td>
                        <td>
                            <select class="form-control input-sm variant-csv-field-select" data-csv-column="${_.escape(h)}">
                                <option value="">-- Alan seçin --</option>
                                ${availableVariantFields.map(f => `<option value="${f}">${f}</option>`).join('')}
                            </select>
                        </td>
                    `;
                    $tbody.append(row);
                });

                showVariantStep(2);
            }).catch((error) => {
                if (error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                    let messages = [];
                    Object.values(error.response.data.errors).forEach((arr) => {
                        if (Array.isArray(arr)) {
                            messages = messages.concat(arr);
                        }
                    });
                    alert('Varyant dosya yükleme doğrulama hatası:\n' + messages.join('\n'));
                } else {
                    alert('Dosya yükleme sırasında bir hata oluştu.');
                }
            });
        });

        $(document).on('click', '#variant-csv-step-2-prev', function (e) {
            e.preventDefault();
            showVariantStep(1);
        });

        $(document).on('click', '#variant-csv-auto-map', function (e) {
            e.preventDefault();
            function normalizeVariantKey(str) {
                return String(str || '')
                    .toLowerCase()
                    .replace(/[\s_\-]+/g, '')
                    .replace(/[ıİ]/g, 'i')
                    .replace(/[şŞ]/g, 's')
                    .replace(/[ğĞ]/g, 'g')
                    .replace(/[üÜ]/g, 'u')
                    .replace(/[öÖ]/g, 'o')
                    .replace(/[çÇ]/g, 'c');
            }

            function variantFieldAliases(field) {
                const base = normalizeVariantKey(field);
                const aliases = [base];

                if (base === 'id' || base === 'variantid' || base === 'varyantid') {
                    aliases.push('id', 'variantid');
                }
                if (base === 'productid' || base === 'urunid') {
                    aliases.push('productid');
                }
                if (base === 'productsku' || base === 'urunkodu') {
                    aliases.push('productsku');
                }
                if (base === 'sku' || base === 'variantsku' || base === 'varyantsku' || base === 'varyantkodu') {
                    aliases.push('sku', 'variantsku');
                }
                if (base === 'name' || base === 'variantname' || base === 'varyantadi') {
                    aliases.push('name', 'variantname');
                }
                if (base === 'price' || base === 'fiyat' || base === 'sellingprice') {
                    aliases.push('price', 'sellingprice');
                }
                if (base === 'qty' || base === 'stok' || base === 'quantity' || base === 'stock') {
                    aliases.push('qty');
                }
                if (base === 'attributes' || base === 'ozellikler' || base === 'varyantlar') {
                    aliases.push('attributes');
                }

                return aliases;
            }

            $('#variant-csv-mapping-table tbody .variant-csv-field-select').each(function () {
                const $sel = $(this);
                const colRaw = String($sel.data('csv-column') || '');
                const colNorm = normalizeVariantKey(colRaw);
                const options = $sel.get(0).options;

                let matched = false;

                for (let i = 0; i < options.length && !matched; i++) {
                    const opt = options[i];
                    if (!opt.value) continue;
                    const optNorm = normalizeVariantKey(opt.value);
                    const aliases = variantFieldAliases(opt.value);
                    if (optNorm === colNorm || aliases.includes(colNorm)) {
                        $sel.val(opt.value);
                        matched = true;
                    }
                }
            });
        });

        $(document).on('click', '#variant-csv-step-2-next', function (e) {
            e.preventDefault();
            if (!variantCsvTempId) {
                alert('Geçici dosya bulunamadı. Lütfen baştan deneyin.');
                return;
            }

            variantCsvMapping = {};
            $('#variant-csv-mapping-table tbody .variant-csv-field-select').each(function () {
                const field = $(this).val();
                const col = $(this).data('csv-column');
                if (field) {
                    variantCsvMapping[col] = field;
                }
            });

            if (!Object.keys(variantCsvMapping).length) {
                alert('Devam etmeden önce en az bir varyant alanını eşlemeniz gerekiyor.');
                return;
            }

            variantCsvMode = $('#variant-csv-mode-update').is(':checked') ? 'update' : 'create';
            variantCsvIdentifier = $('#variant-csv-identifier-sku').is(':checked') ? 'sku' : 'id';

            axios.post(`${FleetCart.baseUrl}/admin/products/variants/csv/import/preview`, {
                temp_id: variantCsvTempId,
                mode: variantCsvMode,
                mapping: variantCsvMapping,
                identifier: variantCsvIdentifier,
            }).then(({ data }) => {
                if (!data.success) return;
                const preview = data.preview || {};
                const total = preview.total || 0;
                const valid = preview.valid || 0;
                const invalid = preview.invalid || 0;
                $('#variant-csv-preview-summary').text(`Toplam ${total} satır, ${valid} geçerli, ${invalid} hatalı.`);

                const $tbody = $('#variant-csv-preview-rows');
                $tbody.empty();
                (preview.rows || []).forEach((r) => {
                    const errors = (r.errors || []).join(' ');
                    let actionLabel = 'Atlanacak';
                    if (r.action === 'create') actionLabel = 'Yeni varyant oluşturulacak';
                    else if (r.action === 'update') actionLabel = 'Varyant güncellenecek';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${r.index}</td>
                        <td>${_.escape(actionLabel)}</td>
                        <td>${_.escape(errors)}</td>
                    `;
                    $tbody.append(tr);
                });

                showVariantStep(3);
            }).catch((error) => {
                if (error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                    let messages = [];
                    Object.values(error.response.data.errors).forEach((arr) => {
                        if (Array.isArray(arr)) {
                            messages = messages.concat(arr);
                        }
                    });
                    alert('Varyant önizleme doğrulama hatası:\n' + messages.join('\n'));
                } else {
                    alert('Önizleme sırasında bir hata oluştu.');
                }
            });
        });

        $(document).on('click', '#variant-csv-step-3-prev', function (e) {
            e.preventDefault();
            showVariantStep(2);
        });

        $(document).on('click', '#variant-csv-process-start', function (e) {
            e.preventDefault();
            if (!variantCsvTempId) {
                alert('Geçici dosya bulunamadı.');
                return;
            }

            axios.post(`${FleetCart.baseUrl}/admin/products/variants/csv/import/process`, {
                temp_id: variantCsvTempId,
                mode: variantCsvMode,
                mapping: variantCsvMapping,
                identifier: variantCsvIdentifier,
            }).then(() => {
                alert('Varyant işlemi kuyruğa alındı. Kuyruktaki işler tamamlandığında sonuçlar sistemde görüntülenecektir.');
                $variantModal.modal('hide');
            }).catch((error) => {
                if (error.response && error.response.status === 422 && error.response.data && error.response.data.errors) {
                    let messages = [];
                    Object.values(error.response.data.errors).forEach((arr) => {
                        if (Array.isArray(arr)) {
                            messages = messages.concat(arr);
                        }
                    });
                    alert('Varyant işlemi başlatılamadı (doğrulama hatası):\n' + messages.join('\n'));
                } else {
                    alert('Varyant işlemi başlatılırken bir hata oluştu.');
                }
            });
        });
    </script>
    <script>
        window.excelOpenImportModal = function() {
            $('#excel-import-modal').modal('show');
        }
        window.excelSubmitImport = function() {
            const file = $('#excel-file-input')[0].files[0];
            if (!file) {
                alert('Lütfen bir dosya seçin.');
                return;
            }
            const mode = $('#excel-mode-select').val();
            const identifier = $('#excel-identifier-select').val();
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('mode', mode);
            formData.append('identifier', identifier);

            $('#excel-import-btn').prop('disabled', true).text('İşleniyor...');

            axios.post(`${FleetCart.baseUrl}/admin/products/excel/import`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(({data}) => {
                // If the controller returns a view (as it does), we can't easily show it in modal 
                // unless we change controller to return JSON or use a target div.
                // Simple way: replace body with response if it's a full page or redirect.
                document.open();
                document.write(data);
                document.close();
            }).catch(err => {
                alert('Bir hata oluştu.');
                $('#excel-import-btn').prop('disabled', false).text('İçe Aktar');
            });
        }
    </script>
    @endpush

    <div class="modal fade" id="excel-import-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Excel ile Ürün Yükle / Güncelle</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Dosya Seç (.xlsx, .xls)</label>
                        <input type="file" id="excel-file-input" class="form-control" accept=".xlsx, .xls" />
                    </div>
                    <div class="form-group">
                        <label>İşlem Modu</label>
                        <select id="excel-mode-select" class="form-control">
                            <option value="create">Yeni Ürünleri Ekle</option>
                            <option value="update">Mevcut Ürünleri Güncelle</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ürün Tanımlayıcı (Güncelleme için)</label>
                        <select id="excel-identifier-select" class="form-control">
                            <option value="sku">SKU (Stok Kodu)</option>
                            <option value="id">ID (Sistem No)</option>
                        </select>
                    </div>
                    <p class="help-block">
                        <strong>Not:</strong> Başlıklar otomatik olarak eşleştirilecektir (SKU, Ürün Adı, Fiyat vb.).
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" id="excel-import-btn" onclick="excelSubmitImport()">İçe Aktar</button>
                </div>
            </div>
        </div>
    </div>
    @push('styles')
    <style>
        .table-responsive {
            border: none !important;
            padding: 10px;
        }

        #products-table .table {
            border-collapse: separate;
            border-spacing: 0 10px;
            background: transparent;
        }

        #products-table .table thead th {
            border: none;
            background: transparent;
            color: #909399;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 10px 15px;
        }

        #products-table .table tbody tr {
            background: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }

        #products-table .table tbody tr:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            background: #fff !important;
        }

        #products-table .table tbody td {
            border: none;
            padding: 15px;
            vertical-align: middle;
        }

        #products-table .table tbody td:first-child { border-radius: 12px 0 0 12px; }
        #products-table .table tbody td:last-child { border-radius: 0 12px 12px 0; }

        .image-cell img {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            background: #f5f7fa;
            border: 1px solid #efefef;
        }

        .product-name-link {
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
            transition: color 0.2s;
        }

        .product-name-link:hover {
            color: #3b82f6;
            text-decoration: none;
        }

        .price-text {
            font-weight: 700;
            color: #1a1a1a;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .price-text:hover { background: #f0f7ff; color: #3b82f6; }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background: #f0fdf4;
            color: #166534;
            cursor: pointer;
        }

        .stock-badge.out-of-stock {
            background: #fef2f2;
            color: #991b1b;
        }

        .actions-grid {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-action-round {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #6b7280;
            transition: all 0.2s;
        }

        .btn-action-round:hover {
            background: #f9fafb;
            color: #111827;
            border-color: #d1d5db;
        }

        .btn-action-round.delete:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        /* Status Switch styling */
        .switch {
            position: relative;
            display: inline-block;
            width: 36px;
            height: 20px;
        }

        .switch input { opacity: 0; width: 0; height: 0; }

        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #e5e7eb;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 14px;
            width: 14px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider { background-color: #10b981; }
        input:checked + .slider:before { transform: translateX(16px); }

        #inventory-drawer-backdrop,
        #pricing-drawer-backdrop { 
            position: fixed; 
            inset: 0; 
            background: rgba(255, 255, 255, 0); 
            backdrop-filter: none; 
            opacity: 0; 
            pointer-events: none; 
            transition: opacity .2s; 
            z-index: 1040; 
        }
        #inventory-drawer-backdrop.open,
        #pricing-drawer-backdrop.open { opacity: 1; pointer-events: auto; }
        
        #inventory-drawer,
        #pricing-drawer { 
            position: fixed; 
            top: 20px; 
            right: -500px; 
            width: 480px; 
            height: calc(100% - 40px); 
            background: #fff; 
            box-shadow: -10px 0 50px rgba(0,0,0,0.1); 
            z-index: 1050; 
            transition: right .4s cubic-bezier(0.4, 0, 0.2, 1); 
            border-radius: 20px 0 0 20px; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden;
            border: 1px solid #eef2f7;
            border-right: none;
        }
        #inventory-drawer.open,
        #pricing-drawer.open { right: 0; }

        .drawer-header {
            padding: 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        #inventory-drawer-title, #pricing-drawer-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 20px;
        }

        .drawer-header .close-btn {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .drawer-header .close-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: #fcfdfe;
        }

        .drawer-footer {
            padding: 20px 24px;
            border-top: 1px solid #f1f5f9;
            background: #fff;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .inv-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #fff;
            margin-bottom: 12px;
            transition: all 0.2s ease;
        }

        .inv-item:hover {
            border-color: #0ea5e9;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }

        .inv-info {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
        }

        .inv-media img {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        .inv-text {
            flex: 1;
        }

        .inv-name {
            font-weight: 600;
            color: #334155;
            font-size: 13px;
            line-height: 1.4;
        }

        .inv-sku {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
            font-family: inherit;
        }

        .inv-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .inv-input-wrap {
            width: 80px;
        }

        .inv-input {
            width: 100%;
            height: 38px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #fff;
            padding: 0 10px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            color: #1e293b;
            transition: all 0.2s;
        }

        .inv-input:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        .btn-save-inline {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
        }

        .inv-media .thumbnail-holder {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
            background: #f8fafc;
            margin: 0;
        }

        .drawer-body::-webkit-scrollbar { width: 5px; }
        .drawer-body::-webkit-scrollbar-track { background: transparent; }
        .drawer-body::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
    @endpush

    @push('notifications')
    <div id="inventory-drawer-backdrop"></div>
    <div id="inventory-drawer">
        <div class="drawer-header">
            <h4 id="inventory-drawer-title"></h4>
            <button class="close-btn drawer-close-trigger">×</button>
        </div>
        <div class="drawer-body" id="inventory-drawer-content"></div>
        <div class="drawer-footer">
            <button class="btn btn-default drawer-close-trigger">{{ trans('admin::admin.buttons.cancel') }}</button>
            <button class="btn btn-primary" id="inventory-drawer-save">{{ trans('admin::admin.buttons.save') }}</button>
        </div>
    </div>
    <div id="pricing-drawer-backdrop"></div>
    <div id="pricing-drawer">
        <div class="drawer-header">
            <h4 id="pricing-drawer-title"></h4>
            <button class="close-btn pricing-close-trigger">×</button>
        </div>
        <div class="drawer-body" id="pricing-drawer-content"></div>
        <div class="drawer-footer">
            <button class="btn btn-default pricing-close-trigger">{{ trans('admin::admin.buttons.cancel') }}</button>
            <button class="btn btn-primary" id="pricing-drawer-save">{{ trans('admin::admin.buttons.save') }}</button>
        </div>
    </div>

    {{-- CSV Import Wizard Modal --}}
    <div class="modal fade" id="csv-import-modal" tabindex="-1" role="dialog" aria-labelledby="csvImportModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="csvImportModalLabel">CSV ile Ürün Yükleme / Güncelleme</h4>
                </div>
                <div class="modal-body">
                    <div id="csv-step-1">
                        <h4>1. Adım: Dosya Yükleme</h4>
                        <div class="form-group">
                            <label for="csv-file-input">CSV Dosyası</label>
                            <input type="file" id="csv-file-input" class="form-control" accept=".csv">
                        </div>
                        <div class="form-group">
                            <label for="csv-mode-select">İşlem tipi</label>
                            <select id="csv-mode-select" class="form-control">
                                <option value="create" selected>Yeni ürünler ekle</option>
                                <option value="update">Mevcut ürünleri güncelle (ID veya SKU ile)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="csv-delimiter-select">Ayraç</label>
                            <select id="csv-delimiter-select" class="form-control">
                                <option value="comma" selected>, (virgül)</option>
                                <option value="semicolon">; (noktalı virgül)</option>
                            </select>
                        </div>
                    </div>

                    <div id="csv-step-2" class="hidden">
                        <h4>2. Adım: Kolon Eşleme</h4>
                        <p>CSV kolonlarını FleetCart ürün alanları ile eşleyin.</p>
                        <button type="button" class="btn btn-default btn-xs" id="csv-auto-map">Otomatik Eşle</button>
                        <table class="table table-bordered" id="csv-mapping-table">
                            <thead>
                                <tr>
                                    <th>CSV Kolonu</th>
                                    <th>Ürün Alanı</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div class="form-group">
                            <label for="csv-identifier-select">Güncelleme için eşleştirme alanı</label>
                            <select id="csv-identifier-select" class="form-control">
                                <option value="id" selected>ID</option>
                                <option value="sku">SKU</option>
                            </select>
                        </div>
                    </div>

                    <div id="csv-step-3" class="hidden">
                        <h4>3. Adım: Önizleme ve İşleme</h4>
                        <p id="csv-preview-summary"></p>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Satır</th>
                                    <th>İşlem</th>
                                    <th>Hatalar</th>
                                </tr>
                            </thead>
                            <tbody id="csv-preview-rows"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
                    <button type="button" class="btn btn-default" id="csv-step-1-next" onclick="csvStep1Next()">Devam</button>
                    <button type="button" class="btn btn-default hidden" id="csv-step-2-prev" onclick="csvStep2Prev()">Geri</button>
                    <button type="button" class="btn btn-primary hidden" id="csv-step-2-next" onclick="csvStep2Next()">Devam</button>
                    <button type="button" class="btn btn-default hidden" id="csv-step-3-prev" onclick="csvStep3Prev()">Geri</button>
                    <button type="button" class="btn btn-primary hidden" id="csv-process-start" onclick="csvProcessStart()">İşlemi Başlat</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Variant CSV Import Wizard Modal --}}
    <div class="modal fade" id="variant-csv-import-modal" tabindex="-1" role="dialog" aria-labelledby="variantCsvImportModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="variantCsvImportModalLabel">CSV ile Varyant Yükleme / Güncelleme</h4>
                </div>
                <div class="modal-body">
                    <div id="variant-csv-step-1">
                        <h4>1. Adım: Varyant CSV Dosyası Yükleme</h4>
                        <div class="form-group">
                            <label for="variant-csv-file-input">CSV Dosyası</label>
                            <input type="file" id="variant-csv-file-input" class="form-control" accept=".csv">
                        </div>
                        <div class="form-group">
                            <label>İşlem tipi</label>
                            <div class="radio">
                                <label><input type="radio" name="variant-csv-mode" id="variant-csv-mode-create" value="create" checked> Yeni varyantlar ekle</label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="variant-csv-mode" id="variant-csv-mode-update" value="update"> Mevcut varyantları güncelle</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Ayraç</label>
                            <div class="radio">
                                <label><input type="radio" name="variant-csv-delimiter" id="variant-csv-delimiter-comma" value="comma" checked> , (virgül)</label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="variant-csv-delimiter" id="variant-csv-delimiter-semicolon" value="semicolon"> ; (noktalı virgül)</label>
                            </div>
                        </div>
                    </div>

                    <div id="variant-csv-step-2" class="hidden">
                        <h4>2. Adım: Varyant Kolon Eşleme</h4>
                        <p>CSV kolonlarını varyant alanları ile eşleyin.</p>
                        <button type="button" class="btn btn-default btn-xs" id="variant-csv-auto-map">Otomatik Eşle</button>
                        <table class="table table-bordered" id="variant-csv-mapping-table">
                            <thead>
                                <tr>
                                    <th>CSV Kolonu</th>
                                    <th>Varyant Alanı</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <div class="form-group">
                            <label>Güncelleme için eşleştirme alanı</label>
                            <div class="radio">
                                <label><input type="radio" name="variant-csv-identifier" id="variant-csv-identifier-id" value="id" checked> Varyant ID</label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="variant-csv-identifier" id="variant-csv-identifier-sku" value="sku"> Varyant SKU</label>
                            </div>
                        </div>
                    </div>

                    <div id="variant-csv-step-3" class="hidden">
                        <h4>3. Adım: Önizleme ve Doğrulama</h4>
                        <p id="variant-csv-preview-summary"></p>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Satır</th>
                                    <th>İşlem</th>
                                    <th>Hatalar</th>
                                </tr>
                            </thead>
                            <tbody id="variant-csv-preview-rows"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
                    <button type="button" class="btn btn-default" id="variant-csv-step-1-next">Devam</button>
                    <button type="button" class="btn btn-default hidden" id="variant-csv-step-2-prev">Geri</button>
                    <button type="button" class="btn btn-primary hidden" id="variant-csv-step-2-next">Devam</button>
                    <button type="button" class="btn btn-default hidden" id="variant-csv-step-3-prev">Geri</button>
                    <button type="button" class="btn btn-primary hidden" id="variant-csv-process-start">İşlemi Başlat</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="trendyol-template-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Trendyol Şablon Seçimi</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="trendyol_template">Lütfen ürünleriniz için uygun şablonu seçin:</label>
                        <select id="trendyol_template" class="form-control">
                            <option value="general">Genel Ürün Şablonu</option>
                            <option value="fabric">Kumaş Şablonu</option>
                            <option value="home_textile">Ev Tekstili / Masa Örtüsü Şablonu</option>
                        </select>
                        <p class="help-block" style="margin-top: 10px;">
                            <small>Seçtiğiniz şablona göre Excel sütunları Trendyol'un o kategori için istediği düzende oluşturulacaktır.</small>
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
                    <button type="button" class="btn btn-primary" onclick="submitTrendyolExport()">Dışa Aktar</button>
                </div>
            </div>
        </div>
    </div>
    @endpush
