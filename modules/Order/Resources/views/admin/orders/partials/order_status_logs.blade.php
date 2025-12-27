<div class="status-logs-wrapper">
    <h4 class="section-title">
        <i class="fa fa-history"></i>
        {{ trans('order::orders.order_status') }} Log
    </h4>

    @php
        $statusLogs = $order->statusLogs->sortByDesc('id')->take(50);
    @endphp

    @if ($statusLogs->isEmpty())
        <div class="empty-state">
            <i class="fa fa-info-circle"></i>
            <p>Log kaydı yok.</p>
        </div>
    @else
        <div class="status-timeline">
            @foreach ($statusLogs as $log)
                <div class="timeline-entry">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <div class="timeline-date">
                                <i class="fa fa-clock-o"></i>
                                {{ optional($log->created_at)->translatedFormat('d M Y, H:i') }}
                            </div>
                            <div class="timeline-source">
                                <i class="fa fa-tag"></i>
                                {{ $log->source }}
                            </div>
                        </div>
                        
                        <div class="timeline-status-change">
                            @if($log->from_status)
                                <span class="status-from">{{ trans('order::statuses.' . $log->from_status) }}</span>
                                <i class="fa fa-arrow-right status-arrow"></i>
                            @endif
                            <span class="status-to">{{ $log->to_status ? trans('order::statuses.' . $log->to_status) : '-' }}</span>
                        </div>

                        @php
                            $shipmentId = is_array($log->context) ? ($log->context['shipment_id'] ?? null) : null;
                            $statusRaw = is_array($log->context) ? ($log->context['status_raw'] ?? null) : null;
                        @endphp

                        @if ($shipmentId || $statusRaw)
                            <div class="timeline-details">
                                @if ($shipmentId)
                                    <span class="detail-item">
                                        <i class="fa fa-cube"></i>
                                        Shipment: <strong>{{ $shipmentId }}</strong>
                                    </span>
                                @endif
                                @if ($statusRaw)
                                    <span class="detail-item">
                                        <i class="fa fa-code"></i>
                                        Raw: <strong>{{ $statusRaw }}</strong>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
