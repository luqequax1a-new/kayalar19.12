<div class="dashboard-panel dashboard-top-customers">
    <div class="grid-header">
        <h5>Top 10 Müşteri</h5>
    </div>

    <div class="clearfix"></div>

    <div class="table-responsive anchor-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Müşteri</th>
                    <th class="text-right">Sipariş</th>
                    <th class="text-right">Toplam Tutar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($topCustomers as $customer)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.edit', $customer->id) }}">
                                {{ trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')) ?: ($customer->email ?: '—') }}
                            </a>
                            @if (!empty($customer->email))
                                <div class="text-muted small">{{ $customer->email }}</div>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.users.edit', $customer->id) }}">
                                {{ (int) $customer->orders_count }}
                            </a>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.users.edit', $customer->id) }}">
                                {{ \Modules\Support\Money::inDefaultCurrency((float) $customer->total_spent)->format() }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty" colspan="3">{{ trans('admin::dashboard.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
