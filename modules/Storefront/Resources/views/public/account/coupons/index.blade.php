@extends('storefront::public.account.layout')

@section('title', trans('storefront::account.pages.my_coupons'))

@section('account_breadcrumb')
    <li class="active">{{ trans('storefront::account.pages.my_coupons') }}</li>
@endsection

@section('panel')
    <div class="panel">
        <div class="panel-header">
            <h4>{{ trans('storefront::account.pages.my_coupons') }}</h4>
        </div>

        <div class="panel-body">
            @if ($coupons->isEmpty())
                <div class="empty-state-v9">
                    <div class="icon-wrap">
                        <i class="las la-ticket-alt"></i>
                    </div>
                    <h3>{{ trans('storefront::account.coupons.no_coupons') }}</h3>
                    <p>Henüz tanımlanmış bir kuponunuz bulunmuyor.</p>
                </div>
            @else
                <div class="coupons-container-v9">
                    @foreach ($coupons as $coupon)
                        @php
                            $isPassive = $coupon->usageLimitReached(auth()->user()?->email) || !$coupon->is_active || $coupon->invalid();
                        @endphp
                        <div class="coupon-card-v9 {{ $isPassive ? 'passive' : '' }}">
                            <div class="card-head">
                                <div class="status-badge-corner">
                                    @if ($isPassive)
                                        <span class="status-pill passive">{{ trans('storefront::account.coupons.passive') }}</span>
                                    @else
                                        <span class="status-pill">{{ trans('storefront::account.coupons.active') }}</span>
                                    @endif
                                </div>
                                <div class="discount-announcement">
                                    @if ($coupon->is_percent)
                                        <span class="discount-value">%{{ (int) $coupon->value }}</span>
                                    @else
                                        <span class="discount-value">{{ $coupon->value->convertToCurrentCurrency()->format() }}</span>
                                    @endif
                                    <span class="discount-message">Tutarında İndirim Kuponu Hesabınıza Tanımlandı!</span>
                                </div>
                            </div>

                            <div class="card-body-v9">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="item-label">
                                            <i class="las la-tag"></i>
                                            <span>Kupon Adı</span>
                                        </div>
                                        <div class="item-value">{{ $coupon->name }}</div>
                                    </div>

                                    <div class="info-item">
                                        <div class="item-label">
                                            <i class="las la-key"></i>
                                            <span>Kupon Kodu</span>
                                        </div>
                                        <div class="item-value">
                                            <div class="copy-box-v9 btn-copy-coupon" data-clipboard-target="#coupon-{{ $coupon->id }}">
                                                <span id="coupon-{{ $coupon->id }}">{{ $coupon->code }}</span>
                                                <i class="las la-copy"></i>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($coupon->end_date)
                                        <div class="info-item">
                                            <div class="item-label">
                                                <i class="las la-calendar-alt"></i>
                                                <span>Son Kullanım</span>
                                            </div>
                                            <div class="item-value text-danger">{{ $coupon->end_date->translatedFormat('d F Y') }}</div>
                                        </div>
                                    @endif

                                    <div class="info-item">
                                        <div class="item-label">
                                            <i class="las la-user-shield"></i>
                                            <span>Kullanım Limiti</span>
                                        </div>
                                        <div class="item-value">
                                            @if (!is_null($coupon->usage_limit_per_customer))
                                                @php
                                                    $remaining = $coupon->remainingUsageForCustomer();
                                                @endphp
                                                {{ $remaining }} / {{ $coupon->usage_limit_per_customer }} Kullanım Kaldı
                                            @else
                                                Limitsiz Geçerlilik
                                            @endif
                                        </div>
                                    </div>

                                    @if ($coupon->minimum_spend)
                                        <div class="info-item">
                                            <div class="item-label">
                                                <i class="las la-shopping-basket"></i>
                                                <span>Alt Limit</span>
                                            </div>
                                            <div class="item-value">{{ $coupon->minimum_spend->convertToCurrentCurrency()->format() }}</div>
                                        </div>
                                    @endif

                                    @if ($coupon->isRedeemed())
                                        <div class="info-item usage-history">
                                            <div class="item-label">
                                                <i class="las la-check-circle"></i>
                                                <span>Kullanım Geçmişi</span>
                                            </div>
                                            <div class="item-value">
                                                <div class="usage-details" style="text-align: right;">
                                                    <div class="usage-date" style="margin-bottom: 2px;">{{ $coupon->redeemed_at->translatedFormat('d F Y, H:i') }}</div>
                                                    @if ($coupon->redeemedOrder)
                                                        <a href="{{ route('account.orders.show', $coupon->redeemedOrder) }}" class="order-link" style="color: #6366f1; font-weight: 700; text-decoration: none;">
                                                            #{{ $coupon->redeemedOrder->order_number }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
    <script>
        (function() {
            const clipboard = new ClipboardJS('.btn-copy-coupon');
            
            clipboard.on('success', function(e) {
                const btn = e.trigger;
                const originalHtml = btn.innerHTML;
                btn.classList.add('is-copied');
                btn.innerHTML = '<span>Kopyalandı!</span> <i class="las la-check"></i>';
                
                if (typeof showToast !== 'undefined') {
                    showToast('{{ trans('storefront::account.view_order.copied_to_clipboard') }}', 'success');
                }

                setTimeout(function() {
                    btn.classList.remove('is-copied');
                    btn.innerHTML = originalHtml;
                }, 2000);
            });
        })();
    </script>
