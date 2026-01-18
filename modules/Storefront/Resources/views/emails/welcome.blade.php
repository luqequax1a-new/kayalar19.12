<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; background: #f1f5f9; }
        .main-wrapper { width: 100%; padding: 40px 15px; background-color: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        /* Preheader text for better inbox preview */
        .preheader { display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0; }

        .header { 
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%); 
            padding: 60px 30px; 
            text-align: center; 
            color: #fff; 
        }
        .header h1 { margin: 10px 0 0; font-size: 32px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 15px 0 0; font-size: 18px; opacity: 0.95; font-weight: 600; }

        .content { padding: 40px 35px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        .main-text { font-size: 16px; color: #475569; line-height: 1.8; margin-bottom: 30px; }
        
        .features { 
            display: table; 
            width: 100%; 
            margin: 30px 0; 
            border-top: 1px solid #f1f5f9; 
            padding-top: 30px;
        }
        .feature-item { 
            display: table-cell; 
            width: 33.33%; 
            padding: 10px; 
            text-align: center;
        }
        .feature-icon { 
            width: 48px; 
            height: 48px; 
            background: #f8fafc; 
            border-radius: 12px; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            margin-bottom: 12px;
            font-size: 20px;
        }
        .feature-title { font-weight: 700; color: #1e293b; font-size: 13px; margin-bottom: 5px; }
        .feature-desc { font-size: 11px; color: #64748b; }

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
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); 
        }
        
        .footer { padding: 40px 30px; text-align: center; background: #f8fafc; font-size: 12px; color: #94a3b8; }
        .footer-logo { max-height: 40px; margin-bottom: 20px; opacity: 0.6; }
        .footer-links { margin-bottom: 20px; }
        .footer-links a { color: #6366f1; text-decoration: none; margin: 0 10px; font-weight: 600; }

        @media screen and (max-width: 480px) {
            .feature-item { display: block; width: 100%; margin-bottom: 20px; }
            .header h1 { font-size: 26px; }
            .content { padding: 30px 20px; }
        }
    </style>
</head>
<body>
<span class="preheader">Aramıza katıldığın için çok mutluyuz! Sana özel fırsatları keşfetmeye hemen başla. 🤍</span>
<table class="main-wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <div class="container">
                <div class="header">
                    <span style="font-size: 48px;">✨</span>
                    <h1>Aramıza Hoş Geldin!</h1>
                    <p>{{ $heading }}</p>
                </div>

                <div class="content">
                    <div class="main-text">
                        {{ $text }}<br><br>
                        Seninle tanıştığımız için çok heyecanlıyız! 🤍<br>
                        {{ setting('store_name') }} ailesinin bir parçası olarak artık en yeni koleksiyonlardan, üyelere özel indirimlerden ve sürpriz fırsatlardan ilk sen haberdar olacaksın.
                    </div>

                    <div class="features">
                        <!--[if mso]>
                        <table role="presentation" width="100%">
                        <tr>
                        <td width="33%" align="center" valign="top">
                        <![endif]-->
                        <div class="feature-item">
                            <div class="feature-icon">🚀</div>
                            <div class="feature-title">Hızlı Teslimat</div>
                            <div class="feature-desc">Siparişlerin özenle hazırlanır ve jet hızıyla kapına gelir.</div>
                        </div>
                        <!--[if mso]>
                        </td>
                        <td width="33%" align="center" valign="top">
                        <![endif]-->
                        <div class="feature-item">
                            <div class="feature-icon">🛡️</div>
                            <div class="feature-title">Güvenli Alışveriş</div>
                            <div class="feature-desc">%100 güvenli ödeme sistemimizle için her zaman rahat olsun.</div>
                        </div>
                        <!--[if mso]>
                        </td>
                        <td width="33%" align="center" valign="top">
                        <![endif]-->
                        <div class="feature-item">
                            <div class="feature-icon">🎁</div>
                            <div class="feature-title">Size Özel</div>
                            <div class="feature-desc">Sadece üyelerimize özel tanımlanan sürpriz indirimleri kaçırma.</div>
                        </div>
                        <!--[if mso]>
                        </tr>
                        </table>
                        <![endif]-->
                    </div>
                </div>

                <div class="cta-wrapper">
                    @if(isset($action_url))
                        <a href="{{ $action_url }}" class="btn">Hemen Keşfetmeye Başla 🤍</a>
                    @else
                        <a href="{{ url('/') }}" class="btn">Mağazayı Geşfet 🤍</a>
                    @endif
                    <p style="margin-top: 25px; color: #64748b; font-size: 13px;">Sana özel teklifleri ve en yeni trendleri kaçırmamak için bizi takipte kal!</p>
                </div>

                <div class="footer">
                    <div style="font-weight: 800; font-size: 16px; color: #64748b; margin-bottom: 5px;">{{ setting('store_name') }}</div>
                    <div style="margin-bottom: 20px;">{{ setting('store_address') }}</div>
                    <div class="footer-links">
                        <a href="{{ url('/') }}">Mağazayı Ziyaret Et</a>
                        <a href="{{ url('/contact') }}">Destek Al</a>
                        @if(auth()->check())
                            <a href="{{ route('account.dashboard.index') }}">Hesabım</a>
                        @endif
                    </div>
                    <p>&copy; {{ date('Y') }} {{ setting('store_name') }}. Sevgiyle Tasarlandı. 🤍</p>
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
