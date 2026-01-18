@extends('storefront::public.layout')

@section('content')
    <section class="order-complete-wrap">
        <div class="container">
            <div class="order-complete-wrap-inner">
                <div class="order-complete">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>

                    <h2>{{ trans('storefront::order_complete.order_placed') }}</h2>
                    <span>{!! trans('storefront::order_complete.your_order_has_been_placed', ['id' => $order->displayOrderNumber()]) !!}</span>
                </div>

                @if ($order && $order->getRawOriginal('payment_method') === 'bank_transfer')
                    <div class="bank-transfer-info">
                        <div class="bank-transfer-info__title">
                            {{ setting('bank_transfer_label') ?: 'Havale / EFT' }}
                        </div>

                        <div class="bank-transfer-info__amount">
                            <span class="bank-transfer-info__amount-label">Sipariş Tutarı</span>
                            <span class="bank-transfer-info__amount-value">
                                {{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                            </span>
                        </div>

                        @if (setting('bank_transfer_instructions'))
                            <div class="bank-transfer-info__instructions">
                                @php
                                    $btRaw = (string) setting('bank_transfer_instructions');
                                    $btNormalized = str_ireplace([
                                        "<br>",
                                        "<br/>",
                                        "<br />",
                                        "</p>",
                                        "</div>",
                                        "</li>",
                                        "</tr>",
                                    ], "\n", $btRaw);
                                    $btText = trim((string) preg_replace("/\n{2,}/", "\n", strip_tags($btNormalized)));
                                    $btLines = array_values(array_filter(array_map('trim', preg_split("/\r\n|\r|\n/", $btText)), fn ($l) => $l !== ''));
                                    $btRows = [];
                                    foreach ($btLines as $line) {
                                        if (str_contains($line, ':')) {
                                            [$k, $v] = array_map('trim', explode(':', $line, 2));
                                            $btRows[] = ['k' => $k, 'v' => $v];
                                        } else {
                                            $btRows[] = ['k' => '', 'v' => $line];
                                        }
                                    }
                                @endphp

                                @if (! empty($btRows))
                                    <div class="bank-transfer-kv">
                                        @foreach ($btRows as $row)
                                            <div class="bank-transfer-kv__row {{ $row['k'] === '' ? 'bank-transfer-kv__row--no-key' : '' }}">
                                                @if ($row['k'] !== '')
                                                    <div class="bank-transfer-kv__key">{{ $row['k'] }}</div>
                                                @endif
                                                <div class="bank-transfer-kv__value">{!! nl2br(e($row['v'])) !!}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection 

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/checkout/complete/main.scss',
    ])

    {{-- Track Purchase Event --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(isset($order))
            var orderData = {
                id: '{{ $order->id }}',
                total: {{ $order->total->amount() }},
                tax: {{ $order->tax->amount() }},
                shipping: {{ $order->shipping_cost->amount() }},
                currency: '{{ $order->currency }}',
                items: [
                    @foreach($order->products as $product)
                    {
                        item_id: '{{ $product->product_id }}',
                        item_name: '{{ addslashes($product->name) }}',
                        price: {{ $product->unit_price->amount() }},
                        quantity: {{ $product->qty }}
                    }@if(!$loop->last),@endif
                    @endforeach
                ]
            };

            // Track purchase with FleetCartAnalytics
            if (typeof window.FleetCartAnalytics !== 'undefined') {
                window.FleetCartAnalytics.trackPurchase(orderData);
            }
            @endif
        });
    </script>
@endpush
