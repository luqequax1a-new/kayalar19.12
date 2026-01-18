@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('order::orders.orders'))

    <li class="active">{{ trans('order::orders.orders') }}</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-body index-table" id="orders-table">
            @component('admin::components.table')
                @slot('thead')
                    <tr>
                        <th class="no-sort">
                            <div class="checkbox">
                                <input type="checkbox" class="select-all" id="select-all">
                                <label for="select-all"></label>
                            </div>
                        </th>
                        <th>{{ trans('admin::admin.table.id') }}</th>
                        <th>{{ trans('order::orders.table.customer_name') }}</th>
                        <th>{{ trans('order::orders.table.customer_email') }}</th>
                        <th>{{ trans('order::orders.payment_method') }}</th>
                        <th>{{ trans('admin::admin.table.status') }}</th>
                        <th>{{ trans('order::orders.table.total') }}</th>
                        <th>Sipariş Sayısı</th>
                        <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                        <th>{{ trans('admin::admin.table.actions') }}</th>
                        <th class="hidden">Child Data</th>
                    </tr>
                @endslot
            @endcomponent
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .child-row-container { display: none !important; }
        
        .order-no-wrapper {
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px;
            border-radius: 6px;
            width: fit-content;
        }
        .order-no-wrapper:hover {
            color: #3b82f6;
            background: #f1f5f9;
        }
        .order-no-wrapper svg { color: #94a3b8; }

        /* Status select ikas Style */
        .status-select-styled {
            height: 32px !important;
            padding: 0 32px 0 12px !important; /* Increased right padding for arrow */
            border-radius: 4px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            border: 1px solid transparent !important;
            cursor: pointer !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-repeat: no-repeat !important;
            background-position: calc(100% - 8px) center !important; /* Arrow position from right */
            background-size: 10px 10px !important;
            transition: all 0.2s;
            min-width: 140px;
            text-align: left;
            line-height: normal !important;
        }

        .badge-success { 
            background-color: #f6fffd !important; 
            color: #3cc3a1 !important; 
            border-color: #3cc3a1 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%233cc3a1' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
        }
        .badge-danger { 
            background-color: #fff5f5 !important; 
            color: #ef4444 !important; 
            border-color: #ef4444 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23ef4444' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
        }
        .badge-info { 
            background-color: #f5f9ff !important; 
            color: #4a90e2 !important; 
            border-color: #4a90e2 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%234a90e2' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
        }
        .badge-warning { 
            background-color: #fffaf5 !important; 
            color: #f5a623 !important; 
            border-color: #f5a623 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23f5a623' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
        }
        .badge-pending { 
            background-color: #fafafa !important; 
            color: #8c8c8c !important; 
            border-color: #d9d9d9 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238c8c8c' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
        }

        /* Modern Drawer Design */
        #order-drawer-backdrop {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: none; opacity: 0;
            transition: opacity 0.3s ease;
        }
        #order-drawer-backdrop.open { display: block; opacity: 1; }
        
        #order-drawer {
            position: fixed;
            top: 0; right: -650px;
            width: 600px;
            height: 100%;
            background: #f8fafc;
            box-shadow: -10px 0 50px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
        }
        #order-drawer.open { right: 0; }

        .drawer-header {
            padding: 24px 30px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }
        .drawer-header h4 {
            margin: 0; font-weight: 800; color: #0f172a; font-size: 18px;
            display: flex; align-items: center; gap: 10px;
        }
        .drawer-header .close-btn {
            background: #f1f5f9; border: none; width: 36px; height: 36px;
            border-radius: 10px; font-size: 20px; color: #64748b;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .drawer-header .close-btn:hover { background: #fee2e2; color: #ef4444; transform: rotate(90deg); }
        
        .drawer-body { flex: 1; overflow-y: auto; padding: 0; }
        
        /* Drawer Content Cards */
        .drawer-section {
            background: #fff;
            margin: 15px 25px;
            padding: 20px;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.7);
        }
        .drawer-section-title {
            font-size: 13px; font-weight: 700; color: #1e293b;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }

        @media (max-width: 640px) {
            #order-drawer {
                width: 100% !important;
                right: -100%;
                border-radius: 0;
            }
            .drawer-section {
                margin: 10px 15px;
                padding: 15px;
            }
            .address-group {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .drawer-header {
                padding: 15px 20px;
            }
            .drawer-footer {
                padding: 15px 20px;
            }
            .product-item {
                gap: 12px;
            }
            .product-img {
                width: 44px;
                height: 44px;
            }
            .product-name {
                font-size: 13px;
            }
            .product-price {
                font-size: 14px;
            }
        }
        .drawer-section-title i { color: #3b82f6; width: 28px; height: 28px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        
        /* New Clean Address Styles */
        .address-details-clean { display: flex; flex-direction: column; gap: 8px; }
        .clean-addr-item { display: flex; align-items: flex-start; gap: 10px; color: #64748b; }
        .addr-svg { width: 16px; height: 16px; color: #94a3b8; flex-shrink: 0; margin-top: 2px; }
        .addr-val-main { font-weight: 700; color: #1e293b; font-size: 14px; }
        .addr-val-sub { font-weight: 600; color: #475569; font-size: 13px; line-height: 1.4; }
        .addr-val-text { font-weight: 500; color: #64748b; font-size: 13px; }
        .addr-label { color: #94a3b8; font-weight: 600; font-size: 11px; text-transform: uppercase; margin-right: 4px; }

        /* Product List */
        .product-item {
            display: flex; align-items: center; gap: 16px;
            padding: 12px 0; border-bottom: 1px solid #f1f5f9;
        }
        .product-item:last-child { border: none; }
        .product-img {
            width: 52px; height: 52px; border-radius: 10px;
            background: #f8fafc; border: 1px solid #f1f5f9; overflow: hidden; flex-shrink: 0;
        }
        .product-img img { width: 100%; height: 100%; object-fit: contain; }
        .product-info { flex: 1; min-width: 0; }
        .product-name { font-weight: 600; color: #1e293b; font-size: 14px; margin-bottom: 4px; display: block; }
        .product-meta { font-size: 12px; color: #64748b; font-weight: 500; }
        
        .product-right { text-align: right; min-width: 100px; }
        .product-price { 
            font-weight: 800; 
            color: #0f172a; 
            font-size: 16px; 
            margin-bottom: 6px;
            display: block;
        }
        .product-qty { 
            font-size: 13px; 
            color: #1e293b; 
            font-weight: 800; 
            background: #f1f5f9; 
            padding: 5px 12px; 
            border-radius: 8px;
            display: inline-block;
            border: none;
        }

        /* Totals Area */
        .summary-row { display: flex; justify-content: space-between; font-size: 14px; color: #64748b; margin-bottom: 10px; }
        .summary-row.total { 
            margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e2e8f0;
            color: #0f172a; font-weight: 800; font-size: 17px;
        }
        .summary-row span:last-child { color: #0f172a; font-weight: 600; }
        .summary-row.total span:last-child { color: #3b82f6; }
        .payment-info { background: #f8fafc; padding: 12px 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; }

        .drawer-footer {
            padding: 20px 30px; border-top: 1px solid #e2e8f0;
            display: flex; justify-content: flex-end; background: #fff;
        }

        .drawer-body::-webkit-scrollbar { width: 4px; }
        .drawer-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* Premium Notification Styling */
        #notification-toast {
            top: 20px !important;
            right: 20px !important;
            z-index: 10001;
        }
        .ohsnap-alert {
            border-radius: 10px !important;
            padding: 12px 20px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            font-family: inherit !important;
            font-weight: 500 !important;
            font-size: 14px !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            margin-top: 10px !important;
            animation: slideInRight 0.3s ease-out;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .ohsnap-alert-green { background: #10b981 !important; color: #fff !important; }
        .ohsnap-alert-red { background: #ef4444 !important; color: #fff !important; }
        .ohsnap-alert-blue { background: #3b82f6 !important; color: #fff !important; }
        .ohsnap-alert-yellow { background: #f59e0b !important; color: #fff !important; }
    </style>
@endpush

@push('scripts')
    <script type="module">
        DataTable.set('#orders-table .table', {
            routePrefix: 'orders',
            routes: {
                table: 'table',
                show: 'show',
            }
        });

        function mediaUrl(p) {
            if (!p) return '';
            if (/^https?:\/\//.test(p)) return p;
            if (p.startsWith('/')) return FleetCart.baseUrl + p;
            if (p.startsWith('storage/')) return FleetCart.baseUrl + '/' + p;
            return FleetCart.baseUrl + '/storage/' + p;
        }

        function renderDrawer(d) {
            const data = d.child_data || {};
            const shipping = data.shipping || {};
            const billing = data.billing || {};
            const products = data.products || [];
            const totals = data.totals || {};

            let productsHtml = '';
            products.forEach(p => {
                const imageUrl = mediaUrl(p.image);
                const img = imageUrl ? `<div class="thumbnail-holder"><img src="${imageUrl}" alt="thumb"></div>` : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f1f5f9; border-radius:6px;"><i class="fa fa-picture-o" style="font-size:18px; color:#cbd5e1;"></i></div>';
                
                let displayQty = parseFloat(p.qty).toString();
                let displayUnit = (p.unit && p.unit !== 'null' && p.unit !== 'undefined') ? p.unit : '';

                const upsellBadge = p.is_upsell ? `<div style="margin-bottom: 2px;"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold" style="display: inline-block; background-color:#fef3c7;color:#92400e; font-size: 10px; border-radius: 4px; padding: 2px 6px;">{{ trans('storefront::upsell.offer_badge') }}</span></div>` : '';
                const originalPriceHtml = (p.is_upsell && p.original_price) ? `<span style="color: #94a3b8; font-size: 12px; text-decoration: line-through; margin-right: 6px;">${p.original_price}</span>` : '';

                productsHtml += `<div class="product-item">
                    <div class="product-img">${img}</div>
                    <div class="product-info">
                        ${upsellBadge}
                        <div class="product-name">${p.name}</div>
                        <div class="product-meta">
                            ${p.variant ? `<span style="color:#3b82f6;">${p.variant}</span> | ` : ''}
                            <span>SKU: ${p.sku}</span>
                        </div>
                    </div>
                    <div class="product-right">
                        <div class="product-price">
                            ${originalPriceHtml}
                            ${p.line_total}
                        </div>
                        <div class="product-qty">${displayQty}${displayUnit ? ' ' + displayUnit : ''}</div>
                    </div>
                </div>`;
            });

            const renderAddr = (a, type = 'shipping') => {
                if (!a || !a.first_name) return `<div class="clean-addr-item"><span class="addr-val-text"><i>${'{{ trans('order::orders.no_information') }}'}</i></span></div>`;
                
                let mainLine = '';
                let subLine = '';

                if (type === 'billing' && a.company_name) {
                    mainLine = `<div class="clean-addr-item">
                        <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span class="addr-val-main"><small class="addr-label">Firma Adı:</small> ${a.company_name}</span>
                    </div>`;
                    if (a.tax_office) {
                        subLine += `<div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span class="addr-val-sub"><small class="addr-label">Vergi Dairesi:</small> ${a.tax_office}</span>
                        </div>`;
                    }
                    if (a.tax_number) {
                        subLine += `<div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm3 0c1.333 0 4 1 4 3v1H5v-1c0-2 2.667-3 4-3z"></path></svg>
                            <span class="addr-val-sub"><small class="addr-label">Vergi No:</small> VKN: ${a.tax_number}</span>
                        </div>`;
                    }
                } else {
                    mainLine = `<div class="clean-addr-item">
                        <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7c0 2.209-1.791 4-4 4s-4-1.791-4-4 1.791-4 4-4 4 1.791 4 4z"></path><path d="M12 14c-3.866 0-7 3.134-7 7h14c0-3.866-3.134-7-7-7z"></path></svg>
                        <span class="addr-val-main"><small class="addr-label">Ad Soyad:</small> ${a.first_name} ${a.last_name}</span>
                    </div>`;
                }

                return `<div class="address-details-clean">
                    ${mainLine}
                    ${subLine}
                    <div class="clean-addr-item">
                        <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z"></path><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="addr-val-sub"><small class="addr-label">Adres:</small> ${a.address_1}${a.address_2 ? ' ' + a.address_2 : ''}</span>
                    </div>
                    <div class="clean-addr-item">
                        <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V7.618a1 1 0 011.447-.894L9 9m0 11l6-3m-6 3V9m6 8l4.553 2.276A1 1 0 0021 18.382V9.618a1 1 0 00-.553-.894L15 6m0 11V6m0 0L9 9"></path></svg>
                        <span class="addr-val-text"><small class="addr-label">Bölge:</small> ${a.city} ${a.state_name ? '• ' + a.state_name : ''} • Türkiye</span>
                    </div>
                    ${a.phone ? `
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 5C3 3.89543 3.89543 3 5 3H8.27924C8.70967 3 9.09181 3.27543 9.22792 3.68377L10.7257 8.17721C10.8831 8.64932 10.6694 9.16531 10.2243 9.38787L7.96701 10.5165C9.06925 12.9612 11.0388 14.9308 13.4835 16.033L14.6121 13.7757C14.8347 13.3306 15.3507 13.1169 15.8228 13.2743L20.3162 14.7721C20.7246 14.9082 21 15.2903 21 15.7208V19C21 20.1046 20.1046 21 19 21H18C9.71573 21 3 14.2843 3 6V5Z"></path></svg>
                            <span class="addr-val-text"><small class="addr-label">Telefon:</small> ${a.phone}</span>
                        </div>
                    ` : ''}
                    ${type === 'billing' && data.customer_email ? `
                        <div class="clean-addr-item">
                            <svg class="addr-svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span class="addr-val-text"><small class="addr-label">E-Posta:</small> ${data.customer_email}</span>
                        </div>
                    ` : ''}
                </div>`;
            };

            const isSameAddress = (s, b) => {
                const checkFields = ['first_name', 'last_name', 'address_1', 'city', 'state_name'];
                const basicMatch = checkFields.every(f => (s[f] || '').toLowerCase() === (b[f] || '').toLowerCase());
                // Also check if billing has company info which makes it 'distinct' for tax purposes even if address is same
                const hasCompanyInfo = !!(b.company_name || b.tax_number || b.tax_office);
                return basicMatch && !hasCompanyInfo;
            };

            const addressesHtml = `
                <div class="drawer-section">
                    <div class="drawer-section-title"><i class="fa fa-truck"></i> ` + '{{ trans('order::orders.shipping_address') }}' + `</div>
                    ${renderAddr(shipping, 'shipping')}
                </div>
                ${!isSameAddress(shipping, billing) ? `
                    <div class="drawer-section">
                        <div class="drawer-section-title"><i class="fa fa-file-text"></i> ` + '{{ trans('order::orders.billing_address') }}' + `</div>
                        ${renderAddr(billing, 'billing')}
                    </div>
                ` : ''}
            `;

            const content = `
                ${addressesHtml}

                <div class="drawer-section">
                    <div class="drawer-section-title"><i class="fa fa-shopping-basket"></i> ` + '{{ trans('order::orders.items_ordered') }}' + ` (${products.length} ` + '{{ trans('order::orders.product') }}' + `)</div>
                    <div class="product-list">${productsHtml}</div>
                </div>

                <div class="drawer-section" style="margin-bottom:40px;">
                    <div class="drawer-section-title"><i class="fa fa-credit-card"></i> ` + '{{ trans('order::orders.payment_method') }}' + ` & ` + '{{ trans('order::orders.total') }}' + `</div>
                    <div class="payment-info">
                        <i class="fa fa-check-circle" style="color:#10b981;"></i> 
                        ` + '{{ trans('order::orders.payment_method') }}' + `: <span style="color:#0f172a; margin-left:5px;">${data.payment_method || '---'}</span>
                    </div>
                    <div class="summary-row"><span>` + '{{ trans('order::orders.subtotal') }}' + `</span><span>${totals.sub_total}</span></div>
                    
                    ${totals.shipping_method ? `
                        <div class="summary-row">
                            <span>` + '{{ trans('order::orders.shipping_method') }}' + ` (${totals.shipping_method})</span>
                            <span>${totals.shipping_cost === '0,00 TL' || totals.shipping_cost === 'ücretsiz' ? '{{ trans('storefront::checkout.free') }}' : totals.shipping_cost}</span>
                        </div>
                    ` : ''}

                    ${totals.cod_fee ? `
                        <div class="summary-row">
                            <span>` + '{{ trans('storefront::checkout.cod_fee') }}' + `</span>
                            <span>${totals.cod_fee}</span>
                        </div>
                    ` : ''}

                    ${totals.taxes && totals.taxes.length > 0 ? 
                        totals.taxes.map(t => `<div class="summary-row"><span>${t.name}</span><span>${t.amount}</span></div>`).join('') 
                    : ''}

                    ${(totals.discount && totals.discount !== '0,00 TL' && totals.discount !== '₺0,00') ? `
                        <div class="summary-row" style="color:#ef4444;">
                            <span>` + '{{ trans('order::orders.coupon') }}' + ` ${data.coupon_code ? `(${data.coupon_code})` : ''}</span>
                            <span>-${totals.discount}</span>
                        </div>
                    ` : ''}

                    <div class="summary-row total"><span>` + '{{ trans('order::orders.total') }}' + `</span><span>${totals.total || '0'}</span></div>
                </div>
            `;
            
            $('#order-drawer-title').html('<i class="fa fa-file-text-o"></i> ' + '{{ trans('order::orders.order_information') }}' + ': <span style="color:#3b82f6; margin-left:5px;">' + d.order_no_raw + '</span>');
            $('#order-drawer-content').html(content);
            $('#order-drawer-backdrop, #order-drawer').addClass('open');
        }

        const closeOrderDrawer = () => {
            $('#order-drawer-backdrop, #order-drawer').removeClass('open');
        };
        $(document).on('click', '.drawer-close-trigger, #order-drawer-backdrop', closeOrderDrawer);

        const tableInstance = new DataTable('#orders-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'order_no', width: '10%' },
                { data: 'customer_name', orderable: false, searchable: false, width: '13%' },
                { data: 'customer_email', width: '15%' },
                { data: 'payment_method', name: 'payment_method', width: '10%' },
                { data: 'status', width: '10%' },
                { data: 'total', width: '10%' },
                { data: 'order_count', orderable: false, searchable: false, width: '9%' },
                { data: 'created', name: 'created_at', width: '15%' },
                { data: 'actions', orderable: false, searchable: false, width: '5%' },
                { data: 'child_data', visible: false },
            ],
            order: [[8, 'desc']]
        });

        // Open Drawer on ID Click
        $('#orders-table .table tbody').on('click', '.order-no-wrapper', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            const tr = $(this).closest('tr');
            let data = tableInstance.api.row(tr).data();
            
            // Extract raw ID from span if needed
            const orderNoRaw = $(this).find('span:last-child').text();
            data.order_no_raw = orderNoRaw;
            
            renderDrawer(data);
        });

        // Also allow clicking the icon specifically
        $('#orders-table .table tbody').on('click', '.details-control', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.order-no-wrapper').trigger('click');
        });

        // Prevent row navigation when clicking on interactive cells (ID and Status)
        $('#orders-table .table tbody').on('click', 'td:nth-child(2), td:nth-child(6)', function(e) {
            e.stopPropagation();
        });

        $(document).on('click', '.order-status-dropdown, .status-select-styled', function(e) {
            e.stopPropagation();
        });

        // Status change
        $(document).on('change', '.status-select-styled', function(e) {
            e.stopPropagation();
            const select = $(this);
            const orderId = select.data('id');
            const newStatus = select.val();

            select.prop('disabled', true).css('opacity', '0.5');

            axios.put(`${FleetCart.baseUrl}/admin/orders/${orderId}/status`, {
                status: newStatus
            }).then(({ data }) => {
                const statusMessages = {
                    'completed': '<i class="fa fa-check-circle" style="margin-right: 8px;"></i> Sipariş Başarıyla Tamamlandı!',
                    'shipped': '<i class="fa fa-truck" style="margin-right: 8px;"></i> Sipariş Kargoya Verildi!',
                    'on_the_way': '<i class="fa fa-road" style="margin-right: 8px;"></i> Sipariş Şu An Yolda!',
                    'out_for_delivery': '<i class="fa fa-archive" style="margin-right: 8px;"></i> Sipariş Dağıtıma Çıktı!',
                    'canceled': '<i class="fa fa-times-circle" style="margin-right: 8px;"></i> Sipariş İptal Edildi.',
                    'refunded': '<i class="fa fa-reply" style="margin-right: 8px;"></i> Sipariş İadesi Yapıldı.',
                    'pending_payment': '<i class="fa fa-clock-o" style="margin-right: 8px;"></i> Ödeme Bekleniyor Olarak Güncellendi.',
                    'pending': '<i class="fa fa-hourglass-start" style="margin-right: 8px;"></i> Sipariş Beklemede.'
                };

                const msg = statusMessages[newStatus] || data.message;
                
                if (typeof window.success === 'function') {
                    window.success(msg);
                } else {
                    console.log('Success notification:', msg);
                }
                
                // Update class
                select.removeClass('badge-success badge-danger badge-info badge-warning badge-pending');
                if (newStatus === 'completed') select.addClass('badge-success');
                else if (newStatus === 'canceled' || newStatus === 'refunded') select.addClass('badge-danger');
                else if (newStatus === 'pending_payment') select.addClass('badge-warning');
                else if (newStatus === 'shipped' || newStatus === 'on_the_way' || newStatus === 'out_for_delivery') select.addClass('badge-info');
                else select.addClass('badge-pending');
            }).catch((err) => {
                const msg = err.response && err.response.data && err.response.data.message 
                    ? err.response.data.message 
                    : 'Bir hata oluştu.';
                
                if (typeof window.error === 'function') {
                    window.error(msg);
                } else {
                    alert(msg);
                }
            }).finally(() => {
                select.prop('disabled', false).css('opacity', '1');
            });
        });
    </script>
@endpush

@push('notifications')
    <div id="order-drawer-backdrop"></div>
    <div id="order-drawer">
        <div class="drawer-header">
            <h4 id="order-drawer-title">{{ trans('order::orders.order_information') }}</h4>
            <button class="close-btn drawer-close-trigger">&times;</button>
        </div>
        <div class="drawer-body" id="order-drawer-content"></div>
        <div class="drawer-footer">
            <button class="btn btn-default drawer-close-trigger">{{ trans('admin::admin.buttons.close') }}</button>
        </div>
    </div>
@endpush
