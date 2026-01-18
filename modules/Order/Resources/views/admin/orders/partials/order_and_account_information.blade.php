<div class="order-account-premium-wrapper">
    <div class="row">
        {{-- Sipariş Bilgileri Kartı --}}
        <div class="col-md-6">
            <div class="info-card-modern">
                <div class="card-head">
                    <div class="c-icon blue">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                    <div class="c-title">
                        <h5>{{ trans('order::orders.order_information') }}</h5>
                        <p>Genel sipariş ve ödeme detayları</p>
                    </div>
                    <div class="c-actions">
                        <a href="{{ route('admin.orders.print.show', $order) }}" class="btn-mini" target="_blank" data-toggle="tooltip" title="{{ trans('order::orders.print') }}">
                            <i class="fa fa-print"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.orders.email.store', $order) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-mini" data-toggle="tooltip" title="{{ trans('order::orders.send_email') }}" data-loading>
                                <i class="fa fa-envelope"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body-content">
                    <div class="data-list">
                        <div class="row-item">
                            <span class="label-text">{{ trans('order::orders.order_id') }}</span>
                            <span class="value-text mono">#{{ $order->order_number ?: $order->id }}</span>
                        </div>

                        <div class="row-item">
                            <span class="label-text">{{ trans('order::orders.order_date') }}</span>
                            <span class="value-text">{{ $order->created_at->translatedFormat('d F Y, H:i') }}</span>
                        </div>

                        <div class="row-item">
                            <span class="label-text">{{ trans('order::orders.order_status') }}</span>
                            <div class="status-dropdown-box">
                                <select id="order-status" class="form-control" data-id="{{ $order->id }}">
                                    @foreach (trans('order::statuses') as $name => $label)
                                        <option value="{{ $name }}" {{ $order->status === $name ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row-item">
                            <span class="label-text">Kargo Yöntemi</span>
                            <span class="value-text">{{ $order->shipping_method ?: 'Belirtilmedi' }}</span>
                        </div>

                        <div class="row-item">
                            <span class="label-text">Ödeme Yöntemi</span>
                            <span class="value-text">{{ $order->payment_method ?: 'Belirtilmedi' }}</span>
                        </div>

                        @if($order->note)
                        <div class="row-item" style="border-top: 1px dashed #f1f5f9; padding-top: 15px; margin-top: 5px;">
                            <span class="label-text">Sipariş Notu</span>
                            <span class="value-text" style="color: #64748b; font-weight: 500;">{{ $order->note }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Hesap Bilgileri Kartı --}}
        <div class="col-md-6">
            <div class="info-card-modern">
                <div class="card-head">
                    <div class="c-icon purple">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="c-title">
                        <h5>{{ trans('order::orders.account_information') }}</h5>
                        <p>Müşteri ve iletişim bilgileri</p>
                    </div>
                </div>

                <div class="card-body-content">
                    <div class="data-list">
                        <div class="row-item">
                            <span class="label-text">{{ trans('order::orders.customer_name') }}</span>
                            <span class="value-text">{{ $order->customer_full_name }}</span>
                        </div>

                        <div class="row-item">
                            <span class="label-text">{{ trans('order::orders.customer_email') }}</span>
                            <span class="value-text">{{ $order->customer_email }}</span>
                        </div>

                        <div class="row-item">
                            <span class="label-text">{{ trans('order::orders.customer_phone') }}</span>
                            <span class="value-text" style="font-family: monospace;">{{ $order->shipping_phone ?: ($order->billing_phone ?: '-') }}</span>
                        </div>

                        <div class="row-item">
                            <span class="label-text">Müşteri Grubu</span>
                            <span class="value-text">
                                <span class="admin-status-pill {{ is_null($order->customer_id) ? 'guest' : 'completed' }}">
                                    {{ is_null($order->customer_id) ? 'Misafir' : 'Kayıtlı Müşteri' }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
