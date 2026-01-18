@if ($availableCoupons->isNotEmpty())
    <div 
        class="custom-modal-overlay" 
        x-show="showCouponList" 
        x-transition.opacity 
        style="display: none;"
    >
        <div 
            class="custom-modal-content trendyol-style" 
            @click.outside="showCouponList = false"
            x-show="showCouponList"
            x-transition:enter="transition-transform-opacity"
            x-transition:enter-start="translate-y-full opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition-transform-opacity"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-full opacity-0"
        >
            <div class="modal-drag-handle"></div>
            
            <div class="custom-modal-header">
                <h3>Kullanılabilir Kuponlarım</h3>
                <button type="button" class="close-btn" @click="showCouponList = false">
                    <i class="las la-times"></i>
                </button>
            </div>
            
            <div class="custom-modal-body">
                <div class="trendyol-coupons-list">
                    @foreach ($availableCoupons as $coupon)
                        <div 
                            class="trendyol-coupon-card"
                            :class="{ 'is-selected': cart.coupon && cart.coupon.code === '{{ $coupon->code }}' }"
                            @click="if (!(cart.coupon && cart.coupon.code === '{{ $coupon->code }}')) applyAvailableCoupon('{{ $coupon->code }}')"
                        >
                            <div class="coupon-left-part">
                                <div class="discount-box">
                                    @if ($coupon->is_percent)
                                        <span class="symbol">%</span>
                                        <span class="value">{{ (int) $coupon->value }}</span>
                                    @else
                                        <span class="value">{{ (int) $coupon->value->amount() }}</span>
                                        <span class="symbol">TL</span>
                                    @endif
                                </div>
                                <div class="coupon-type">İndirim</div>
                            </div>
                            
                            <div class="coupon-right-part">
                                <div class="coupon-info">
                                    <h4 class="coupon-name">{{ $coupon->name }}</h4>
                                    <p class="coupon-desc">
                                        @if ($coupon->minimum_spend)
                                            {{ $coupon->minimum_spend->convertToCurrentCurrency()->format() }} ve üzeri alışverişlerde geçerli.
                                        @else
                                            Tüm alışverişlerde geçerli.
                                        @endif
                                    </p>
                                    <div class="coupon-footer">
                                        <span class="coupon-code-label">Kod: <strong>{{ $coupon->code }}</strong></span>
                                    </div>
                                </div>
                                
                                <div class="coupon-action">
                                    <template x-if="cart.coupon && cart.coupon.code === '{{ $coupon->code }}'">
                                        <div class="selected-badge">
                                            <i class="las la-check-circle"></i>
                                            <span>Uygulandı</span>
                                        </div>
                                    </template>
                                    <template x-if="!(cart.coupon && cart.coupon.code === '{{ $coupon->code }}')">
                                        <button 
                                            type="button" 
                                            class="btn-trendyol-apply" 
                                            :disabled="applyingCoupon"
                                            :class="{ 'is-loading': applyingCoupon }"
                                        >
                                            <span x-show="!applyingCoupon">UYGULA</span>
                                            <span x-show="applyingCoupon">
                                                <i class="las la-spinner la-spin"></i>
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            
                            <div class="ticket-cutout cut-top"></div>
                            <div class="ticket-cutout cut-bottom"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

@push('globals')
    <style>
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .transition-transform-opacity {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .custom-modal-content.trendyol-style {
            background: #f4f6f9;
            width: 100%;
            max-width: 550px;
            max-height: 85vh;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-drag-handle {
            display: none;
            width: 40px;
            height: 5px;
            background: #cbd5e1;
            border-radius: 10px;
            margin: 12px auto 0;
        }

        .custom-modal-header {
            padding: 20px 25px;
            background: #fff;
            border-bottom: 1px solid #eef1f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #1a202c;
        }

        .close-btn {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }

        .close-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .custom-modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        .trendyol-coupons-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .trendyol-coupon-card {
            background: #fff;
            border-radius: 12px;
            display: flex;
            min-height: 110px;
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .trendyol-coupon-card:hover {
            border-color: #6366f1;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        .trendyol-coupon-card.is-selected {
            border-color: #10b981;
            background: #f0fdf4;
        }

        /* Ticket Cutouts */
        .ticket-cutout {
            position: absolute;
            width: 16px;
            height: 16px;
            background: #f4f6f9; /* Same as body bg */
            border-radius: 50%;
            left: 92px; /* Center of cutout should align with left part border */
            z-index: 2;
        }

        .cut-top { top: -8px; border-bottom: 1px solid #e2e8f0; }
        .cut-bottom { bottom: -8px; border-top: 1px solid #e2e8f0; }

        .coupon-left-part {
            width: 100px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 10px;
            border-right: 1px dashed #e2e8f0;
            position: relative;
        }

        .trendyol-coupon-card.is-selected .coupon-left-part {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .discount-box {
            display: flex;
            align-items: baseline;
            gap: 2px;
        }

        .discount-box .value {
            font-size: 28px;
            font-weight: 900;
        }

        .discount-box .symbol {
            font-size: 14px;
            font-weight: 700;
        }

        .coupon-type {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
            opacity: 0.9;
        }

        .coupon-right-part {
            flex: 1;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .coupon-info {
            flex: 1;
            padding-right: 15px;
        }

        .coupon-name {
            margin: 0 0 5px 0;
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
        }

        .coupon-desc {
            margin: 0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }

        .coupon-footer {
            margin-top: 8px;
        }

        .coupon-code-label {
            font-size: 11px;
            color: #94a3b8;
        }

        .coupon-code-label strong {
            color: #6366f1;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 4px;
        }

        .coupon-action {
            display: flex;
            align-items: center;
        }

        .btn-trendyol-apply {
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-trendyol-apply:hover {
            border-color: #6366f1;
            color: #6366f1;
            background: #f5f3ff;
        }

        .selected-badge {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #10b981;
            font-weight: 800;
            font-size: 12px;
        }

        .selected-badge i {
            font-size: 18px;
        }

        /* Mobile specific adjustments (Bottom Sheet) */
        @media (max-width: 767px) {
            .custom-modal-overlay {
                align-items: flex-end;
            }

            .custom-modal-content.trendyol-style {
                max-width: 100%;
                border-radius: 24px 24px 0 0;
                max-height: 80vh;
            }

            .modal-drag-handle {
                display: block;
            }

            .custom-modal-header {
                padding: 15px 20px;
            }

            .custom-modal-body {
                padding: 15px;
            }

            .coupon-left-part {
                width: 80px;
            }

            .ticket-cutout {
                left: 72px;
            }

            .discount-box .value {
                font-size: 24px;
            }

            .coupon-right-part {
                padding: 12px 15px;
            }

            .coupon-name {
                font-size: 14px;
            }

            .btn-trendyol-apply {
                padding: 6px 12px;
                font-size: 11px;
            }
        }
    </style>
@endpush