@endpush

@push('globals')
    <style>
        .coupons-container-v9 {
            padding: 10px 0;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .coupon-card-v9 {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eef1f5;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            width: 100%;
        }

        /* CARD HEADER */
        .card-head {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            padding: 50px 30px 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            position: relative;
            min-height: 120px;
        }

        /* Gift Icon - Top Left Corner */
        .gift-icon-corner {
            position: absolute;
            top: 15px;
            left: 20px;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        /* Status Badge - Top Right Corner */
        .status-badge-corner {
            position: absolute;
            top: 15px;
            right: 20px;
        }

        .status-pill {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.2px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: inline-block;
        }

        .status-pill.passive {
            background: rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
        }

        .coupon-card-v9.passive {
            opacity: 0.8;
            filter: grayscale(0.5);
        }

        .coupon-card-v9.passive .card-head {
            background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        }

        .coupon-card-v9.passive .item-label i {
            color: #94a3b8;
        }

        /* Centered Discount Announcement - Single Line */
        .discount-announcement {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        .discount-value {
            font-size: 36px;
            font-weight: 900;
            letter-spacing: -1.5px;
            line-height: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .discount-message {
            font-size: 16px;
            font-weight: 600;
            opacity: 0.95;
            letter-spacing: 0.3px;
        }

        /* CARD BODY */
        .card-body-v9 {
            padding: 10px 0;
        }

        .info-grid {
            display: flex;
            flex-direction: column;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 35px;
            border-bottom: 1px solid #f7f9fc;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .item-label {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #7f8c9b;
            font-size: 14px;
            font-weight: 500;
        }

        .item-label i {
            font-size: 18px;
            color: #6366f1;
            opacity: 0.7;
            width: 20px;
            text-align: center;
        }

        .item-value {
            color: #1a202c;
            font-size: 15px;
            font-weight: 700;
            text-align: right;
        }

        /* COPY BOX */
        .copy-box-v9 {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e4e9f0;
            padding: 6px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'DM Mono', monospace;
        }

        .copy-box-v9:hover {
            border-color: #6366f1;
            background: #fff;
            color: #6366f1;
        }

        .copy-box-v9 i {
            color: #6366f1;
            font-size: 16px;
        }

        .copy-box-v9.is-copied {
            background: #10b981;
            border-color: #10b981;
            color: #fff;
        }

        .copy-box-v9.is-copied i {
            color: #fff;
        }

        /* USAGE HISTORY */
        .usage-history .item-label i {
            color: #10b981;
        }

        .usage-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
        }

        .usage-date {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }

        .order-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #6366f1;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .order-link:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        .order-link i {
            font-size: 16px;
        }

        /* EMPTY STATE */
        .empty-state-v9 {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-v9 .icon-wrap {
            font-size: 64px;
            color: #e2e8f0;
            margin-bottom: 20px;
        }

        .empty-state-v9 h3 {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .empty-state-v9 p {
            color: #718096;
        }

        /* RESPONSIVE */
        @media (max-width: 991px) {
            .discount-announcement {
                flex-direction: column;
                gap: 8px;
            }
            .discount-value {
                font-size: 32px;
            }
            .discount-message {
                font-size: 15px;
            }
        }

        @media (max-width: 768px) {
            .card-head {
                padding: 45px 20px 25px;
                min-height: 110px;
            }
            .gift-icon-corner {
                top: 12px;
                left: 15px;
                width: 36px;
                height: 36px;
                font-size: 20px;
            }
            .status-badge-corner {
                top: 12px;
                right: 15px;
            }
            .status-pill {
                padding: 5px 12px;
                font-size: 10px;
            }
            .discount-value {
                font-size: 28px;
            }
            .discount-message {
                font-size: 14px;
            }
            .info-item {
                padding: 15px 20px;
            }
        }
    </style>
@endpush
