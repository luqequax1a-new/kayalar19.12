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
        
        .header { 
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); 
            padding: 60px 30px; 
            text-align: center; 
            color: #fff; 
        }
        .header h1 { margin: 10px 0 0; font-size: 28px; font-weight: 800; letter-spacing: -0.5px; }
        .header p { margin: 15px 0 0; font-size: 16px; opacity: 0.9; }

        .content { padding: 40px 35px; text-align: center; }
        .main-text { font-size: 16px; color: #475569; line-height: 1.8; margin-bottom: 30px; }
        
        .code-box { 
            background: #f8fafc; 
            border: 2px dashed #e2e8f0; 
            border-radius: 16px; 
            padding: 30px; 
            margin: 30px 0; 
            display: inline-block;
            min-width: 200px;
        }
        .code {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 15px;
            color: #6366f1;
            margin: 0;
            padding-left: 15px; /* offset for letter-spacing */
        }

        .footer { padding: 40px 30px; text-align: center; background: #f8fafc; font-size: 12px; color: #94a3b8; }
        .footer-links { margin-bottom: 20px; }
        .footer-links a { color: #6366f1; text-decoration: none; margin: 0 10px; font-weight: 600; }

        .expire-note {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<table class="main-wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <div class="container">
                <div class="header">
                    <span style="font-size: 48px;">🛡️</span>
                    <h1>Güvenlik Doğurulaması</h1>
                    <p>Yönetici paneline giriş yapmak için bu kodu kullanın.</p>
                </div>

                <div class="content">
                    <div class="main-text">
                        Merhaba {{ $user->first_name }},<br><br>
                        Hesabınızın güvenliği için iki faktörlü doğrulama etkinleştirildi. Lütfen aşağıdaki giriş kodunu ilgili alana girerek işleminizi tamamlayın.
                    </div>

                    <div class="code-box">
                        <div class="code">{{ $code }}</div>
                    </div>

                    <div class="expire-note">
                        Bu kod 10 dakika boyunca geçerlidir.
                    </div>
                </div>

                <div class="footer">
                    <div style="font-weight: 800; font-size: 16px; color: #64748b; margin-bottom: 5px;">{{ setting('store_name') }}</div>
                    <div style="margin-bottom: 20px;">{{ setting('store_address') }}</div>
                    <div class="footer-links">
                        <a href="{{ url('/') }}">Mağazayı Ziyaret Et</a>
                        <a href="{{ url('/contact') }}">Destek Al</a>
                    </div>
                    <p>&copy; {{ date('Y') }} {{ setting('store_name') }}. Güvenle Hazırlandı. 🛡️</p>
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
