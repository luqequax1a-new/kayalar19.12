<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yorumunuz için teşekkürler</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding: 20px 0 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background: linear-gradient(135deg, {{ mail_theme_color() }}, #0ea5e9);
            padding: 50px 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.025em;
            line-height: 1.3;
        }
        .header .subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 8px;
        }
        .content {
            padding: 40px 30px;
            color: #334155;
            line-height: 1.6;
            text-align: center;
        }
        .content p {
            margin: 0 0 16px;
            font-size: 15px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .coupon-box {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 30px 25px;
            margin: 30px 0;
            text-align: center;
        }
        .coupon-badge {
            display: inline-block;
            font-size: 12px;
            color: #075985;
            background: #e0f2fe;
            padding: 8px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: 700;
        }
        .coupon-label {
            font-size: 16px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .coupon-code-wrapper {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 16px 0;
        }
        .coupon-code {
            font-size: 32px;
            letter-spacing: 3px;
            color: #0f172a;
            font-weight: 800;
            font-family: 'Courier New', monospace;
        }
        .coupon-details {
            margin-top: 16px;
            font-size: 14px;
            color: #334155;
        }
        .coupon-detail-item {
            display: inline-block;
            margin: 0 12px;
            white-space: nowrap;
        }
        .btn {
            display: inline-block;
            background-color: {{ mail_theme_color() }};
            color: #ffffff !important;
            padding: 14px 32px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            text-align: center;
            margin: 25px 0 10px;
        }
        .footer {
            text-align: center;
            padding: 25px 20px;
            font-size: 13px;
            color: #94a3b8;
            background: #0f172a;
        }
        .footer a {
            color: #ffffff;
            text-decoration: none;
        }
        .footer-divider {
            margin: 0 8px;
            opacity: 0.5;
        }
        @media screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 30px 20px;
            }
            .header {
                padding: 40px 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .coupon-code {
                font-size: 24px;
                letter-spacing: 2px;
            }
            .coupon-detail-item {
                display: block;
                margin: 8px 0;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>❤️ Yorumunuz için teşekkürler</h1>
                <div class="subtitle">{{ setting('store_name') }}</div>
            </div>
            
            <div class="content">
                <div class="greeting">
                    {{ $order->customer_first_name }} {{ $order->customer_last_name }}, indirim kuponunuz hazır!
                </div>
                
                <p>
                    Geri bildiriminiz bizim için çok değerli. Yorumlarınız ürün ve hizmetlerimizi geliştirmemize yardımcı oluyor.
                </p>

                <div class="coupon-box">
                    <div class="coupon-badge">
                        ⚠️ Not: Kupon tek kullanımlıktır
                    </div>
                    
                    <div class="coupon-label">İndirim Kuponunuz</div>
                    
                    <div class="coupon-code-wrapper">
                        <div class="coupon-code">{{ $coupon->code }}</div>
                    </div>
                    
                    <div class="coupon-details">
                        <div class="coupon-detail-item">
                            🔖 İndirim: <strong>%{{ (int) ($coupon->value) }}</strong>
                        </div>
                        <div class="coupon-detail-item">
                            🗓️ Geçerlilik: <strong>{{ optional($coupon->end_date)->format('d.m.Y') }}</strong>
                        </div>
                    </div>
                </div>

                <a href="{{ route('home') }}" class="btn">
                    🛍️ Hemen Kullan
                </a>
                
                <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                    Kuponunuzu sepet sayfasında kullanabilirsiniz. İyi alışverişler! 🎉
                </p>
            </div>
            
            <div class="footer">
                <div style="margin-bottom: 10px; font-size: 14px; font-weight: 600;">
                    <a href="{{ route('home') }}">{{ setting('store_name') }}</a>
                </div>
                @if (setting('store_phone') && !setting('store_phone_hide'))
                    <a href="tel:{{ setting('store_phone') }}">{{ setting('store_phone') }}</a>
                @endif
                @if (setting('store_phone') && !setting('store_phone_hide') && setting('store_email') && !setting('store_email_hide'))
                    <span class="footer-divider">•</span>
                @endif
                @if (setting('store_email') && !setting('store_email_hide'))
                    <a href="mailto:{{ setting('store_email') }}">{{ setting('store_email') }}</a>
                @endif
                <div style="margin-top: 12px; opacity: 0.7; font-size: 12px;">
                    &copy; {{ date('Y') }} {{ setting('store_name') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
