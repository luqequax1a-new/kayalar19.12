<div class="dashboard-panel dashboard-latest-customers">
    <div class="grid-header">
        <h5>En Son Kayıt Olan 10 Müşteri</h5>
    </div>

    <div class="clearfix"></div>

    <div class="table-responsive anchor-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Müşteri</th>
                    <th>Email</th>
                    <th class="text-right">Kayıt Tarihi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($latestCustomers as $customer)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.edit', $customer->id) }}">
                                {{ $customer->first_name }} {{ $customer->last_name }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $customer->id) }}">
                                {{ $customer->email }}
                            </a>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.users.edit', $customer->id) }}">
                                {{ $customer->created_at->translatedFormat('j F Y') }}
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
