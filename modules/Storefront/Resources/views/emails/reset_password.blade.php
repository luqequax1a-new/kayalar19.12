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
        
        .url-box { 
            background: #f8fafc; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            padding: 20px; 
            margin: 30px 0; 
            word-break: break-all;
            font-size: 13px;
            color: #64748b;
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
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); 
        }
        
        .footer { padding: 40px 30px; text-align: center; background: #f8fafc; font-size: 12px; color: #94a3b8; }
        .footer-links { margin-bottom: 20px; }
        .footer-links a { color: #6366f1; text-decoration: none; margin: 0 10px; font-weight: 600; }

        @media screen and (max-width: 480px) {
            .header h1 { font-size: 26px; }
            .content { padding: 30px 20px; }
        }
    </style>
</head>
<body>
<span class="preheader">Şifrenizi sıfırlamak için bu güvenli bağlantıyı kullanın. 🛡️</span>
<table class="main-wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <div class="container">
                <div class="header">
                    <span style="font-size: 48px;">🔐</span>
                    <h1>Şifreni mi Unuttun?</h1>
                    <p>Endişelenme, senin için buradayız!</p>
                </div>

                <div class="content">
                    <div class="main-text">
                        Merhaba {{ $user->first_name }},<br><br>
                        Görünüşe göre şifreni sıfırlamak için bir talepte bulundun. Güvenliğin bizim için her şeyden önemli. Aşağıdaki butona tıklayarak yeni şifreni hemen oluşturabilirsin.
                    </div>

                    <div style="text-align:center; padding: 20px 0;">
                        <a href="{{ $url }}" class="btn">Şifremi Yenile</a>
                    </div>

                    <div class="url-box">
                        <strong>Bağlantı çalışmıyor mu?</strong><br>
                        Aşağıdaki linki kopyalayıp tarayıcının adres çubuğuna yapıştırabilirsin:<br><br>
                        <a href="{{ $url }}" style="color: #6366f1; text-decoration: none;">{{ $url }}</a>
                    </div>
                </div>

                <div class="cta-wrapper">
                    <p style="margin: 0; color: #64748b; font-size: 13px;">Bu işlemi sen yapmadıysan bu e-postayı güvenle görmezden gelebilirsin. Şifren güvende kalacaktır.</p>
                </div>

                <div class="footer">
                    <div style="font-weight: 800; font-size: 16px; color: #64748b; margin-bottom: 5px;">{{ setting('store_name') }}</div>
                    <div style="margin-bottom: 20px;">{{ setting('store_address') }}</div>
                    <div class="footer-links">
                        <a href="{{ url('/') }}">Mağazayı Ziyaret Et</a>
                        <a href="{{ url('/contact') }}">Destek Al</a>
                        <a href="{{ url('/login') }}">Giriş Yap</a>
                    </div>
                    <p>&copy; {{ date('Y') }} {{ setting('store_name') }}. Güvenle Hazırlandı. 🛡️</p>
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
