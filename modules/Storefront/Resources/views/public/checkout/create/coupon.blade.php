<div 
    class="coupon-outer-wrap" 
    x-data="{ couponFieldOpen: false }"
    :class="{ 'coupon-applied': $store.cart.hasCoupon }"
>
    <!-- Durum 1: Kupon Uygulanmamışsa Gösterilecek Başlık -->
    <template x-if="!$store.cart.hasCoupon">
        <div class="coupon-header-toggle" @click="couponFieldOpen = !couponFieldOpen">
            <div class="d-flex align-items-center">
                 <i class="las la-tag main-icon"></i>
                 <span class="title-text">İndirim / Hediye Kodu Ekle</span>
            </div>
            
            <div class="arrow-icon" :class="{ 'open': couponFieldOpen }">
                <i class="las la-angle-down"></i>
            </div>
        </div>
    </template>

    <!-- Durum 2: Kupon UYGULANMIŞSA Gösterilecek Rozet -->
    <template x-if="$store.cart.hasCoupon">
        <div class="applied-coupon-wrapper">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="success-icon">
                        <i class="las la-check"></i>
                    </div>
                    <div>
                        <div class="applied-label">Kupon Uygulandı</div>
                        <div class="applied-code" x-text="$store.cart.cart.coupon.code"></div>
                    </div>
                </div>
                <button type="button" class="btn-remove-coupon-link" @click="removeCoupon">
                    Kaldır
                </button>
            </div>
        </div>
    </template>

    <!-- Açılır Panel Content -->
    <div 
        class="coupon-body-content"
        x-show="couponFieldOpen && !$store.cart.hasCoupon" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        style="display: none;" 
    >
        <div class="input-group-custom">
            <input
                type="text"
                placeholder="{{ trans('storefront::checkout.enter_coupon_code') }}"
                class="form-control coupon-input"
                @keyup.enter="applyCoupon()"
                @input="couponError = null"
                x-model="couponCode"
            >

            <button
                type="button"
                class="btn btn-apply-coupon"
                :disabled="applyingCoupon"
                @click.prevent="applyCoupon()"
            >
                <span x-show="!applyingCoupon">{{ trans('storefront::checkout.apply') }}</span>
                <i x-show="applyingCoupon" class="las la-spinner la-spin"></i>
            </button>
        </div>

        <template x-if="couponError">
            <span class="error-message" x-text="couponError"></span>
        </template>

        @auth
            @if ($availableCoupons->isNotEmpty())
                <div class="my-coupons-trigger-wrap">
                    <button 
                        type="button" 
                        class="btn-my-coupons-trigger" 
                        @click="showCouponList = true"
                    >
                        <span class="d-flex align-items-center">
                            <i class="las la-ticket-alt ticket-icon"></i>
                            Kuponlarım ({{ $couponCount }})
                        </span>
                        <i class="las la-angle-right"></i>
                    </button>
                </div>
            @endif
        @endauth
    </div>
</div>

@push('globals')
    <style>
        .coupon-outer-wrap {
            background: #fff;
            border: 1px solid #eef1f5;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }

        .coupon-outer-wrap.coupon-applied {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .applied-coupon-wrapper {
            padding: 5px 0;
        }

        .success-icon {
            width: 32px;
            height: 32px;
            background: #10b981;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 18px;
        }

        .applied-label {
            font-size: 11px;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .applied-code {
            font-size: 14px;
            font-weight: 800;
            color: #064e3b;
            font-family: monospace;
        }

        .btn-remove-coupon-link {
            background: none;
            border: none;
            color: #ef4444;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            text-decoration: underline;
        }

        .coupon-header-toggle {
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .coupon-header-toggle .main-icon {
            font-size: 22px;
            color: #6366f1;
            margin-right: 12px;
        }

        .coupon-header-toggle .title-text {
            font-weight: 700;
            color: #1a202c;
            font-size: 15px;
        }

        .coupon-header-toggle .arrow-icon {
            transition: transform 0.3s ease;
            color: #64748b;
        }

        .coupon-header-toggle .arrow-icon.open {
            transform: rotate(180deg);
        }

        .coupon-body-content {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
        }

        .input-group-custom {
            display: flex;
            width: 100%;
        }

        .coupon-input {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            border-right: 0 !important;
            height: 48px !important;
            flex: 1;
        }

        .btn-apply-coupon {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            height: 48px !important;
            padding: 0 25px !important;
            font-weight: 700 !important;
            background: #1a202c !important;
            color: #fff !important;
            border: none !important;
        }

        .error-message {
            display: block;
            color: #ef4444;
            font-size: 13px;
            margin-top: 10px;
            font-weight: 500;
        }

        .my-coupons-trigger-wrap {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #e2e8f0;
        }

        .btn-my-coupons-trigger {
            width: 100%;
            background: none;
            border: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #6366f1;
            font-weight: 700;
            font-size: 14px;
        }

        @media (max-width: 767px) {
            .coupon-outer-wrap {
                margin-left: -15px;
                margin-right: -15px;
                width: calc(100% + 30px);
                border-radius: 0;
                padding: 15px;
            }
        }
    </style>
@endpush
