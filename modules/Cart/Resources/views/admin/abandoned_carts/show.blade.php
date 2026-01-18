@extends('admin::layout')

@section('title', 'Sepet Detayı')

@section('content_header')
    <div style="margin-bottom: 24px; background: #fff; padding: 20px 24px; border-bottom: 1px solid #e8e8e8;">
        <h3 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 600; color: rgba(0, 0, 0, 0.88); font-family: Inter, sans-serif;">
            Sepet Detayı
        </h3>
        <p style="margin: 0; color: rgba(0, 0, 0, 0.45); font-size: 12px; font-family: Inter, sans-serif;">
            {{ substr($cart->id, 0, 20) }}...
        </p>
    </div>
@endsection

@push('styles')
<style>
    body.skin-blue .content-wrapper {
        background: #f5f5f5;
    }

    .content-wrapper {
        font-family: Inter, sans-serif;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    @media (max-width: 992px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .card-minimal {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e8e8e8;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .card-header-minimal {
        background: #fafafa;
        border-bottom: 1px solid #e8e8e8;
        padding: 16px 20px;
        font-weight: 600;
        font-size: 14px;
        color: rgba(0, 0, 0, 0.88);
        font-family: Inter, sans-serif;
    }

    .card-body-minimal {
        padding: 20px;
    }

    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-row:first-child {
        padding-top: 0;
    }

    .info-label {
        width: 110px;
        font-size: 14px;
        color: rgba(0, 0, 0, 0.45);
        flex-shrink: 0;
        font-family: Inter, sans-serif;
    }

    .info-value {
        color: rgba(0, 0, 0, 0.88);
        font-size: 14px;
        font-weight: 400;
        font-family: Inter, sans-serif;
    }

    .badge-minimal {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 400;
        font-family: Inter, sans-serif;
        line-height: 20px;
    }

    .badge-minimal.success {
        background: #f6ffed;
        border: 1px solid #b7eb8f;
        color: #52c41a;
    }

    .badge-minimal.warning {
        background: #fffbe6;
        border: 1px solid #ffe58f;
        color: #faad14;
    }

    .badge-minimal.info {
        background: #e6f7ff;
        border: 1px solid #91d5ff;
        color: #1890ff;
    }

    .badge-minimal.default {
        background: #fafafa;
        border: 1px solid #d9d9d9;
        color: rgba(0, 0, 0, 0.45);
    }

    .product-table {
        width: 100%;
        border-collapse: collapse;
    }

    .product-table thead th {
        background: #fafafa;
        color: rgba(0, 0, 0, 0.45);
        padding: 12px 16px;
        font-weight: 600;
        font-size: 12px;
        text-align: left;
        border-bottom: 1px solid #e8e8e8;
        font-family: Inter, sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .product-table thead th:last-child {
        text-align: right;
    }

    .product-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }

    .product-table tbody tr:hover {
        background: #fafafa;
    }

    .product-table tbody tr:last-child {
        border-bottom: none;
    }

    .product-table tbody td {
        padding: 16px;
        vertical-align: middle;
        font-family: Inter, sans-serif;
        font-size: 14px;
        color: rgba(0, 0, 0, 0.88);
    }

    .product-table tbody td:last-child {
        text-align: right;
    }

    .product-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .product-image {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        border-radius: 4px;
        overflow: hidden;
        background: #fafafa;
        border: 1px solid #e8e8e8;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .product-image:hover {
        border-color: #4096ff;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(0, 0, 0, 0.25);
        font-size: 24px;
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-weight: 500;
        color: rgba(0, 0, 0, 0.88);
        font-size: 14px;
        margin-bottom: 4px;
        font-family: Inter, sans-serif;
    }

    .product-variant {
        font-size: 12px;
        color: rgba(0, 0, 0, 0.45);
        margin-top: 4px;
        font-family: Inter, sans-serif;
    }

    .btn-minimal {
        background: #1890ff;
        color: #fff;
        border: 1px solid #1890ff;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: Inter, sans-serif;
        cursor: pointer;
    }

    .btn-minimal:hover {
        background: #40a9ff;
        border-color: #40a9ff;
        color: #fff;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: rgba(0, 0, 0, 0.25);
    }

    .empty-state i {
        font-size: 48px;
        opacity: 0.3;
        margin-bottom: 12px;
    }

    .empty-state p {
        font-size: 14px;
        margin: 0;
        font-family: Inter, sans-serif;
        color: rgba(0, 0, 0, 0.45);
    }

    /* Image Preview */
    .image-preview-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .image-preview-popup.active {
        display: flex;
    }

    .image-preview-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }

    .image-preview-content img {
        max-width: 100%;
        max-height: 90vh;
        border-radius: 8px;
    }

    .image-preview-close {
        position: absolute;
        top: -40px;
        right: 0;
        background: #fff;
        border: 1px solid #d9d9d9;
        width: 32px;
        height: 32px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        color: rgba(0, 0, 0, 0.88);
        transition: all 0.2s;
    }

    .image-preview-close:hover {
        border-color: #4096ff;
        color: #4096ff;
    }

    /* Upsell Item Styles */
    .product-table tbody tr.upsell-row {
        background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%);
        border-left: 4px solid #f59e0b;
    }

    .upsell-badge-detail {
        display: inline-block;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 12px;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: Inter, sans-serif;
    }

    .product-table tbody tr.upsell-row td.upsell-total {
        color: #10b981 !important;
        font-weight: 800 !important;
    }
</style>
@endpush

@section('content')
    <div class="detail-grid">
        {{-- Sidebar --}}
        <div>
            {{-- Customer Info --}}
            <div class="card-minimal">
                <div class="card-header-minimal">
                    Müşteri Bilgileri
                </div>
                <div class="card-body-minimal">
                    <div class="info-row">
                        <div class="info-label">Ad Soyad</div>
                        <div class="info-value">
                            {{ trim(($cart->customer_first_name ?? '') . ' ' . ($cart->customer_last_name ?? '')) ?: '-' }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">E-posta</div>
                        <div class="info-value">
                            {{ $cart->customer_email ?: '-' }}
                            @if($cart->is_recovered && $cart->recovered_by_email && $cart->recovered_by_email !== $cart->customer_email)
                                <div style="font-size: 11px; color: #10b981; margin-top: 4px; font-weight: 600;">
                                    Sipariş Veren: {{ $cart->recovered_by_email }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Telefon</div>
                        <div class="info-value">
                            {{ $cart->customer_phone ?: '-' }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Kullanıcı</div>
                        <div class="info-value">
                            {{ $cart->user_id ?: 'Misafir' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="card-minimal">
                <div class="card-header-minimal">
                    Sepet Durumu
                </div>
                <div class="card-body-minimal">
                    <div class="info-row">
                        <div class="info-label">Durum</div>
                        <div class="info-value">
                            @if($cart->is_recovered)
                                <span class="badge-minimal success">Kurtarıldı</span>
                            @else
                                <span class="badge-minimal warning">Beklemede</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Oluşturma</div>
                        <div class="info-value">
                            {{ $cart->created_at->format('d.m.Y H:i') }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Güncelleme</div>
                        <div class="info-value">
                            {{ $cart->updated_at->format('d.m.Y H:i') }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Hatırlatma</div>
                        <div class="info-value">
                            <span class="badge-minimal info">{{ $cart->reminder_count ?? 0 }} kez</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Son Hatırlatma</div>
                        <div class="info-value">
                            {{ $cart->last_notified_at ? $cart->last_notified_at->format('d.m.Y H:i') : '-' }}
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email Tıklama</div>
                        <div class="info-value">
                            @if($cart->is_clicked)
                                <span class="badge-minimal info">Evet</span>
                                <div style="font-size: 11px; color: #adb5bd; margin-top: 4px;">
                                    {{ $cart->clicked_at->format('d.m.Y H:i') }}
                                </div>
                            @else
                                <span class="badge-minimal default">Hayır</span>
                            @endif
                        </div>
                    </div>
                    @if($cart->is_recovered && $cart->order)
                        <div class="info-row">
                            <div class="info-label">Sipariş</div>
                            <div class="info-value">
                                <a href="{{ route('admin.orders.show', $cart->order_id) }}" 
                                   target="_blank" 
                                   style="color: #495057; font-weight: 600;">
                                    #{{ $cart->order->id }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Products --}}
        <div>
            <div class="card-minimal">
                <div class="card-header-minimal">
                    Sepetteki Ürünler ({{ count($cart->data ?? []) }})
                </div>
                <div style="padding: 0;">
                    @if(count($cart->data ?? []) > 0)
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th style="text-align: center;">Miktar</th>
                                    <th style="text-align: right;">Birim Fiyat</th>
                                    <th style="text-align: right;">Toplam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart->data as $item)
                                    @php
                                        $cartItem = new \Modules\Cart\CartItem($item);
                                        $prod = $cartItem->product;
                                        $vari = $cartItem->variant;
                                        $image = optional($vari?->base_image)->path ?? optional($prod?->base_image)->path ?? null;
                                        $isUpsell = !empty($cartItem->upsell) && isset($cartItem->upsell['is_upsell']) && $cartItem->upsell['is_upsell'];
                                    @endphp
                                    <tr class="{{ $isUpsell ? 'upsell-row' : '' }}">
                                        <td>
                                            <div class="product-cell">
                                                <div class="product-image" data-image="{{ $image }}">
                                                    @if($image)
                                                        <img src="{{ $image }}" alt="{{ $item->name }}">
                                                    @else
                                                        <div class="product-image-placeholder">
                                                            <i class="fa fa-image"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="product-info">
                                                    @if($isUpsell)
                                                        <span class="upsell-badge-detail">🎁 Sepet Teklifi</span>
                                                    @endif
                                                    <div class="product-name">{{ $item->name }}</div>
                                                    @if($cartItem->variations->isNotEmpty())
                                                        @foreach($cartItem->variations as $variation)
                                                            <div class="product-variant">
                                                                {{ $variation->name }}: {{ $variation->values->pluck('label')->implode(', ') }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td style="text-align: center; font-weight: 600; color: rgba(0, 0, 0, 0.88); font-family: Inter, sans-serif;">
                                            @php
                                                $qty = (float)($item->quantity ?? 0);
                                                $qtyValue = fmod($qty, 1) === 0.0 ? (int)$qty : rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
                                                $unitSuffix = $prod ? ($prod->unit_suffix ?? '') : '';
                                                $qtyDisplay = $unitSuffix ? trim($qtyValue . ' ' . $unitSuffix) : $qtyValue . ' Adet';
                                            @endphp
                                            {{ $qtyDisplay }}
                                        </td>
                                        <td style="text-align: right; font-weight: 600; color: #495057;">
                                            @if($isUpsell && isset($cartItem->upsell['original_price']) && $cartItem->upsell['original_price'] > 0)
                                                <div style="color: #94a3b8; font-size: 12px; text-decoration: line-through; margin-bottom: 2px;">
                                                    {{ \Modules\Support\Money::inDefaultCurrency($cartItem->upsell['original_price'])->format() }}
                                                </div>
                                                <div style="color: #10b981; font-weight: 700;">
                                                    {{ $cartItem->unitPrice()->format() }}
                                                </div>
                                            @else
                                                {{ $cartItem->unitPrice()->format() }}
                                            @endif
                                        </td>
                                        <td style="text-align: right; font-weight: 700; font-size: 15px;" class="{{ $isUpsell ? 'upsell-total' : '' }}">
                                            {{ $cartItem->totalPrice()->format() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <i class="fa fa-shopping-cart"></i>
                            <p>Sepette ürün bulunmuyor</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Back Button --}}
    <div style="margin-top: 20px;">
        <a href="{{ route('admin.abandoned_carts.index') }}" class="btn-minimal">
            <i class="fa fa-arrow-left"></i>
            Listeye Dön
        </a>
    </div>

    {{-- Image Preview --}}
    <div class="image-preview-popup" id="imagePreview">
        <div class="image-preview-content">
            <button class="image-preview-close" onclick="closeImagePreview()">
                <i class="fa fa-times"></i>
            </button>
            <img src="" alt="Preview" id="previewImage">
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.product-image').on('click', function() {
            var imageSrc = $(this).data('image');
            if (imageSrc) {
                $('#previewImage').attr('src', imageSrc);
                $('#imagePreview').addClass('active');
            }
        });

        $('#imagePreview').on('click', function(e) {
            if (e.target === this) {
                closeImagePreview();
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImagePreview();
            }
        });
    });

    function closeImagePreview() {
        $('#imagePreview').removeClass('active');
    }
</script>
@endpush
