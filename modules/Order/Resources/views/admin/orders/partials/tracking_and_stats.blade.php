<div class="row tracking-stats-row">
    {{-- Order Tracking Section --}}
    <div class="col-md-4">
        <div class="tracking-card-minimal">
            <h5 class="minimal-section-title">
                <i class="fa fa-truck"></i> {{ trans('order::orders.order_tracking') }}
            </h5>
            
            <div class="tracking-form-minimal">
                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group-ultra">
                        <label>{{ trans('order::orders.shipping_carrier_name') }}</label>
                        <input
                            type="text"
                            name="shipping_carrier_name"
                            class="form-control-minimal"
                            value="{{ old('shipping_carrier_name', $order->shipping_carrier_name) }}"
                            placeholder="Kargo Firması"
                        >
                    </div>

                    <div class="form-group-ultra">
                        <label>{{ trans('order::orders.shipping_tracking_number') }}</label>
                        <input
                            type="text"
                            name="shipping_tracking_number"
                            class="form-control-minimal"
                            value="{{ old('shipping_tracking_number', $order->shipping_tracking_number) }}"
                            placeholder="Takip Numarası"
                        >
                    </div>

                    <div class="form-group-ultra">
                        <label>{{ trans('order::orders.shipping_tracking_url') }}</label>
                        <input
                            type="url"
                            name="shipping_tracking_url"
                            class="form-control-minimal"
                            value="{{ old('shipping_tracking_url', $order->shipping_tracking_url) }}"
                            placeholder="Takip Linki"
                        >
                    </div>

                    <button type="submit" class="btn btn-save-minimal">
                         {{ trans('admin::admin.buttons.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Customer Statistics Section --}}
    @if($order->customer_id)
    <div class="col-md-8">
        <div class="tracking-card-minimal stats-card-minimal">
            <h5 class="minimal-section-title">
                <i class="fa fa-user-o"></i> Müşteri Geçmişi
            </h5>

            <div class="stats-simple-grid">
                <div class="stat-simple">
                    <span class="stat-simple-label">TOPLAM SİPARİŞ</span>
                    <span class="stat-simple-value">{{ $totalOrders }}</span>
                </div>
                <div class="stat-simple">
                    <span class="stat-simple-label">TOPLAM HARCAMA</span>
                    <span class="stat-simple-value">{{ \Modules\Support\Money::inDefaultCurrency($totalSpend)->format() }}</span>
                </div>
                <div class="stat-simple">
                    <span class="stat-simple-label">ORTALAMA</span>
                    <span class="stat-simple-value">
                        {{ $totalOrders > 0 ? \Modules\Support\Money::inDefaultCurrency($totalSpend / $totalOrders)->format() : \Modules\Support\Money::inDefaultCurrency(0)->format() }}
                    </span>
                </div>
            </div>

            <div class="customer-history-table-minimal-wrapper">
                <table class="customer-history-table-minimal">
                    <thead>
                        <tr>
                            <th>KOD</th>
                            <th>TARİH</th>
                            <th class="text-right">TUTAR</th>
                            <th class="text-center">DURUM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerOrders as $cOrder)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $cOrder->id) }}" class="order-link-minimal">
                                        {{ $cOrder->order_number ?: '#' . $cOrder->id }}
                                    </a>
                                </td>
                                <td>
                                    <div class="order-date-minimal">{{ $cOrder->created_at->translatedFormat('d M Y') }}</div>
                                </td>
                                <td class="text-right">
                                    <div class="order-total-minimal">{{ $cOrder->total->format() }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-dot-text {{ $cOrder->status }}">
                                        {{ trans("order::statuses.{$cOrder->status}") }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted" style="padding: 30px;">
                                    Başka sipariş bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
