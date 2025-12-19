@extends('admin::layout')

@section('title', 'Sipariş Oluştur')

@component('admin::components.page.header')
    @slot('title', 'Sipariş Oluştur')
    <li class="active">Sipariş Oluştur</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-body">
            <form method="POST" action="{{ route('admin.cart_links.orders.store') }}" id="cart-link-form">
                {{ csrf_field() }}

                <div class="row fc-layout">
                    <div class="col-lg-8 col-md-7">
                        <div class="panel panel-default" style="margin-bottom:10px;">
                            <div class="panel-heading"><strong>Ürünler</strong></div>
                            <div class="panel-body">
                                <div class="row" style="margin-bottom:10px;">
                                    <div class="col-md-6">
                                        <div class="form-group" style="margin-bottom:0;">
                                            <label>Arama (isim / SKU / barkod)</label>
                                            <input type="text" class="form-control" id="product_grid_query" placeholder="Ürün ara..." />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" style="margin-bottom:0;">
                                            <label>Kategori</label>
                                            <select class="form-control" id="product_grid_category">
                                                <option value="">Tümü</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="product-grid" style="margin:0 -6px;"></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover text-center" id="cart-link-products-table">
                                <thead>
                                    <tr>
                                        <th class="text-center">Görsel</th>
                                        <th class="text-center">Ürün</th>
                                        <th class="text-center">SKU</th>
                                        <th class="text-center">Birim Fiyat</th>
                                        <th class="text-center">Varyant</th>
                                        <th class="text-center fc-col-qty">Miktar</th>
                                        <th class="text-center fc-col-actions">Aksiyon</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-5">
                        <div class="fc-right">
                            <div class="panel panel-default" style="margin-bottom:10px;">
                                <div class="panel-heading"><strong>Müşteri</strong></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label>Müşteri seç (opsiyonel)</label>
                                        <select class="selectize prevent-creation" id="cart-link-customer-search" data-url="{{ route('admin.cart_links.customers.search') }}"></select>
                                        <input type="hidden" name="customer_id" id="cart-link-customer-id" />
                                    </div>
                                    <div id="cart-link-guest-fields">
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <div class="form-group">
                                                    <label>Ad</label>
                                                    <input type="text" class="form-control" name="customer_first_name" id="guest_first_name" />
                                                </div>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="form-group">
                                                    <label>Soyad</label>
                                                    <input type="text" class="form-control" name="customer_last_name" id="guest_last_name" />
                                                </div>
                                            </div>
                                            <div class="col-xs-12">
                                                <div class="form-group">
                                                    <label>E-posta</label>
                                                    <input type="email" class="form-control" name="customer_email" id="guest_email" />
                                                </div>
                                            </div>
                                            <div class="col-xs-12">
                                                <div class="form-group">
                                                    <label>Telefon</label>
                                                    <input type="text" class="form-control" name="customer_phone" id="guest_phone" placeholder="+905XXXXXXXXX" />
                                                    <small class="text-muted">Format: +90 + 10 hane</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default" style="margin-bottom:10px;">
                                <div class="panel-heading"><strong>Adres</strong></div>
                                <div class="panel-body">
                                    <div id="customer-address-block" style="display:none;">
                                        <div class="form-group">
                                            <label>Kayıtlı adres (Sevkiyat)</label>
                                            <select class="form-control" id="customer_shipping_address_id" name="shipping_address_id" data-url="{{ route('admin.cart_links.customers.addresses', ['customer' => '__ID__']) }}" style="display:none;"></select>
                                            <div id="customer-shipping-address-cards" class="fc-address-cards" style="margin-top:10px;"></div>
                                        </div>
                                        <div class="form-group">
                                            <label>Kayıtlı adres (Fatura)</label>
                                            <select class="form-control" id="customer_billing_address_id" name="billing_address_id" data-url="{{ route('admin.cart_links.customers.addresses', ['customer' => '__ID__']) }}" style="display:none;"></select>
                                            <div id="customer-billing-address-cards" class="fc-address-cards" style="margin-top:10px;"></div>
                                        </div>
                                    </div>

                                    <div id="guest-address-block">
                                        <div class="form-group">
                                            <label>Adres</label>
                                            <input type="text" class="form-control" name="shipping[address_1]" id="shipping_address_1" />
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <div class="form-group">
                                                    <label>Şehir</label>
                                                    <select class="selectize prevent-creation" id="shipping_city_id" data-url="{{ route('admin.cart_links.locations.cities') }}"></select>
                                                </div>
                                            </div>
                                            <div class="col-xs-6">
                                                <div class="form-group">
                                                    <label>İlçe</label>
                                                    <select class="selectize prevent-creation" id="shipping_district_id" data-url="{{ route('admin.cart_links.locations.districts') }}"></select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="shipping[country]" id="shipping_country" value="TR" />
                                    <input type="hidden" name="shipping[city]" id="shipping_city" value="" />
                                    <input type="hidden" name="shipping[state]" id="shipping_state" value="" />
                                    <input type="hidden" name="billing[country]" id="billing_country" value="TR" />
                                    <input type="hidden" name="billing[address_1]" id="billing_address_1" value="" />
                                    <input type="hidden" name="billing[city]" id="billing_city" value="" />
                                    <input type="hidden" name="billing[state]" id="billing_state" value="" />
                                </div>
                            </div>

                            <div class="panel panel-default" style="margin-bottom:10px;">
                                <div class="panel-heading"><strong>Kargo</strong></div>
                                <div class="panel-body">
                                    <input type="hidden" name="shipping_method" id="cart-link-shipping-method" />
                                    <div id="cart-link-shipping-methods" style="display:flex;flex-direction:column;gap:8px;"></div>
                                </div>
                            </div>

                            <div class="panel panel-default" style="margin-bottom:10px;">
                                <div class="panel-heading"><strong>Ödeme</strong></div>
                                <div class="panel-body">
                                    <input type="hidden" name="payment_method" id="cart-link-payment-method" />
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label>Ödeme yöntemi</label>
                                        <div id="cart-link-payment-methods" style="display:flex;flex-direction:column;gap:8px;"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Ödeme notu</label>
                                        <input type="text" name="payment_note" class="form-control" id="payment_note" placeholder="Dekont no, banka vs." />
                                    </div>
                                </div>
                            </div>

                            <div id="cart-link-preview" class="fc-summary text-right" style="display:none;">
                                <div><span class="text-muted">Ara Toplam</span> <span id="cl_sub" style="min-width:80px;display:inline-block;">0</span></div>
                                <div><span class="text-muted">Kargo</span> <span id="cl_ship" style="min-width:80px;display:inline-block;">0</span></div>
                                <div id="cl_cod_row" style="display:none;"><span class="text-muted">Kapıda Ödeme Ücreti</span> <span id="cl_cod" style="min-width:80px;display:inline-block;">0</span></div>
                                <div><span class="text-muted">İndirim</span> <span id="cl_disc" style="min-width:80px;display:inline-block;">0</span></div>
                                <div><span class="text-muted">Vergi</span> <span id="cl_tax" style="min-width:80px;display:inline-block;">0</span></div>
                                <div><strong>Toplam</strong> <span id="cl_total" style="min-width:80px;display:inline-block;"><strong>0</strong></span></div>
                            </div>

                            <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin-top:10px;">
                                <button type="submit" class="btn btn-primary">Siparişi Oluştur</button>
                                <button type="submit" class="btn btn-default" formaction="{{ route('admin.cart_links.store') }}">Sepet Linki Oluştur</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            @if (session('cart_link_url'))
                <div class="alert alert-success" style="margin-top:10px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
                    <span id="cart-link-url" style="flex:1;word-break:break-all;">{{ session('cart_link_url') }}</span>
                    <button type="button" class="btn btn-default btn-sm" id="copy-cart-link" title="Kopyala"><i class="fa fa-copy"></i></button>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('globals')
    @include('admin::partials.selectize_remote')
    <style>
        .fc-inline { display:flex; gap:10px; align-items:center; width:100%; flex-wrap:wrap; position:relative; }
        .fc-inline .selectize-control { flex:1 1 auto; min-width:0; position:relative; }
        .selectize-control { width:100%; }
        .selectize-control .selectize-input { min-height:40px; padding:8px 12px; border-radius:8px; }
        .fc-inline .selectize-control .selectize-input { padding-right:92px; }
        .selectize-control.form-control { height:auto; }
        .selectize-control.form-control .selectize-input { box-shadow:none; border-color:#d2d6de; }
        .selectize-dropdown [data-selectable] { min-height:60px; padding:10px 12px; display:flex; align-items:center; }
        .selectize-dropdown img.fc-search-img { width:56px; height:56px; object-fit:contain; margin-right:12px; }
        .selectize-dropdown .fc-search-item { display:flex; align-items:center; }
        .selectize-dropdown .fc-search-text { display:flex; flex-direction:column; gap:4px; width:100%; }
        .selectize-dropdown .fc-search-title { font-weight:600; }
        .selectize-dropdown .fc-search-sub { display:flex; justify-content:space-between; }
        .fc-btn { height:36px; padding:0 16px; border-radius:6px; }
        .fc-btn { white-space:nowrap; }
        .fc-btn-in { position:absolute; right:12px; top:50%; transform:translateY(-50%); z-index:3; }
        .table th, .table td { vertical-align:middle !important; }
        .fc-col-qty { width:120px; }
        .fc-col-actions { width:90px; }
        .fc-prod-img { width:64px; height:64px; object-fit:contain; }
        .fc-variant-select { height:36px; }
        .qty-mini { position:relative; width:120px; margin:0 auto; }
        .fc-qty-input { height:36px; padding:6px 28px 6px 10px; border-radius:8px; }
        .qty-mini .qty-suffix { right:8px; color:#555; font-size:12px; position:absolute; top:50%; transform:translateY(-50%); pointer-events:none; }
        .qty-mini .qty-btn { position:absolute; top:50%; transform:translateY(-50%); width:22px; height:22px; border-radius:6px; border:1px solid #d2d6de; background:#fff; padding:0; line-height:20px; text-align:center; cursor:pointer; }
        .qty-mini .qty-btn:active { background:#f5f5f5; }
        .qty-mini .qty-btn.qty-minus { left:6px; }
        .qty-mini .qty-btn.qty-plus { right:6px; }
        .qty-mini .fc-qty-input { padding-left:32px; padding-right:32px; }
        .qty-mini.has-suffix .fc-qty-input { padding-right:44px; }
        .qty-mini.has-suffix .qty-btn.qty-plus { right:22px; }
        .qty-mini input::-webkit-outer-spin-button,
        .qty-mini input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .qty-mini input[type='number'] { -moz-appearance: textfield; }
        .fc-summary { margin-top:12px; padding:12px; border:1px solid #e4e4e4; border-radius:8px; background:#f9fafb; }
        .selectize-dropdown [data-selectable] mark { background:#ffeb3b; color:#000; padding:0 1px; border-radius:2px; }
        .fc-price-edit { cursor:pointer; text-decoration:underline; }
        .fc-right { position: sticky; top: 10px; }
        #product-grid .fc-row { padding:6px; }
        #product-grid .fc-row .fc-row-in { border:1px solid #e5e7eb; border-radius:10px; background:#fff; padding:10px; display:flex; gap:12px; align-items:center; }
        #product-grid .fc-row .fc-row-img { width:56px; height:56px; object-fit:contain; background:#fafafa; border-radius:8px; flex:0 0 auto; }
        #product-grid .fc-row .fc-row-mid { flex:1 1 auto; min-width:0; }
        #product-grid .fc-row .fc-row-title { font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        #product-grid .fc-row .fc-row-sub { color:#666; font-size:12px; display:flex; justify-content:space-between; gap:10px; }
        #product-grid .fc-row .fc-row-price { margin-top:4px; display:flex; gap:8px; align-items:baseline; }
        #product-grid .fc-row .fc-row-price .old { color:#999; text-decoration:line-through; font-size:12px; }
        #product-grid .fc-row .fc-row-price .new { font-weight:700; }
        #product-grid .fc-row .fc-row-badges { margin-top:6px; display:flex; gap:6px; flex-wrap:wrap; }
        #product-grid .fc-row .badge { background:#f3f4f6; color:#111; border:1px solid #e5e7eb; }
        #product-grid .fc-row .fc-row-actions { flex:0 0 auto; display:flex; align-items:center; gap:10px; }
        #product-grid .fc-row .btn { border-radius:8px; }
        @media (max-width: 768px) {
            .fc-inline { flex-direction:column; align-items:stretch; gap:8px; }
            .fc-btn-in { position:static; transform:none; width:100%; }
            .fc-inline .selectize-control .selectize-input { padding-right:12px; }
            .fc-right { position: static; }
        }
        .fc-address-cards { display:flex; flex-direction:column; gap:8px; }
        .fc-address-card { border:1px solid #ddd; border-radius:8px; padding:10px 12px; background:#fff; cursor:pointer; user-select:none; }
        .fc-address-card.active { background:#f5f5f5; border:2px solid #3c8dbc; box-shadow:0 0 0 2px rgba(60,141,188,0.08); }
        .fc-address-card .fc-address-title { font-weight:600; }
        .fc-address-card .fc-address-line { margin-top:4px; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.axios && document.querySelector('meta[name="csrf-token"]')) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            }
            function httpGet(url, params) {
                if (window.axios) {
                    return axios.get(url, { params: params || {} });
                }
                return new Promise(function(resolve, reject) {
                    $.ajax({ url: url, method: 'GET', data: params || {}, success: resolve, error: reject });
                });
            }
            function httpPost(url, data) {
                if (window.axios) {
                    return axios.post(url, data || {});
                }
                return new Promise(function(resolve, reject) {
                    $.ajax({ url: url, method: 'POST', data: data || {}, success: resolve, error: reject });
                });
            }
            const tableBody = document.querySelector('#cart-link-products-table tbody');
            const customerSelect = document.getElementById('cart-link-customer-search');
            const customerIdInput = document.getElementById('cart-link-customer-id');
            const guestFields = document.getElementById('cart-link-guest-fields');
            const customerAddressBlock = document.getElementById('customer-address-block');
            const guestAddressBlock = document.getElementById('guest-address-block');
            const customerShippingAddressSelect = document.getElementById('customer_shipping_address_id');
            const customerBillingAddressSelect = document.getElementById('customer_billing_address_id');
            const customerShippingAddressCards = document.getElementById('customer-shipping-address-cards');
            const customerBillingAddressCards = document.getElementById('customer-billing-address-cards');
            const shippingAddress1 = document.getElementById('shipping_address_1');
            const shippingCityId = document.getElementById('shipping_city_id');
            const shippingDistrictId = document.getElementById('shipping_district_id');
            const shippingCity = document.getElementById('shipping_city');
            const shippingState = document.getElementById('shipping_state');
            const billingAddress1 = document.getElementById('billing_address_1');
            const billingState = document.getElementById('billing_state');
            const billingCity = document.getElementById('billing_city');
            const shippingMethodInput = document.getElementById('cart-link-shipping-method');
            const shippingMethodsBox = document.getElementById('cart-link-shipping-methods');
            const paymentMethodInput = document.getElementById('cart-link-payment-method');
            const paymentMethodsBox = document.getElementById('cart-link-payment-methods');
            const productGrid = document.getElementById('product-grid');
            const productGridQuery = document.getElementById('product_grid_query');
            const productGridCategory = document.getElementById('product_grid_category');
            let items = [];
            let defaultShippingMethodName = '';
            let freeShippingAvailable = false;
            let freeShippingMethodName = '';
            let paymentMethodsCache = null;

            const CURRENCY = '{{ currency() }}';
            const CURRENCY_SYMBOL = (function(code){
                switch (String(code || '').toUpperCase()) {
                    case 'TRY': return '₺';
                    case 'USD': return '$';
                    case 'EUR': return '€';
                    case 'GBP': return '£';
                    default: return code || '';
                }
            })(CURRENCY);
            function fmt(a){
                if (a === null || a === '' || typeof a === 'undefined') return '';
                const n = Number(a);
                if (Number.isNaN(n)) return '';
                return CURRENCY_SYMBOL + ' ' + n.toFixed(2);
            }

            const copyBtn = document.getElementById('copy-cart-link');
            if (copyBtn) {
                copyBtn.addEventListener('click', function(){
                    const urlEl = document.getElementById('cart-link-url');
                    const text = urlEl ? (urlEl.textContent || '').trim() : '';
                    if (!text) return;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text);
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = text;
                        document.body.appendChild(ta);
                        ta.select();
                        try { document.execCommand('copy'); } finally { document.body.removeChild(ta); }
                    }
                });
            }

            function showCustomerAddressUI(show) {
                if (customerAddressBlock) customerAddressBlock.style.display = show ? 'block' : 'none';
                if (guestAddressBlock) guestAddressBlock.style.display = show ? 'none' : 'block';
            }

            function renderRows() {
                tableBody.innerHTML = '';
                items.forEach((item, idx) => {
                    const tr = document.createElement('tr');
                    const unitPrice = (function(){
                        if (item.manual_unit_price !== null && typeof item.manual_unit_price !== 'undefined' && item.manual_unit_price !== '') {
                            return Number(item.manual_unit_price);
                        }
                        if (item.variant_id) {
                            const v = (item.variants || []).find(x => parseInt(x.id,10) === parseInt(item.variant_id,10));
                            if (v && typeof v.price !== 'undefined') return Number(v.price);
                        }
                        return (typeof item.product_price !== 'undefined') ? Number(item.product_price) : null;
                    })();
                    const priceText = fmt(unitPrice);
                    const imgTag = item.image ? `<img src="${item.image}" alt="${item.name || ''}" class="fc-prod-img"/>` : '';
                    const step = item.unit_step ? Number(item.unit_step) : (item.unit_decimal ? 0.01 : 1);
                    const min = item.unit_min ? Number(item.unit_min) : step;
                    const variantSelect = (Array.isArray(item.variants) && item.variants.length)
                        ? `<select class="form-control fc-variant-select" data-variant="${idx}">
                               <option value="">Seçiniz</option>
                               ${item.variants.map(v => `<option value="${v.id}" ${item.variant_id === v.id ? 'selected' : ''}>${v.name || v.sku}</option>`).join('')}
                           </select>`
                        : '-';
                    const qtySuffix = item.unit_suffix ? `<span class="qty-suffix">${item.unit_suffix}</span>` : '';
                    const qtyWrapClass = item.unit_suffix ? 'qty-mini has-suffix' : 'qty-mini';
                    tr.innerHTML = `
                        <td class="text-center">${imgTag}</td>
                        <td class="text-center">${item.name || ''}</td>
                        <td class="text-center">${item.sku || ''}</td>
                        <td class="text-center"><span class="fc-price-edit" data-price-edit="${idx}">${priceText}</span></td>
                        <td class="text-center">${variantSelect}</td>
                        <td class="text-center">
                            <div class="${qtyWrapClass}">
                                <button type="button" class="qty-btn qty-minus" data-qty-minus="${idx}" aria-label="Azalt">-</button>
                                <input type="text" inputmode="decimal" class="form-control text-center fc-qty-input" value="${item.qty}" data-idx="${idx}" data-min="${min}" data-step="${step}" />
                                <button type="button" class="qty-btn qty-plus" data-qty-plus="${idx}" aria-label="Arttır">+</button>
                                ${qtySuffix}
                            </div>
                        </td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-xs" data-del="${idx}" title="Sil"><i class="fa fa-times"></i></button></td>
                    `;
                    tableBody.appendChild(tr);
                });
                bindRowEvents();
                syncFormItems();
                refreshPreview();
            }

            function decimalPlaces(n) {
                var s = String(n);
                if (s.indexOf('.') === -1) return 0;
                return s.split('.')[1].length;
            }

            function clampQty(rawValue, min, step, allowDecimal) {
                var v = Number(rawValue);
                min = Number(min);
                step = Number(step);
                if (!isFinite(step) || step <= 0) step = 1;
                if (!isFinite(min) || min <= 0) min = step;
                if (!isFinite(v)) v = min;
                if (v < min) v = min;

                // Frontend-like: allow manual values (no snapping), but keep precision sane.
                var dp = allowDecimal ? Math.max(2, decimalPlaces(step), decimalPlaces(min)) : 0;
                v = Number(v.toFixed(dp));
                if (v < min) v = min;
                return v;
            }

            function snapQty(rawValue, min, step) {
                var v = Number(rawValue);
                min = Number(min);
                step = Number(step);
                if (!isFinite(step) || step <= 0) step = 1;
                if (!isFinite(min) || min <= 0) min = step;
                if (!isFinite(v)) v = min;
                if (v < min) v = min;

                var steps = Math.round((v - min) / step);
                v = min + steps * step;
                var dp = Math.max(decimalPlaces(step), decimalPlaces(min));
                v = Number(v.toFixed(dp));
                if (v < min) v = min;
                return v;
            }

            function bindRowEvents() {
                tableBody.querySelectorAll('input.fc-qty-input').forEach(inp => {
                    inp.addEventListener('input', function() {
                        const idx = parseInt(this.dataset.idx, 10);
                        // Allow free typing like storefront. Normalize on blur / +/-.
                        const raw = parseFloat(String(this.value || '').replace(',', '.'));
                        if (!Number.isNaN(raw) && isFinite(raw) && items[idx]) {
                            items[idx].qty = raw;
                            syncFormItems();
                        }
                    });

                    inp.addEventListener('blur', function(){
                        const idx = parseInt(this.dataset.idx, 10);
                        const min = this.dataset.min ? Number(this.dataset.min) : 1;
                        const step = this.dataset.step ? Number(this.dataset.step) : 1;
                        const allowDecimal = step < 1 || String(step).indexOf('.') >= 0;
                        const val = clampQty(this.value || min, min, step, allowDecimal);
                        items[idx].qty = val;
                        this.value = String(val);
                        syncFormItems();
                        refreshPreview();
                    });
                });

                tableBody.querySelectorAll('button[data-qty-plus]').forEach(btn => {
                    btn.addEventListener('click', function(){
                        const idx = parseInt(this.getAttribute('data-qty-plus'), 10);
                        const rowInput = tableBody.querySelector(`input.fc-qty-input[data-idx="${idx}"]`);
                        const min = rowInput && rowInput.dataset.min ? Number(rowInput.dataset.min) : 1;
                        const step = rowInput && rowInput.dataset.step ? Number(rowInput.dataset.step) : 1;
                        const cur = items[idx] && isFinite(Number(items[idx].qty)) ? Number(items[idx].qty) : min;
                        const val = snapQty(cur + step, min, step);
                        items[idx].qty = val;
                        if (rowInput) rowInput.value = String(val);
                        syncFormItems();
                        refreshPreview();
                    });
                });

                tableBody.querySelectorAll('button[data-qty-minus]').forEach(btn => {
                    btn.addEventListener('click', function(){
                        const idx = parseInt(this.getAttribute('data-qty-minus'), 10);
                        const rowInput = tableBody.querySelector(`input.fc-qty-input[data-idx="${idx}"]`);
                        const min = rowInput && rowInput.dataset.min ? Number(rowInput.dataset.min) : 1;
                        const step = rowInput && rowInput.dataset.step ? Number(rowInput.dataset.step) : 1;
                        const cur = items[idx] && isFinite(Number(items[idx].qty)) ? Number(items[idx].qty) : min;
                        const val = snapQty(cur - step, min, step);
                        items[idx].qty = val;
                        if (rowInput) rowInput.value = String(val);
                        syncFormItems();
                        refreshPreview();
                    });
                });
                tableBody.querySelectorAll('select[data-variant]').forEach(sel => {
                    sel.addEventListener('change', function() {
                        const idx = parseInt(this.dataset.variant, 10);
                        const val = this.value ? parseInt(this.value, 10) : null;
                        items[idx].variant_id = val;
                        if (val) {
                            const v = (items[idx].variants || []).find(x => parseInt(x.id, 10) === val);
                            if (v) {
                                items[idx].sku = v.sku || items[idx].sku;
                                items[idx].image = v.image || items[idx].image;
                            }
                        } else {
                            items[idx].sku = items[idx].product_sku;
                            items[idx].image = items[idx].product_image;
                        }
                        renderRows();
                        refreshPreview();
                    });
                });
                tableBody.querySelectorAll('button[data-del]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = parseInt(this.dataset.del, 10);
                        items.splice(idx, 1);
                        renderRows();
                        refreshPreview();
                    });
                });

                tableBody.querySelectorAll('[data-price-edit]').forEach(el => {
                    el.addEventListener('click', function() {
                        const idx = parseInt(this.getAttribute('data-price-edit'), 10);
                        const current = items[idx] && items[idx].manual_unit_price !== null && typeof items[idx].manual_unit_price !== 'undefined'
                            ? items[idx].manual_unit_price
                            : '';
                        const input = document.createElement('input');
                        input.type = 'number';
                        input.min = '0';
                        input.step = '0.01';
                        input.className = 'form-control input-sm';
                        input.value = current;
                        this.replaceWith(input);
                        input.focus();

                        function commit() {
                            const v = input.value;
                            if (v === '' || v === null || typeof v === 'undefined') {
                                items[idx].manual_unit_price = null;
                            } else {
                                const n = Number(v);
                                items[idx].manual_unit_price = Number.isNaN(n) ? null : n;
                            }
                            renderRows();
                        }
                        input.addEventListener('blur', commit);
                        input.addEventListener('keydown', function(e){
                            if (e.key === 'Enter') { e.preventDefault(); commit(); }
                            if (e.key === 'Escape') { e.preventDefault(); renderRows(); }
                        });
                    });
                });
            }

            function syncFormItems() {
                let hidden = document.getElementById('cart-link-items');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'items';
                    hidden.id = 'cart-link-items';
                    document.getElementById('cart-link-form').appendChild(hidden);
                }
                hidden.value = JSON.stringify(items);
            }

            function setPreviewVisible(visible){
                var el = document.getElementById('cart-link-preview');
                el.style.display = visible ? 'block' : 'none';
            }

            function refreshPreview(){
                if (!items.length) { setPreviewVisible(false); return; }
                const hasCustomer = customerIdInput && customerIdInput.value;

                // Ensure shipping_method has a sensible value. If empty, backend will fallback to first available.
                if (shippingMethodInput && !String(shippingMethodInput.value || '').trim() && defaultShippingMethodName) {
                    shippingMethodInput.value = defaultShippingMethodName;
                }

                if (paymentMethodInput && !String(paymentMethodInput.value || '').trim()) {
                    // Let backend accept empty for preview; createOrder will validate.
                }

                if (!hasCustomer) {
                    if (billingAddress1) billingAddress1.value = shippingAddress1 ? (shippingAddress1.value || '') : '';
                    if (billingCity) billingCity.value = shippingCity ? (shippingCity.value || '') : '';
                    if (billingState) billingState.value = shippingState ? (shippingState.value || '') : '';
                }

                const payload = {
                    items: JSON.stringify(items),
                    customer_id: customerIdInput ? (customerIdInput.value || '') : '',
                    shipping_address_id: hasCustomer && customerShippingAddressSelect ? (customerShippingAddressSelect.value || '') : '',
                    billing_address_id: hasCustomer && customerBillingAddressSelect ? (customerBillingAddressSelect.value || '') : '',
                    shipping_method: shippingMethodInput ? (shippingMethodInput.value || '') : '',
                    payment_method: paymentMethodInput ? (paymentMethodInput.value || '') : '',
                    shipping: hasCustomer ? {} : {
                        country: document.getElementById('shipping_country') ? document.getElementById('shipping_country').value : 'TR',
                        address_1: shippingAddress1 ? (shippingAddress1.value || '') : '',
                        city: shippingCity ? (shippingCity.value || '') : '',
                        state: shippingState ? (shippingState.value || '') : '',
                    },
                    billing: hasCustomer ? {} : {
                        country: document.getElementById('billing_country') ? document.getElementById('billing_country').value : 'TR',
                        address_1: billingAddress1 ? (billingAddress1.value || '') : '',
                        city: billingCity ? (billingCity.value || '') : '',
                        state: billingState ? (billingState.value || '') : '',
                    }
                };
                httpPost(`{{ route('admin.cart_links.preview') }}`, payload)
                    .then(function(resp){
                        var data = resp.data || resp;
                        if (!data || !data.summary) { setPreviewVisible(false); return; }
                        document.getElementById('cl_sub').textContent = fmt(data.summary.sub_total);
                        document.getElementById('cl_ship').textContent = fmt(data.summary.shipping_cost);
                        var codRow = document.getElementById('cl_cod_row');
                        var codVal = document.getElementById('cl_cod');
                        if (codRow && codVal) {
                            var mode = String(data.summary.cod_fee_display_mode || 'separate_line');
                            var fee = Number(data.summary.cod_fee || 0);
                            if (mode === 'separate_line' && fee > 0) {
                                codVal.textContent = fmt(fee);
                                codRow.style.display = 'block';
                            } else {
                                codRow.style.display = 'none';
                            }
                        }
                        document.getElementById('cl_disc').textContent = fmt(data.summary.discount);
                        document.getElementById('cl_tax').textContent = fmt(data.summary.tax);
                        document.getElementById('cl_total').textContent = fmt(data.summary.total);
                        setPreviewVisible(true);

                        if (data.cart && data.cart.availableShippingMethods) {
                            updateShippingChoices(data.cart.availableShippingMethods, data.cart.shippingMethodName);
                        }

                        if (data && data.payment_methods) {
                            paymentMethodsCache = data.payment_methods;
                            renderPaymentMethods(paymentMethodsCache);
                        }
                    })
                    .catch(function(err){
                        // Keep UI usable even if preview fails (e.g. missing address/method).
                        try { console.error('cart-links preview failed', err && err.response ? err.response.data : err); } catch (e) {}
                        setPreviewVisible(false);
                    });
            }

            function renderShippingMethods(methodsMap){
                if (!shippingMethodsBox || !shippingMethodInput) return;
                var map = methodsMap && typeof methodsMap === 'object' ? methodsMap : {};
                var keys = Object.keys(map);
                shippingMethodsBox.innerHTML = '';
                if (!keys.length) {
                    shippingMethodsBox.innerHTML = '<div class="text-muted">Kargo yöntemi bulunamadı.</div>';
                    return;
                }
                var current = String(shippingMethodInput.value || '') || String(keys[0] || '');
                if (!current || !map[current]) {
                    current = String(keys[0] || '');
                    shippingMethodInput.value = current;
                }
                keys.forEach(function(k){
                    var m = map[k] || {};
                    var id = 'cl_sm_' + String(k).replace(/[^a-zA-Z0-9_\-]/g, '_');
                    var label = m.label || m.name || k;
                    var cost = '';
                    try {
                        if (m.cost && (m.cost.inCurrentCurrency && typeof m.cost.inCurrentCurrency.amount !== 'undefined')) {
                            cost = fmt(m.cost.inCurrentCurrency.amount);
                        } else if (m.cost && typeof m.cost.amount !== 'undefined') {
                            cost = fmt(m.cost.amount);
                        }
                    } catch(e) {}
                    var div = document.createElement('label');
                    div.style.display = 'flex';
                    div.style.alignItems = 'center';
                    div.style.gap = '8px';
                    div.innerHTML = `<input type="radio" name="_cl_shipping_method" id="${id}" value="${k}" ${String(current)===String(k)?'checked':''} />
                        <span style="flex:1;">${label}</span>
                        <span class="text-muted">${cost || ''}</span>`;
                    div.querySelector('input').addEventListener('change', function(){
                        shippingMethodInput.value = k;
                        refreshPreview();
                    });
                    shippingMethodsBox.appendChild(div);
                });
            }

            function renderPaymentMethods(methods){
                if (!paymentMethodsBox || !paymentMethodInput) return;
                var arr = Array.isArray(methods) ? methods : (methods && typeof methods === 'object' ? Object.values(methods) : []);
                paymentMethodsBox.innerHTML = '';
                if (!arr.length) {
                    paymentMethodsBox.innerHTML = '<div class="text-muted">Ödeme yöntemi bulunamadı.</div>';
                    return;
                }
                var current = String(paymentMethodInput.value || '') || String((arr[0] && arr[0].code) ? arr[0].code : '');
                if (!current) {
                    current = String((arr[0] && arr[0].code) ? arr[0].code : '');
                    paymentMethodInput.value = current;
                }
                arr.forEach(function(pm){
                    var code = pm.code || '';
                    if (!code) return;
                    var id = 'cl_pm_' + String(code).replace(/[^a-zA-Z0-9_\-]/g, '_');
                    var label = pm.label || code;
                    var desc = pm.description || '';
                    var div = document.createElement('label');
                    div.style.display = 'flex';
                    div.style.flexDirection = 'column';
                    div.style.gap = '2px';
                    div.style.padding = '6px 0';
                    div.innerHTML = `<div style="display:flex;align-items:center;gap:8px;">
                            <input type="radio" name="_cl_payment_method" id="${id}" value="${code}" ${String(current)===String(code)?'checked':''} />
                            <span>${label}</span>
                        </div>
                        ${desc ? `<div class="text-muted" style="margin-left:22px;">${desc}</div>` : ''}`;
                    div.querySelector('input').addEventListener('change', function(){
                        paymentMethodInput.value = code;
                        refreshPreview();
                    });
                    paymentMethodsBox.appendChild(div);
                });
            }

            function updateShippingChoices(methods, selectedName){
                const list = (function(m){
                    if (Array.isArray(m)) return m;
                    if (m && typeof m === 'object') {
                        return Object.keys(m).map(function(k){
                            var v = m[k] || {};
                            if (v && typeof v === 'object') {
                                v.__key = k;
                            }
                            return v;
                        });
                    }
                    return [];
                })(methods);

                const freeMethod = list.find(function(m){
                    const raw = String(m.__key || m.name || m.driver || m.method || m.code || '');
                    const label = String(m.label || m.title || '');
                    return raw === 'free_shipping' || raw === 'free' || raw.indexOf('free_shipping') >= 0 || label.toLowerCase().indexOf('ücretsiz') >= 0 || label.toLowerCase().indexOf('free') >= 0;
                }) || null;

                freeShippingMethodName = freeMethod ? String(freeMethod.__key || freeMethod.name || freeMethod.driver || freeMethod.method || freeMethod.code || '') : '';
                freeShippingAvailable = !!freeShippingMethodName;
                const picked = selectedName || (list[0] ? String(list[0].__key || list[0].name || list[0].driver || list[0].method || list[0].code || '') : '');
                defaultShippingMethodName = String(picked || '');

                // Prefer using normalized map returned by backend
                if (methods && typeof methods === 'object' && !Array.isArray(methods)) {
                    renderShippingMethods(methods);
                }
            }

            function toggleGuestUI(){
                const hasCustomer = customerIdInput && customerIdInput.value;
                if (guestFields) guestFields.style.display = (!hasCustomer) ? 'block' : 'none';
                showCustomerAddressUI(!!hasCustomer);
            }

            var guestPhoneEl = document.getElementById('guest_phone');
            if (guestPhoneEl) {
                guestPhoneEl.addEventListener('input', function(){
                    var v = String(guestPhoneEl.value || '');
                    v = v.replace(/\s+/g, '');
                    if (!v.startsWith('+90')) {
                        v = '+90' + v.replace(/^\+?90/, '').replace(/^0+/, '');
                    }
                    var digits = v.replace(/\D/g, '');
                    if (!digits.startsWith('90')) {
                        digits = '90' + digits;
                    }
                    digits = digits.slice(0, 12);
                    guestPhoneEl.value = '+' + digits;
                });
            }

            if (shippingAddress1) shippingAddress1.addEventListener('input', function(){ refreshPreview(); });

            // Shipping/payment UIs are rendered from preview response.

            if (customerShippingAddressSelect) {
                customerShippingAddressSelect.addEventListener('change', function(){ refreshPreview(); });
            }
            if (customerBillingAddressSelect) {
                customerBillingAddressSelect.addEventListener('change', function(){ refreshPreview(); });
            }

            function makeAddressCard(addr) {
                var div = document.createElement('div');
                div.className = 'fc-address-card';
                div.setAttribute('data-id', addr.id);

                var name = (addr.full_name ? addr.full_name : (((addr.first_name || '') + ' ' + (addr.last_name || '')).trim()));
                var city = addr.city_title || addr.city || '';
                var district = addr.district_title || addr.state || '';
                var loc = (city ? city : '') + (district ? (city ? ', ' : '') + district : '');

                var lines = [];
                if (loc) lines.push(loc);
                if (addr.phone) lines.push('Telefon: ' + addr.phone);
                if (addr.company_name) lines.push('Firma Adı: ' + addr.company_name);

                var taxNo = addr.tax_number || addr.invoice_tax_number || '';
                if (taxNo) lines.push('Vergi Numarası / TCKN: ' + taxNo);
                var taxOffice = addr.tax_office || addr.invoice_tax_office || '';
                if (taxOffice) lines.push('Vergi Dairesi: ' + taxOffice);

                div.innerHTML = `<div class="fc-address-title">${name || ''}</div>`
                    + lines.map(function(t){ return `<div class="fc-address-line">${t}</div>`; }).join('');

                return div;
            }

            function highlightAddressCards(container, selectedId) {
                if (!container) return;
                container.querySelectorAll('.fc-address-card').forEach(function(el){
                    el.classList.toggle('active', String(el.getAttribute('data-id')) === String(selectedId || ''));
                });
            }

            function renderCustomerAddressCards(addresses) {
                if (!customerShippingAddressCards || !customerBillingAddressCards) return;
                customerShippingAddressCards.innerHTML = '';
                customerBillingAddressCards.innerHTML = '';

                (addresses || []).forEach(function(addr){
                    var s = makeAddressCard(addr);
                    s.addEventListener('click', function(){
                        if (customerShippingAddressSelect) customerShippingAddressSelect.value = String(addr.id);
                        highlightAddressCards(customerShippingAddressCards, addr.id);
                        refreshPreview();
                    });
                    customerShippingAddressCards.appendChild(s);

                    var b = makeAddressCard(addr);
                    b.addEventListener('click', function(){
                        if (customerBillingAddressSelect) customerBillingAddressSelect.value = String(addr.id);
                        highlightAddressCards(customerBillingAddressCards, addr.id);
                        refreshPreview();
                    });
                    customerBillingAddressCards.appendChild(b);
                });

                highlightAddressCards(customerShippingAddressCards, customerShippingAddressSelect ? customerShippingAddressSelect.value : '');
                highlightAddressCards(customerBillingAddressCards, customerBillingAddressSelect ? customerBillingAddressSelect.value : '');
            }

            function loadCities(){
                if (!shippingCityId) return;
                httpGet(shippingCityId.getAttribute('data-url')).then(function(resp){
                    var data = resp.data || resp;
                    shippingCityId.innerHTML = '<option value="">Şehir seç</option>';
                    (data || []).forEach(function(c){
                        var opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        shippingCityId.appendChild(opt);
                    });

                    var cityInst = $(shippingCityId)[0] && $(shippingCityId)[0].selectize ? $(shippingCityId)[0].selectize : null;
                    if (!cityInst) {
                        $(shippingCityId).selectize({
                            allowEmptyOption: true,
                            placeholder: 'Şehir seç',
                            wrapperClass: 'selectize-control form-control single',
                            dropdownClass: 'selectize-dropdown form-control single',
                            onChange: function(value) {
                                loadDistricts(value);
                                syncCityDistrictToHidden();
                                refreshPreview();
                            }
                        });
                        cityInst = $(shippingCityId)[0] && $(shippingCityId)[0].selectize ? $(shippingCityId)[0].selectize : null;
                    } else {
                        var cur = String(cityInst.getValue() || '');
                        cityInst.clearOptions();
                        (data || []).forEach(function(c){
                            cityInst.addOption({ id: c.id, name: c.name });
                        });
                        cityInst.refreshOptions(false);
                        if (cur) {
                            cityInst.setValue(cur, true);
                        }
                    }

                    // Ensure district is in a good initial state.
                    var cityValue = cityInst ? cityInst.getValue() : ($(shippingCityId).val() || '');
                    loadDistricts(cityValue || '');
                });
            }

            function loadDistricts(cityId){
                if (!shippingDistrictId) return;

                var districtInst = $(shippingDistrictId)[0] && $(shippingDistrictId)[0].selectize ? $(shippingDistrictId)[0].selectize : null;

                if (!cityId) {
                    shippingDistrictId.innerHTML = '<option value="">Önce şehir seç</option>';

                    if (!districtInst) {
                        $(shippingDistrictId).selectize({
                            allowEmptyOption: true,
                            placeholder: 'Önce şehir seç',
                            wrapperClass: 'selectize-control form-control single',
                            dropdownClass: 'selectize-dropdown form-control single',
                            onChange: function() {
                                syncCityDistrictToHidden();
                                refreshPreview();
                            }
                        });
                        districtInst = $(shippingDistrictId)[0] && $(shippingDistrictId)[0].selectize ? $(shippingDistrictId)[0].selectize : null;
                    } else {
                        districtInst.clear(true);
                        districtInst.clearOptions();
                        districtInst.addOption({ id: '', name: 'Önce şehir seç' });
                        districtInst.refreshOptions(false);
                    }

                    if (districtInst) districtInst.disable();
                    return;
                }

                var url = shippingDistrictId.getAttribute('data-url');
                httpGet(url, { city_id: cityId || '' }).then(function(resp){
                    var data = resp.data || resp;
                    shippingDistrictId.innerHTML = '<option value="">İlçe seç</option>';
                    (data || []).forEach(function(d){
                        var opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.name;
                        shippingDistrictId.appendChild(opt);
                    });

                    if (!districtInst) {
                        $(shippingDistrictId).selectize({
                            allowEmptyOption: true,
                            placeholder: 'İlçe seç',
                            wrapperClass: 'selectize-control form-control single',
                            dropdownClass: 'selectize-dropdown form-control single',
                            onChange: function() {
                                syncCityDistrictToHidden();
                                refreshPreview();
                            }
                        });
                        districtInst = $(shippingDistrictId)[0] && $(shippingDistrictId)[0].selectize ? $(shippingDistrictId)[0].selectize : null;
                    }

                    if (districtInst) {
                        var cur = String(districtInst.getValue() || '');
                        districtInst.clearOptions();
                        (data || []).forEach(function(d){
                            districtInst.addOption({ id: d.id, name: d.name });
                        });
                        districtInst.refreshOptions(false);
                        districtInst.enable();
                        if (cur) {
                            districtInst.setValue(cur, true);
                        }
                    }
                });
            }

            function syncCityDistrictToHidden(){
                if (shippingCityId) {
                    var cityText = shippingCityId.options && shippingCityId.selectedIndex >= 0 ? (shippingCityId.options[shippingCityId.selectedIndex].textContent || '') : '';
                    if (shippingCity) shippingCity.value = (shippingCityId.value ? cityText : '');
                }
                if (shippingDistrictId) {
                    var distText = shippingDistrictId.options && shippingDistrictId.selectedIndex >= 0 ? (shippingDistrictId.options[shippingDistrictId.selectedIndex].textContent || '') : '';
                    if (shippingState) shippingState.value = (shippingDistrictId.value ? distText : '');
                }
            }

            loadCities();

            function loadCustomerAddresses(customerId) {
                if (!customerShippingAddressSelect || !customerBillingAddressSelect) return;
                var urlTpl = customerShippingAddressSelect.getAttribute('data-url') || '';
                var url = urlTpl.replace('__ID__', customerId);
                httpGet(url).then(function(resp){
                    var data = resp.data || resp;
                    customerShippingAddressSelect.innerHTML = '<option value="">Adres seç</option>';
                    customerBillingAddressSelect.innerHTML = '<option value="">Adres seç</option>';
                    (data || []).forEach(function(a){
                        var label = (a.full_name ? a.full_name : ((a.first_name||'') + ' ' + (a.last_name||''))) + ' - ' + (a.address_1||'') + ' - ' + (a.city_title || a.city || '') + (a.district_title ? ('/' + a.district_title) : '');
                        var opt = document.createElement('option');
                        opt.value = a.id;
                        opt.textContent = label;
                        customerShippingAddressSelect.appendChild(opt);
                        var opt2 = opt.cloneNode(true);
                        customerBillingAddressSelect.appendChild(opt2);
                    });
                    if ((data || []).length) {
                        customerShippingAddressSelect.value = String((data[0] && data[0].id) ? data[0].id : '');
                        customerBillingAddressSelect.value = String((data[0] && data[0].id) ? data[0].id : '');
                    }

                    renderCustomerAddressCards(Array.isArray(data) ? data : []);
                });
            }

            function fetchGridProducts(){
                if (!productGrid) return;
                var q = productGridQuery ? String(productGridQuery.value || '').trim() : '';
                var cat = productGridCategory ? String(productGridCategory.value || '').trim() : '';
                httpGet(`{{ route('admin.cart_links.products.search') }}`, { query: q, category_id: cat || undefined })
                    .then(function(resp){
                        var data = resp.data || resp;
                        renderProductGrid(Array.isArray(data) ? data : []);
                    });
            }

            function renderProductGrid(products){
                productGrid.innerHTML = '';
                products.forEach(function(p){
                    var col = document.createElement('div');
                    col.className = 'col-xs-12 fc-row';
                    var img = p.image ? `<img src="${p.image}" class="fc-row-img" alt="${(p.name||'')}"/>` : `<div class="fc-row-img"></div>`;
                    var selling = (typeof p.selling_price !== 'undefined' && p.selling_price !== null) ? Number(p.selling_price) : (typeof p.price !== 'undefined' ? Number(p.price) : null);
                    var original = (typeof p.original_price !== 'undefined' && p.original_price !== null) ? Number(p.original_price) : null;
                    var hasDiscount = (original !== null && selling !== null && !Number.isNaN(original) && !Number.isNaN(selling) && original > selling);
                    var priceHtml = hasDiscount
                        ? `<div class="fc-row-price"><span class="old">${fmt(original)}</span><span class="new">${fmt(selling)}</span></div>`
                        : `<div class="fc-row-price"><span class="new">${fmt(selling)}</span></div>`;
                    var stockText = (function(){
                        if (p.manage_stock) {
                            var q = (typeof p.qty !== 'undefined' && p.qty !== null) ? Number(p.qty) : null;
                            if (q === null || Number.isNaN(q)) return 'Stok: -';
                            var suf = (p.unit_suffix ? String(p.unit_suffix) : '');
                            return 'Stok: ' + q + (suf ? (' ' + suf) : '');
                        }
                        return (p.in_stock ? 'Stokta' : 'Stokta değil');
                    })();
                    col.innerHTML = `
                        <div class="fc-row-in">
                            ${img}
                            <div class="fc-row-mid">
                                <div class="fc-row-title">${p.name || p.sku || ''}</div>
                                <div class="fc-row-sub">
                                    <span>${p.sku || ''}</span>
                                    <span>${stockText}</span>
                                </div>
                                ${priceHtml}
                            </div>
                            <div class="fc-row-actions">
                                <button type="button" class="btn btn-default btn-sm" data-grid-add="${p.id}">Ekle</button>
                            </div>
                        </div>
                    `;
                    col.querySelector('[data-grid-add]').addEventListener('click', function(){
                        var step = p.unit_step ? Number(p.unit_step) : (p.unit_decimal ? 0.01 : 1);
                        var min = p.unit_min ? Number(p.unit_min) : step;
                        var existing = items.find(x => String(x.product_id) === String(p.id) && (x.variant_id == null));
                        if (existing) {
                            existing.qty = snapQty(Number(existing.qty || min) + step, min, step);
                        } else {
                            items.push({
                                product_id: parseInt(p.id, 10),
                                variant_id: null,
                                qty: snapQty(min, min, step),
                                options: {},
                                sku: p.sku,
                                name: p.name,
                                image: p.image || '',
                                product_sku: p.sku,
                                product_image: p.image || '',
                                product_price: (typeof p.price !== 'undefined') ? p.price : null,
                                variants: Array.isArray(p.variants) ? p.variants : [],
                                unit_step: p.unit_step || null,
                                unit_min: p.unit_min || null,
                                unit_decimal: p.unit_decimal || null,
                                unit_suffix: p.unit_suffix || null,
                                manual_unit_price: null
                            });
                        }
                        renderRows();
                    });
                    productGrid.appendChild(col);
                });
            }

            var gridTimer = null;
            function scheduleGridFetch(){
                if (gridTimer) clearTimeout(gridTimer);
                gridTimer = setTimeout(fetchGridProducts, 250);
            }
            if (productGridQuery) productGridQuery.addEventListener('input', scheduleGridFetch);
            if (productGridCategory) productGridCategory.addEventListener('change', fetchGridProducts);
            fetchGridProducts();

            if (customerSelect) {
                $(customerSelect).on('change', function() {
                    const value = $(customerSelect).val();
                    if (!customerIdInput) return;
                    customerIdInput.value = value || '';
                    if (value) {
                        loadCustomerAddresses(value);
                    }
                    toggleGuestUI();
                    refreshPreview();
                });
            }

            var formEl = document.getElementById('cart-link-form');
            if (formEl) {
                formEl.addEventListener('submit', function(){
                    if (shippingMethodInput && !String(shippingMethodInput.value || '').trim()) {
                        var firstShip = shippingMethodsBox ? shippingMethodsBox.querySelector('input[type="radio"][name="_cl_shipping_method"]') : null;
                        if (firstShip) {
                            shippingMethodInput.value = firstShip.value;
                            firstShip.checked = true;
                        }
                    }
                    if (paymentMethodInput && !String(paymentMethodInput.value || '').trim()) {
                        var firstPay = paymentMethodsBox ? paymentMethodsBox.querySelector('input[type="radio"][name="_cl_payment_method"]') : null;
                        if (firstPay) {
                            paymentMethodInput.value = firstPay.value;
                            firstPay.checked = true;
                        }
                    }
                });
            }

            toggleGuestUI();
            refreshPreview();
        });
    </script>
@endpush
