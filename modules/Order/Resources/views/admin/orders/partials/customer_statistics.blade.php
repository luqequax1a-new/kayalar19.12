@if($order->customer_id)
<div class="customer-stats-wrapper">
    <h4 class="section-title">Müşteri İstatistikleri</h4>
    
    {{-- Stats Cards --}}
    <div class="stats-cards-row">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Toplam Sipariş</div>
                <div class="stat-value">{{ $totalOrders }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa fa-money"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Toplam Harcama</div>
                <div class="stat-value">{{ \Modules\Support\Money::inDefaultCurrency($totalSpend)->format() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa fa-calculator"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Ortalama Sipariş</div>
                <div class="stat-value">{{ $totalOrders > 0 ? \Modules\Support\Money::inDefaultCurrency($totalSpend / $totalOrders)->format() : \Modules\Support\Money::inDefaultCurrency(0)->format() }}</div>
            </div>
        </div>
    </div>

    {{-- Recent Orders Table --}}
    @if($customerOrders->isNotEmpty())
    <div class="recent-orders-section">
        <h5 class="subsection-title">Son Siparişler</h5>
        <div class="table-responsive">
            <table class="table recent-orders-table">
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Tarih</th>
                        <th>Durum</th>
                        <th class="text-right">Tutar</th>
                        <th class="text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customerOrders as $customerOrder)
                    <tr>
                        <td>
                            <span class="order-number">#{{ $customerOrder->order_number }}</span>
                        </td>
                        <td>
                            <span class="order-date">{{ $customerOrder->created_at->translatedFormat('d M Y') }}</span>
                        </td>
                        <td>
                            @php
                                $statusClass = strtolower($customerOrder->status);
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $customerOrder->status() }}
                            </span>
                        </td>
                        <td class="text-right">
                            <span class="order-total">{{ $customerOrder->total->format() }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.orders.show', $customerOrder->id) }}" class="btn-view-order" title="Görüntüle">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endif
