<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stok Tükendi Uyarısı</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fef2f2;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-top: 6px solid #dc2626;
        }
        .header {
            padding: 30px;
            text-align: center;
            background-color: #ffffff;
        }
        .alert-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #991b1b;
            font-weight: 700;
        }
        .content {
            padding: 0 30px 30px;
            color: #4b5563;
            line-height: 1.6;
        }
        .product-details {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 5px;
        }
        .product-meta {
            font-size: 14px;
            color: #6b7280;
        }
        .btn {
            display: inline-block;
            background-color: #dc2626;
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 25px;
            text-align: center;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="alert-icon">⚠️</div>
            <h1>Stok Tükendi!</h1>
        </div>
        
        <div class="content">
            <p>Sayın Yönetici,</p>
            <p>Mağazanızdaki bir ürünün stoğu az önce tükendi. Satışların devam edebilmesi için stok durumunu güncellemenizi öneririz.</p>
            
            <div class="product-details">
                <div class="product-name">
                    {{ $product->name }}
                    @if($variantName)
                        <span style="color: #6b7280; font-size: 14px;">({{ $variantName }})</span>
                    @endif
                </div>
                <div class="product-meta">
                    <strong>Stok Kodu:</strong> {{ $product->sku }}<br>
                    <strong>Mevcut Durum:</strong> Stokta Yok
                </div>
            </div>
            
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn">Stok Güncelle</a>
        </div>
        
        <div class="footer">
            Bu otomatik bir sistem uyarısıdır. Lütfen bu e-postayı yanıtlamayınız.
        </div>
    </div>
</body>
</html>
