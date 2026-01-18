<div class="tracking-stats-premium-wrapper">
    <div class="row">
        {{-- Kargo Bilgileri Kartı --}}
        <div class="col-md-4">
            <div class="admin-card-modern">
                <div class="card-header-modern">
                    <div class="header-icon">
                        <i class="fa fa-truck"></i>
                    </div>
                    <div class="header-info">
                        <h5>Sipariş Takibi</h5>
                        <p>Kargo ve teslimat verileri</p>
                    </div>
                </div>
                
                <div class="card-body-modern">
                    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modern-form-row">
                            <label>{{ trans('order::orders.shipping_carrier_name') }}</label>
                            <div class="input-with-icon">
                                <i class="fa fa-ship"></i>
                                <input type="text" name="shipping_carrier_name" value="{{ old('shipping_carrier_name', $order->shipping_carrier_name) }}" placeholder="Örn: Aras Kargo">
                            </div>
                        </div>

                        <div class="modern-form-row">
                            <label>{{ trans('order::orders.shipping_tracking_number') }}</label>
                            <div class="input-with-icon">
                                <i class="fa fa-barcode"></i>
                                <input type="text" name="shipping_tracking_number" value="{{ old('shipping_tracking_number', $order->shipping_tracking_number) }}" placeholder="Takip No">
                            </div>
                        </div>

                        <div class="modern-form-row">
                            <label>{{ trans('order::orders.shipping_tracking_url') }}</label>
                            <div class="input-with-icon">
                                <i class="fa fa-link"></i>
                                <input type="url" name="shipping_tracking_url" value="{{ old('shipping_tracking_url', $order->shipping_tracking_url) }}" placeholder="https://...">
                            </div>
                        </div>

                        <button type="submit" class="btn-update-tracking">
                            <i class="las la-save"></i> Bilgileri Güncelle
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Müşteri Geçmişi Kartı --}}
        @if($order->customer_id)
        <div class="col-md-8">
            <div class="admin-card-modern">
                <div class="card-header-modern">
                    <div class="header-icon history">
                        <i class="fa fa-history"></i>
                    </div>
                    <div class="header-info">
                        <h5>Müşteri Geçmişi</h5>
                        <p>Önceki siparişler ve istatistikler</p>
                    </div>
                </div>

                <div class="customer-stats-bar">
                    <div class="stat-item">
                        <span class="s-label">Toplam Sipariş</span>
                        <span class="s-value">{{ $totalOrders }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="s-label">Toplam Harcama</span>
                        <span class="s-value">{{ \Modules\Support\Money::inDefaultCurrency($totalSpend)->format() }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="s-label">Ortalama Sepet</span>
                        <span class="s-value">
                            {{ $totalOrders > 0 ? \Modules\Support\Money::inDefaultCurrency($totalSpend / $totalOrders)->format() : '-' }}
                        </span>
                    </div>
                </div>

                <div class="history-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Kod</th>
                                <th>Tarih</th>
                                <th>Tutar</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customerOrders as $cOrder)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $cOrder->id) }}" class="order-code">
                                            {{ $cOrder->order_number ?: '#' . $cOrder->id }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="order-date">{{ $cOrder->created_at->translatedFormat('d M Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="order-amount">{{ $cOrder->total->format() }}</span>
                                    </td>
                                    <td>
                                        <span class="admin-status-pill {{ $cOrder->status }}">
                                            {{ trans("order::statuses.{$cOrder->status}") }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #94a3b8; padding: 40px;">
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
</div>
