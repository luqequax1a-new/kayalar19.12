<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('product::mail.back_in_stock.subject') }}</title>
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
            padding-bottom: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 40px 30px;
            color: #334155;
            line-height: 1.6;
        }
        .content p {
            margin: 0 0 20px;
            font-size: 16px;
        }
        .product-list-block { 
            background: #f8fafc; 
            border-radius: 20px; 
            padding: 25px; 
            border: 1px solid #e2e8f0; 
            margin-bottom: 30px;
        }
        .product-item { padding: 10px 0; }
        .product-image { 
            width: 85px; 
            height: 85px; 
            border-radius: 12px; 
            object-fit: cover; 
            border: 1px solid #e2e8f0; 
            display: block; 
            background: #fff; 
        }
        .product-name { 
            font-weight: 700; 
            color: #1e293b; 
            font-size: 15px; 
            margin-bottom: 4px; 
            display: block; 
            text-decoration: none; 
        }
        .product-meta { font-size: 12px; color: #64748b; line-height: 1.5; }
        
        .btn-wrap { margin-top: 15px; }
        .btn {
            display: inline-block;
            background-color: #d97706;
            color: #ffffff !important;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            text-align: center;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #94a3b8;
        }
        @media screen and (max-width: 600px) {
            .container {
                margin-top: 0;
                border-radius: 0;
            }
            .product-image {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>
@php
    $storeName = setting('store_name');
    
    // Pick the best image (Variant base image first, then product base image)
    $baseImage = ($variant && $variant->base_image->exists) ? $variant->base_image : $product->base_image;
    $imageUrl = $baseImage->path ?? url('build/assets/image-placeholder.png');
    
    if (!str_starts_with($imageUrl, 'http')) {
        $imageUrl = Storage::url($imageUrl);
    }
    
    $productUrl = $variant ? $variant->url() : route('products.show', $product->slug);
    $sku = $variant ? ($variant->sku ?: $product->sku) : $product->sku;
@endphp
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Beklediğiniz Ürün Tekrardan Stokta! 🥳</h1>
            </div>
            
            <div class="content">
                <p>Merhaba,</p>
                <p>Beklediğiniz o haber geldi! Takip ettiğiniz <strong>{{ $product->name }}</strong> ürünü sitemizde tekrar stoklara girdi. 🥳</p>
                
                <div class="product-list-block">
                    <div class="product-item">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="95" valign="top">
                                    <a href="{{ $productUrl }}" target="_blank">
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="product-image">
                                    </a>
                                </td>
                                <td valign="top" style="padding-left: 15px;">
                                    <a href="{{ $productUrl }}" target="_blank" class="product-name">{{ $product->name }}</a>
                                    <div class="product-meta">
                                        <div style="margin-bottom: 2px;">Stok Kodu: {{ $sku }}</div>
                                        
                                        @if($variant)
                                @foreach($variant->getVariationLabels() as $name => $label)
                                    <div style="margin-bottom: 2px; color: #1e293b; font-weight: 600;">{{ $name }}: {{ $label }}</div>
                                @endforeach
                                        @endif

                                        <div class="btn-wrap">
                                            <a href="{{ $productUrl }}" class="btn">Hemen İncele 🛍️</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <p>Ürünlerimizin hızlıca tükenebileceğini hatırlatmak isteriz. Eğer hala ilgileniyorsanız, fırsatı kaçırmadan sepetinize ekleyebilirsiniz.</p>
                <p>Keyifli alışverişler dileriz! ❤️</p>
            </div>
            
            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ $storeName }}. Tüm hakları saklıdır.</p>
                <p>Bu e-postayı, ürün stoğa geldiğinde haber verilmesini istediğiniz için aldınız.</p>
            </div>
        </div>
    </div>
</body>
</html>
