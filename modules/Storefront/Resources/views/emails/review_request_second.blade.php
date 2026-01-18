<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
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
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .logo-wrapper {
            display: flex;
            height: 50px;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .logo-wrapper img {
            max-height: 100%;
            max-width: 180px;
        }
        .content {
            padding: 40px 30px;
            color: #334155;
            line-height: 1.6;
        }
        .content p {
            margin: 0 0 16px;
            font-size: 15px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .highlight-text {
            font-weight: 600;
            color: #0f172a;
            font-size: 15px;
            background: #fef3c7;
            padding: 12px 16px;
            border-radius: 10px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
        }
        .product-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin: 25px 0;
        }
        .product-image { 
            width: 80px; 
            height: 80px; 
            border-radius: 10px; 
            object-fit: cover; 
            border: 1px solid #e2e8f0; 
            display: block;
            background: #ffffff;
        }
        .product-name { 
            font-weight: 700; 
            color: #0f172a; 
            font-size: 16px; 
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .btn {
            display: inline-block;
            background-color: {{ mail_theme_color() }};
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            text-align: center;
            margin-top: 12px;
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
            .header h1 {
                font-size: 20px;
            }
            .product-image {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                @if (!empty($logo))
                    <div class="logo-wrapper">
                        <img src="{{ $logo }}" alt="{{ setting('store_name') }}">
                    </div>
                @endif
                <h1>{{ $title }}</h1>
            </div>
            
            <div class="content">
                <div class="greeting">
                    Merhaba {{ $order->customer_first_name }} {{ $order->customer_last_name }},
                </div>
                
                <p>
                    Bir süre önce siparişinizle ilgili bir değerlendirme daveti göndermiştik. Yoğunlukta gözünüzden kaçmış olabilir diye nazikçe tekrar hatırlatmak istedik. 😊
                </p>
                
                <div class="highlight-text">
                    💬 Deneyiminiz hem bizim için hem de diğer müşterilerimiz için çok değerli.
                </div>

                @php 
                    $firstProduct = $order->products->first(); 
                @endphp
                
                @if ($firstProduct && $firstProduct->product)
                    <div class="product-box">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="90" valign="top">
                                    @if ($firstProduct->product->base_image)
                                        <img src="{{ $firstProduct->product->base_image->path }}" alt="{{ $firstProduct->product->name }}" class="product-image">
                                    @endif
                                </td>
                                <td valign="top" style="padding-left: 15px;">
                                    <div class="product-name">{{ $firstProduct->product->name }}</div>
                                    <a href="{{ $firstProduct->product->url() }}#reviews?order_id={{ $order->id }}" class="btn">
                                        ⭐ Ürünü Değerlendir
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif

                <p style="margin-top: 25px;">
                    Değerlendirmeniz için teşekkür ederiz! 🙏
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
