<div class="box">
    <div class="box-header">
        <h5>{{ trans('order::orders.order_status') }} Log</h5>
    </div>

    <div class="box-body">
        @php
            $statusLogs = \Modules\Order\Entities\OrderStatusLog::query()
                ->where('order_id', $order->id)
                ->orderByDesc('id')
                ->limit(50)
                ->get();
        @endphp

        @if ($statusLogs->isEmpty())
            <p class="text-muted">Log kaydı yok.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Kaynak</th>
                            <th>Önce</th>
                            <th>Sonra</th>
                            <th>Detay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statusLogs as $log)
                            <tr>
                                <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $log->source }}</td>
                                <td>{{ $log->from_status ? trans('order::statuses.' . $log->from_status) : '-' }}</td>
                                <td>{{ $log->to_status ? trans('order::statuses.' . $log->to_status) : '-' }}</td>
                                <td>
                                    @php
                                        $shipmentId = is_array($log->context) ? ($log->context['shipment_id'] ?? null) : null;
                                        $statusRaw = is_array($log->context) ? ($log->context['status_raw'] ?? null) : null;
                                    @endphp
                                    @if ($shipmentId || $statusRaw)
                                        <small>
                                            @if ($shipmentId)
                                                shipment: {{ $shipmentId }}
                                            @endif
                                            @if ($shipmentId && $statusRaw)
                                                <br>
                                            @endif
                                            @if ($statusRaw)
                                                raw: {{ $statusRaw }}
                                            @endif
                                        </small>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
