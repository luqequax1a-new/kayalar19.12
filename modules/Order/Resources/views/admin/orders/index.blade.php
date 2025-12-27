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
        .details-control svg {
            transition: transform 0.2s;
        }
        .child-row-container {
            padding: 20px 40px;
            background: #fafafb;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            gap: 40px;
        }
        .details-control svg {
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        tr.shown .details-control svg {
            transform: rotate(90deg);
        }
        .child-col { flex: 1; }
        .child-col-title {
            font-size: 11px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .child-info-box {
            font-size: 13px;
            line-height: 1.6;
            color: #475569;
        }
        .child-product-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #eef2f6;
        }
        .child-product-item:last-child { border: none; }
        
        .child-product-img {
            width: 90px;
            height: 90px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            overflow: hidden !important;
            flex-shrink: 0;
            background: #fff;
            padding: 0 !important;
        }
        .child-product-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        /* Status select styling - Minimal & Modern */
        .status-select-styled {
            height: 28px !important;
            padding: 0 24px 0 10px !important;
            border-radius: 4px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #fff !important;
            border: none !important;
            cursor: pointer !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            transition: background 0.2s !important;
            width: auto !important;
            min-width: 120px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            transform: none !important; /* Force no movement */
        }
        .status-select-styled:hover { 
            filter: brightness(0.95);
            transform: none !important;
        }
        .status-select-styled:focus {
            outline: none;
        }
        
        .badge-success { background-color: #10b981 !important; }
        .badge-danger { background-color: #ef4444 !important; }
        .badge-info { background-color: #0ea5e9 !important; }
        .badge-warning { background-color: #f59e0b !important; }
        .badge-pending { background-color: #64748b !important; }
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

        function format(d) {
            const data = d.child_data || {};
            const shipping = data.shipping || {};
            const billing = data.billing || {};
            const products = data.products || [];

            let productsHtml = '';
            products.forEach(p => {
                const img = p.image ? `<img src="${p.image}" alt="thumb">` : '<i class="fa fa-picture-o" style="font-size:24px; color:#cbd5e1;"></i>';
                productsHtml += `<div class="child-product-item">
                    <div class="thumbnail-holder child-product-img">
                        ${img}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight:600; color:#1e293b; font-size:13px; margin-bottom:2px;">${p.name}</div>
                        <div style="color:#64748b; font-size:12px;">
                            ${p.variant ? `<span>Varyant: ${p.variant}</span> | ` : ''}
                            <span>SKU: ${p.sku}</span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight:700; color:#0f172a;">${p.line_total}</div>
                        <div style="font-size:12px; color:#94a3b8;">${p.qty} ${p.unit || 'Adet'}</div>
                    </div>
                </div>`;
            });

            const renderAddr = (a, type = 'shipping') => {
                if (!a || !a.first_name) return '&mdash;';
                let html = '';
                
                if (type === 'billing') {
                    if (a.company_name) html += `<div style="margin-bottom:2px;"><strong>Firma Adı:</strong> ${a.company_name}</div>`;
                    if (a.tax_number) html += `<div style="margin-bottom:2px;"><strong>Vergi No:</strong> ${a.tax_number}</div>`;
                    if (a.tax_office) html += `<div style="margin-bottom:2px;"><strong>Vergi Dairesi:</strong> ${a.tax_office}</div>`;
                    if (a.billing_email) html += `<div style="margin-bottom:2px;"><strong>Email:</strong> ${a.billing_email}</div>`;
                }

                let part2 = a.address_2 ? `${a.address_2}<br>` : '';
                html += `<strong>${a.first_name} ${a.last_name}</strong><br>
                        ${a.address_1}<br>${part2}
                        ${a.city} ${a.state_name ? '/ ' + a.state_name : ''}<br>
                        ${a.phone ? a.phone : ''}`;
                return html;
            };

            return `<div class="child-row-container">
                <div class="child-col">
                    <div class="child-col-title"><i class="fa fa-truck"></i> Teslimat</div>
                    <div class="child-info-box">${renderAddr(shipping, 'shipping')}</div>
                </div>
                <div class="child-col">
                    <div class="child-col-title"><i class="fa fa-file-text-o"></i> Fatura</div>
                    <div class="child-info-box">${renderAddr(billing, 'billing')}</div>
                </div>
                <div class="child-col" style="flex: 2.2;">
                    <div class="child-col-title"><i class="fa fa-shopping-basket"></i> Sipariş İçeriği (${products.length})</div>
                    <div style="max-height: 250px; overflow-y: auto; padding-right: 10px;">
                        ${productsHtml}
                    </div>
                </div>
            </div>`;
        }

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

        // Toggle Expand
        $('#orders-table .table tbody').on('click', '.details-control', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            const tr = $(this).closest('tr');
            const row = tableInstance.api.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
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
                success(data);
                // Update class
                select.removeClass('badge-success badge-danger badge-info badge-warning badge-pending');
                if (newStatus === 'completed') select.addClass('badge-success');
                else if (newStatus === 'canceled' || newStatus === 'refunded') select.addClass('badge-danger');
                else if (newStatus === 'shipped' || newStatus === 'on_the_way') select.addClass('badge-info');
                else if (newStatus === 'pending_payment') select.addClass('badge-warning');
                else select.addClass('badge-pending');
            }).catch((error) => {
                if (error.response && error.response.data && error.response.data.message) {
                    error(error.response.data.message);
                } else {
                    error('Bir hata oluştu.');
                }
            }).finally(() => {
                select.prop('disabled', false).css('opacity', '1');
            });
        });
    </script>
@endpush
