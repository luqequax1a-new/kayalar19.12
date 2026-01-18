<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? trans('storefront::product.add_a_review') }}</title>
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
            font-size: 26px;
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
            font-size: 20px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 20px;
        }
        .highlight-text {
            font-weight: 600;
            color: #0f172a;
            font-size: 16px;
        }
        .product-list-block { 
            background: #f8fafc; 
            border-radius: 16px; 
            padding: 20px; 
            border: 1px solid #e2e8f0; 
            margin: 25px 0;
        }
        .product-item { 
            padding: 12px; 
            border: 1px solid #e5e7eb; 
            border-radius: 12px; 
            background: #ffffff;
            margin-bottom: 12px;
        }
        .product-item:last-child {
            margin-bottom: 0;
        }
        .product-image { 
            width: 70px; 
            height: 70px; 
            border-radius: 10px; 
            object-fit: cover; 
            border: 1px solid #e5e7eb; 
            display: block; 
        }
        .product-name { 
            font-weight: 700; 
            color: #0f172a; 
            font-size: 14px; 
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .product-sku { 
            font-size: 12px; 
            color: #64748b; 
            margin-top: 4px;
        }
        .info-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
        }
        .info-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }
        .info-item {
            margin: 8px 0;
            color: #334155;
            font-size: 14px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background-color: {{ mail_theme_color() }};
            color: #ffffff !important;
            padding: 14px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            text-align: center;
            margin: 20px 0;
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
                font-size: 22px;
            }
            .product-image {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                @if (!is_null($logo))
                    <div class="logo-wrapper">
                        <img src="{{ $logo }}" alt="{{ setting('store_name') }}">
                    </div>
                @endif
                <h1>{{ $title ?? trans('storefront::product.add_a_review') }}</h1>
            </div>
            
            <div class="content">
                <div class="greeting">
                    {{ trans('storefront::mail.hello', ['name' => $order->customer_first_name]) }}
                </div>
                
                <p>
                    {{ $intro ?? 'Siparişiniz elinize ulaştı. Deneyiminizi bizimle ve diğer müşterilerimizle paylaşır mısınız?' }}
                </p>
                
                <p class="highlight-text">
                    {{ $promo ?? 'Siparişiniz hakkında değerlendirme yapmayı unutmayın. Yorum bırakan müşterilerimize bir sonraki alışverişlerinde kullanmaları için özel indirim kuponu tanımlıyoruz.' }}
                </p>

                <div class="product-list-block">
                    @foreach ($order->products as $product)
                        @php
                            $imagePath = $product->product_variant?->base_image?->path
                                ?? $product->product?->base_image?->path
                                ?? $product->product_image_path;
                        @endphp
                        <div class="product-item">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="80" valign="top">
                                        @if ($imagePath)
                                            <img src="{{ $imagePath }}" alt="{{ $product->name }}" class="product-image">
                                        @endif
                                    </td>
                                    <td valign="top" style="padding-left: 12px;">
                                        <div class="product-name">{{ $product->name }}</div>
                                        @if ($product->sku)
                                            <div class="product-sku">Kod: {{ $product->sku }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                </div>

                <div class="info-box">
                    <div class="info-title">💡 Yorumunuz neden önemli?</div>
                    <div class="info-item">• Ürünlerimizin gelişimine katkı sağlarsınız</div>
                    <div class="info-item">• Diğer müşterilere karar verirken rehberlik edersiniz</div>
                    <div class="info-item">• Topluluğumuzun güvenilirliğine katkıda bulunursunuz</div>
                    
                    <div style="margin-top: 24px; padding: 20px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; border: 2px dashed #f59e0b;">
                        <div style="text-align: center; margin-bottom: 12px; font-size: 32px;">🎁</div>
                        <div class="info-title" style="text-align: center; color: #92400e; margin-bottom: 12px;">Size güzel haberimiz var!</div>
                        <div style="font-size: 14px; color: #78350f; text-align: center; line-height: 1.6;">
                            Yorum yaparsanız <strong>%{{ setting('review_coupon_discount_percent', 10) }} indirim kuponu</strong> kazanırsınız! 🎉<br>
                            <span style="font-size: 13px; opacity: 0.9;">Kuponunuz {{ setting('review_coupon_valid_days', 30) }} gün boyunca geçerli olacak.</span>
                        </div>
                    </div>
                </div>

                @php
                    $firstProduct = $order->products->first();
                    $productUrl = $firstProduct ? $firstProduct->url() : null;
                    $targetReviewUrl = ($productUrl && $productUrl !== '#')
                        ? ($productUrl . '?order_id=' . $order->id . '#reviews')
                        : route('home');
                @endphp

                <div style="text-align: center;">
                    <a href="{{ $targetReviewUrl }}" class="btn">
                        ⭐ Ürünlerimi Değerlendir
                    </a>
                </div>
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
                    &copy; {{ date('Y') }} {{ trans('storefront::mail.all_rights_reserved') }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
