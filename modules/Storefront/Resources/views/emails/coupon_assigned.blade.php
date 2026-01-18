<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; background: #f1f5f9; }
        .main-wrapper { width: 100%; padding: 40px 15px; background-color: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        .header { 
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); 
            padding: 50px 30px; 
            text-align: center; 
            color: #fff; 
        }
        .header-icon { font-size: 56px; margin-bottom: 15px; }
        .header h1 { margin: 10px 0 0; font-size: 32px; font-weight: 800; }
        .header p { margin: 15px 0 0; font-size: 18px; opacity: 0.95; font-weight: 600; }

        .content { padding: 40px 35px; text-align: center; }
        .main-text { font-size: 16px; color: #475569; line-height: 1.8; margin-bottom: 30px; }
        
        .coupon-card {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            margin: 30px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .coupon-header {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            padding: 30px;
            text-align: center;
            color: #fff;
        }
        .coupon-discount {
            font-size: 48px;
            font-weight: 900;
            margin-bottom: 8px;
        }
        .coupon-subtitle {
            font-size: 16px;
            font-weight: 600;
        }
        .coupon-body {
            padding: 30px;
            background: #f8fafc;
        }
        .coupon-code-wrapper {
            background: #fff;
            border: 2px dashed #6366f1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        .coupon-code {
            font-size: 32px;
            font-weight: 900;
            color: #6366f1;
            letter-spacing: 3px;
        }
        
        .coupon-details {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
        }
        .detail-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 700;
            text-align: right;
        }
        
        .info-note {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 8px;
            text-align: left;
        }
        .info-note p {
            margin: 0;
            font-size: 13px;
            color: #1e40af;
            line-height: 1.6;
        }
        .info-note strong {
            color: #1e3a8a;
        }

        .cta-wrapper { text-align: center; padding: 40px 30px; background: #fcfcfc; }
        .btn { 
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); 
            color: #fff !important; 
            padding: 18px 45px; 
            border-radius: 50px; 
            text-decoration: none; 
            font-weight: 800; 
            font-size: 16px; 
            display: inline-block; 
        }
        
        .footer { padding: 40px 30px; text-align: center; background: #f8fafc; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<table class="main-wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <div class="container">
                <div class="header">
                    <div class="header-icon">🎁</div>
                    <h1>Size Özel İndirim Kuponu!</h1>
                    <p>Hesabınıza özel bir indirim tanımlandı</p>
                </div>

                <div class="content">
                    <div class="main-text">
                        Merhaba <strong>{{ $customer->first_name }}</strong>,<br><br>
                        Size özel olarak hazırladığımız indirim kuponunu kullanarak alışverişlerinizde tasarruf edebilirsiniz!
                    </div>

                    <div class="coupon-card">
                        <div class="coupon-header">
                            <div class="coupon-discount">
                                @if ($coupon->is_percent)
                                    %{{ (int) $coupon->value }}
                                @else
                                    {{ $coupon->value->convertToCurrentCurrency()->format() }}
                                @endif
                            </div>
                            <div class="coupon-subtitle">İndirim Kuponu</div>
                        </div>
                        
                        <div class="coupon-body">
                            <div class="coupon-code-wrapper">
                                <div style="font-size: 14px; color: #64748b; font-weight: 600; margin-bottom: 10px;">İndirim Kodu:</div>
                                <div class="coupon-code">{{ $coupon->code }}</div>
                            </div>
                            
                            @if ($coupon->end_date || $coupon->usage_limit_per_customer || $coupon->minimum_spend)
                                <div class="coupon-details">
                                    <div class="detail-row">
                                        <div class="detail-label">🏷️ Kupon Adı</div>
                                        <div class="detail-value">{{ $coupon->name }}</div>
                                    </div>
                                    
                                    @if ($coupon->end_date)
                                        <div class="detail-row">
                                            <div class="detail-label">📅 Son Kullanım</div>
                                            <div class="detail-value">{{ $coupon->end_date->translatedFormat('d F Y') }}</div>
                                        </div>
                                    @endif

                                    @if ($coupon->usage_limit_per_customer)
                                        <div class="detail-row">
                                            <div class="detail-label">🔄 Kullanım Limiti</div>
                                            <div class="detail-value">{{ $coupon->usage_limit_per_customer }} Kullanım</div>
                                        </div>
                                    @endif

                                    @if ($coupon->minimum_spend)
                                        <div class="detail-row">
                                            <div class="detail-label">🛒 Minimum Sepet</div>
                                            <div class="detail-value">{{ $coupon->minimum_spend->convertToCurrentCurrency()->format() }}</div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="coupon-details">
                                    <div class="detail-row">
                                        <div class="detail-label">🏷️ Kupon Adı</div>
                                        <div class="detail-value">{{ $coupon->name }}</div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="info-note">
                                <p>
                                    <strong>ℹ️ Bilgi:</strong> Kuponunuzun tüm detaylarını ve kullanım geçmişini 
                                    <a href="{{ route('account.coupons.index') }}" style="color: #2563eb; font-weight: 700;">Hesabım > Kuponlarım</a> 
                                    sayfasından görüntüleyebilirsiniz.
                                </p>
                                <div style="text-align: center; margin-top: 20px;">
                                    <a href="{{ route('account.coupons.index') }}" class="btn">Kuponlarımı Görüntüle</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <p>&copy; {{ date('Y') }} {{ setting('store_name') }}</p>
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
