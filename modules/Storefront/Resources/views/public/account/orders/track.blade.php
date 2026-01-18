@extends('storefront::public.layout')

@section('title', trans('storefront::account.orders.track_order'))

@section('content')
    <section class="order-tracking-wrap">
        <div class="container">
            <div class="order-tracking-inner">
                
                {{-- Reverted Search Section to "First Design" Style (Card Based) --}}
                <div class="tracking-search-container {{ isset($order) ? 'has-result' : '' }}">
                    <div class="tracking-card search-card">
                        <div class="card-header">
                            <h3>{{ trans('storefront::account.orders.track_order') }}</h3>
                            <p>{{ trans('storefront::account.orders.track_order_subtitle') }}</p>
                        </div>

                        <form method="POST" action="{{ route('order_tracking.show') }}" class="card-body">
                            @csrf

                            <div class="form-group">
                                <label for="order_id">{{ trans('storefront::account.orders.order_id') }}</label>
                                <div class="input-wrapper">
                                    <i class="las la-hashtag"></i>
                                    <input type="text" name="order_id" id="order_id" class="form-control" value="{{ old('order_id') }}" placeholder="Örn: KYM_1234" required>
                                </div>
                                @error('order_id')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">{{ trans('storefront::account.profile.email') }}</label>
                                <div class="input-wrapper">
                                    <i class="las la-envelope"></i>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="example@gmail.com" required>
                                </div>
                                @error('email')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn-track-submit">
                                <span>Siparişimi Sorgula</span>
                            </button>
                        </form>
                    </div>
                </div>

                @if (isset($order))
                    {{-- Result Section --}}
                    <div class="order-result-wrap">
                        {{-- Status Timeline --}}
                        <div class="tracking-card timeline-card">
                            <div class="order-summary-header">
                                <div class="order-meta">
                                    <span class="order-no">#{{ $order->displayOrderNumber() }}</span>
                                    <span class="order-date">{{ $order->created_at->translatedFormat('d F Y') }}</span>
                                </div>
                                <div class="order-status-badge {{ $order->status }}">
                                    {{ $order->status() }}
                                </div>
                            </div>

                            <div class="status-timeline">
                                @php
                                    $statuses = [
                                        ['key' => 'pending', 'label' => 'Alındı', 'icon' => 'las la-receipt'],
                                        ['key' => 'processing', 'label' => 'Hazırlanıyor', 'icon' => 'las la-box'],
                                        ['key' => 'shipped', 'label' => 'Kargoda', 'icon' => 'las la-shipping-fast'],
                                        ['key' => 'completed', 'label' => 'Teslim Edildi', 'icon' => 'las la-check-double']
                                    ];
                                    
                                    $currentStep = 0;
                                    if(in_array($order->status, ['pending', 'pending_payment'])) $currentStep = 0;
                                    elseif(in_array($order->status, ['on_the_way', 'out_for_delivery'])) $currentStep = 1;
                                    elseif(in_array($order->status, ['shipped'])) $currentStep = 2;
                                    elseif(in_array($order->status, ['completed'])) $currentStep = 3;
                                    else $currentStep = 1;
                                @endphp

                                <div class="timeline-container">
                                    @foreach ($statuses as $index => $step)
                                        <div class="timeline-item {{ $index <= $currentStep ? 'active' : '' }} {{ $index < $currentStep ? 'done' : '' }}">
                                            <div class="icon-wrap">
                                                <i class="{{ $step['icon'] }}"></i>
                                            </div>
                                            <span class="step-name">{{ $step['label'] }}</span>
                                            @if($index < count($statuses) - 1)
                                                <div class="progress-line"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Unified Info Card --}}
                        <div class="tracking-card info-card">
                            <div class="card-title">Sipariş Bilgileri</div>
                            <div class="info-grid">
                                <div class="info-section">
                                    <h3>İletişim Bilgileri</h3>
                                    <div class="info-item">
                                        <label>Alıcı</label>
                                        <span>{{ $order->customer_full_name }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>Telefon</label>
                                        <span>{{ $order->customer_phone }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>E-posta</label>
                                        <span>{{ $order->customer_email }}</span>
                                    </div>
                                </div>

                                <div class="info-section">
                                    <h3>Teslimat & Ödeme</h3>
                                    @if($order->shipping_state_name || $order->shipping_city_title)
                                        <div class="info-item">
                                            <label>Teslimat Adresi</label>
                                            <span class="address-text">
                                                @if($order->shippingAddress)
                                                    {{ $order->shippingAddress->address_1 }} <br>
                                                @endif
                                                {{ $order->shipping_city_title }} / {{ $order->shipping_state_name }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="info-item">
                                        <label>Ödeme Yöntemi</label>
                                        <span>{{ $order->payment_method }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>Toplam Tutar</label>
                                        <span class="total-highlight">{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                                    </div>
                                </div>

                                <div class="info-section full-width">
                                    <h3>Kargo & Takip</h3>
                                    <div class="shipping-flex">
                                        <div class="info-item">
                                            <label>Kargo Firması</label>
                                            <span>{{ $order->shipping_method }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label>Kargo Takip No</label>
                                            @if($order->tracking_reference)
                                                <div class="tracking-box">
                                                    <span class="tracking-no">{{ $order->tracking_reference }}</span>
                                                    @if(filter_var($order->tracking_reference, FILTER_VALIDATE_URL))
                                                        <a href="{{ $order->tracking_reference }}" target="_blank" class="btn-track-kargo">
                                                            Kargom Nerede? <i class="las la-external-link-alt"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="not-available">Henüz kargo girişi yapılmadı.</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    :root {
        --track-bg: #f8fafc;
        --track-primary: #006aff;
        --track-success: #10b981;
        --track-text: #1e293b;
        --track-text-muted: #64748b;
        --track-border: #e2e8f0;
        --track-radius: 24px;
    }

    .order-tracking-wrap {
        padding: 60px 0 100px;
        background: var(--track-bg);
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
    }

    .order-tracking-inner {
        max-width: 800px;
        margin: 0 auto;
    }

    .tracking-card {
        background: #fff;
        border-radius: var(--track-radius);
        border: 1px solid var(--track-border);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
        overflow: hidden;
    }

    /* Search Section Reverted Style */
    .tracking-search-container {
        max-width: 500px;
        margin: 0 auto 40px;
    }

    .tracking-search-container.has-result {
        margin-bottom: 50px;
    }

    .search-card {
        padding: 40px;
    }

    .search-card .card-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .search-card .header-icon {
        font-size: 44px;
        color: var(--track-primary);
        margin-bottom: 12px;
    }

    .search-card h3 {
        font-size: 24px;
        font-weight: 800;
        color: var(--track-text);
        margin-bottom: 8px;
    }

    .search-card p {
        color: var(--track-text-muted);
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: var(--track-text);
        margin-bottom: 8px;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-wrapper i {
        position: absolute;
        left: 16px;
        font-size: 20px;
        color: var(--track-primary);
    }

    .input-wrapper .form-control {
        height: 54px;
        padding-left: 48px;
        border-radius: 14px;
        border: 1px solid var(--track-border);
        background: #fcfdfe;
        font-weight: 500;
        transition: all 0.3s;
    }

    .input-wrapper .form-control:focus {
        border-color: var(--track-primary);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(0, 106, 255, 0.08);
    }

    .btn-track-submit {
        width: 100%;
        height: 54px;
        border-radius: 14px;
        background: var(--track-primary);
        color: #fff;
        border: none;
        font-weight: 700;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
        transition: all 0.3s;
    }

    .btn-track-submit:hover {
        background: #005ce6;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 106, 255, 0.2);
    }

    .error-message {
        color: #ef4444;
        font-size: 12px;
        font-weight: 600;
        margin-top: 6px;
        display: block;
    }

    /* Result Styling */
    .timeline-card { padding: 30px; }

    .order-summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 35px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .order-no { font-size: 18px; font-weight: 800; color: var(--track-text); display: block; }
    .order-date { font-size: 13px; color: var(--track-text-muted); }

    .order-status-badge {
        padding: 8px 16px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 700;
        background: #f1f5f9;
        color: #475569;
    }

    .order-status-badge.completed { background: #dcfce7; color: #166534; }
    .order-status-badge.shipped { background: #dbeafe; color: #1e40af; }

    /* Timeline */
    .timeline-container {
        display: flex;
        justify-content: space-between;
        position: relative;
    }

    .timeline-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
        z-index: 2;
    }

    .icon-wrap {
        width: 48px;
        height: 48px;
        background: #fff;
        border: 2px solid #eee;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #ccc;
    }

    .timeline-item.active .icon-wrap { border-color: var(--track-primary); color: var(--track-primary); }
    .timeline-item.done .icon-wrap { background: var(--track-success); border-color: var(--track-success); color: #fff; }

    .step-name { margin-top: 12px; font-size: 12px; font-weight: 700; color: var(--track-text-muted); }
    .timeline-item.active .step-name { color: var(--track-text); }

    .progress-line { position: absolute; width: 100%; height: 3px; background: #f0f0f0; top: 24px; left: 50%; z-index: -1; }
    .timeline-item.done .progress-line { background: var(--track-success); }

    /* Unified Info Card */
    .info-card { padding: 30px; }
    .card-title { font-size: 18px; font-weight: 800; color: var(--track-text); margin-bottom: 25px; }

    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }

    .info-section h3 {
        font-size: 14px;
        font-weight: 800;
        color: var(--track-primary);
        margin-bottom: 20px;
        text-transform: uppercase;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 8px;
        display: inline-block;
    }

    .info-item { margin-bottom: 15px; }
    .info-item label { display: block; font-size: 11px; font-weight: 700; color: var(--track-text-muted); text-transform: uppercase; margin-bottom: 4px; }
    .info-item span { font-size: 14px; font-weight: 600; color: var(--track-text); }

    .total-highlight { font-size: 18px !important; color: var(--track-primary) !important; font-weight: 800 !important; }

    .full-width { grid-column: span 2; background: #f8fafc; padding: 20px; border-radius: 16px; }
    .shipping-flex { display: flex; gap: 40px; }
    
    .tracking-no { background: #fff; padding: 4px 10px; border-radius: 8px; border: 1px solid #ddd; font-family: monospace; color: var(--track-primary) !important; font-size: 16px !important; }
    .btn-track-kargo { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--track-primary); text-decoration: underline; }

    @media (max-width: 767px) {
        .info-grid { grid-template-columns: 1fr; }
        .full-width { grid-column: span 1; }
        .shipping-flex { flex-direction: column; gap: 15px; }
        .icon-wrap { width: 36px; height: 36px; font-size: 16px; }
        .progress-line { top: 18px; }
    }
</style>
@endpush
