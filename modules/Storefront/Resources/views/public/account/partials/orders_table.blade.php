<div class="orders-list-modern">
    @foreach ($orders as $order)
        <div class="order-entry-card">
            <div class="order-entry-header">
                <div class="order-id-group">
                    <span class="label">{{ trans('storefront::account.orders.order_id') }}</span>
                    <span class="value">#{{ $order->displayOrderNumber() }}</span>
                </div>
                <div class="order-status-group">
                    <span class="status-badge {{ $order->status }} {{ order_status_badge_class($order->status) }}">
                        {{ $order->status() }}
                    </span>
                </div>
            </div>

            <div class="order-entry-body">
                <div class="info-grid">
                    <div class="info-col">
                        <span class="info-label">{{ trans('storefront::account.date') }}</span>
                        <span class="info-value">{{ $order->created_at->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="info-col">
                        <span class="info-label">{{ trans('storefront::account.view_order.payment_method') }}</span>
                        <span class="info-value">{{ $order->payment_method }}</span>
                    </div>
                    <div class="info-col">
                        <span class="info-label">{{ trans('storefront::account.orders.total') }}</span>
                        <span class="info-value highlight">{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                    </div>
                </div>
                
                <div class="order-entry-actions">
                    @if(!empty($order->tracking_reference))
                        @if(filter_var($order->tracking_reference, FILTER_VALIDATE_URL))
                            <a href="{{ $order->tracking_reference }}" target="_blank" class="btn-order btn-track" title="{{ trans('storefront::account.orders.track_order') }}">
                                <i class="las la-truck"></i>
                                <span>{{ trans('storefront::account.orders.tracking') }}</span>
                            </a>
                        @else
                            <div class="tracking-copy-group" x-data="{ copied: false }">
                                <button @click="navigator.clipboard.writeText('{{ $order->tracking_reference }}'); copied = true; setTimeout(() => copied = false, 2000)" class="btn-order btn-track">
                                    <i class="las la-truck"></i>
                                    <span x-text="copied ? '{{ trans('storefront::account.view_order.copied_to_clipboard') }}' : '{{ $order->tracking_reference }}'"></span>
                                </button>
                            </div>
                        @endif
                    @endif

                    <a href="{{ route('account.orders.show', $order) }}" class="btn-order btn-details">
                        <span>{{ trans('storefront::account.orders.view') }}</span>
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
