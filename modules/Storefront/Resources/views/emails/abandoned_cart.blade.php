<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; background: #f1f5f9; }
        .main-wrapper { width: 100%; padding: 40px 0; background-color: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        .header { 
            background: linear-gradient(135deg, #ec4899 0%, #f472b6 61.8%, #fbbf24 100%); 
            padding: 50px 30px; 
            text-align: center; 
            color: #fff; 
        }
        .header h1 { margin: 10px 0 0; font-size: 28px; font-weight: 800; }
        .header p { margin: 10px 0 0; font-size: 16px; opacity: 0.9; }

        .section { padding: 35px; border-bottom: 1px solid #f1f5f9; }
        .section-title { font-size: 18px; font-weight: 800; margin-bottom: 20px; color: #0f172a; text-align: center; }
        
        .product-list { margin-bottom: 30px; }
        .product-item { padding: 15px 0; border-bottom: 1px solid #f1f5f9; }
        .product-item:last-child { border-bottom: none; }
        .product-item.upsell-item { background: linear-gradient(135deg, #fef3c7 0%, #fef9e7 100%); border-left: 4px solid #f59e0b; padding-left: 12px; border-radius: 8px; }
        .product-image { width: 80px; height: 80px; border-radius: 12px; object-fit: cover; border: 1px solid #f1f5f9; }
        .product-name { font-weight: 700; color: #1e293b; font-size: 14px; display: block; text-decoration: none; margin-bottom: 5px; }
        .product-meta { font-size: 12px; color: #64748b; }
        .upsell-badge { display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 12px; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .price-original { color: #94a3b8; font-size: 12px; text-decoration: line-through; margin-right: 6px; }
        .price-upsell { color: #10b981; font-weight: 800; }

        .coupon-box { background: #fef2f2; border: 2px dashed #f87171; border-radius: 16px; padding: 25px; text-align: center; margin: 20px 0; }
        .coupon-title { font-weight: 700; color: #991b1b; font-size: 16px; margin-bottom: 10px; }
        .coupon-code { display: inline-block; background: #fff; padding: 10px 25px; border-radius: 10px; font-family: monospace; font-size: 24px; font-weight: 800; color: #b91c1c; border: 1px solid #fee2e2; margin: 10px 0; }
        .coupon-hint { font-size: 12px; color: #ef4444; margin-top: 10px; }

        .cta-wrapper { text-align: center; padding: 40px 30px; }
        .btn { 
            background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%); 
            color: #fff !important; 
            padding: 18px 40px; 
            border-radius: 50px; 
            text-decoration: none; 
            font-weight: 800; 
            font-size: 16px; 
            display: inline-block; 
            box-shadow: 0 10px 20px rgba(236, 72, 153, 0.3); 
        }
        
        .footer { padding: 40px 30px; text-align: center; background: #f8fafc; font-size: 12px; color: #94a3b8; }
        .footer-logo { max-height: 40px; margin-bottom: 20px; opacity: 0.6; }
        .footer-links { margin-bottom: 20px; }
        .footer-links a { color: #ec4899; text-decoration: none; margin: 0 10px; font-weight: 600; }
    </style>
</head>
<body>
<table class="main-wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <div class="container">
                <div class="header">
                    <h1 style="display: flex; align-items: center; justify-content: center; gap: 12px; margin: 0;">
                        <span style="font-size: 36px;">🤍</span>
                        <span>Unuttuğun Bir Şeyler Var!</span>
                    </h1>
                    <p>Sepetindeki ürünler seni bekliyor. Seçtiğin harika parçaları kaçırmanı istemeyiz.</p>
                </div>

                <div class="section">
                    <div class="section-title">Sepetindeki Ürünler</div>
                    <div class="product-list">
                        @foreach($cart->data as $item)
                            @php
                                $cartItem = new \Modules\Cart\CartItem($item);
                                $prod = $cartItem->product;
                                $vari = $cartItem->variant;
                                $isUpsell = !empty($cartItem->upsell) && isset($cartItem->upsell['is_upsell']) && $cartItem->upsell['is_upsell'];
                                
                                $image = optional($vari?->base_image)->path 
                                    ?? optional($prod?->base_image)->path 
                                    ?? null;
                            @endphp
                            <div class="product-item {{ $isUpsell ? 'upsell-item' : '' }}">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="80" valign="top">
                                            @if($image)
                                                <img src="{{ $image }}" class="product-image">
                                            @else
                                                <div style="width:80px; height:80px; background:#f1f5f9; border-radius:12px;"></div>
                                            @endif
                                        </td>
                                        <td valign="top" style="padding-left: 15px;">
                                            @if($isUpsell)
                                                <span class="upsell-badge">🎁 Sepet Teklifi</span><br>
                                            @endif
                                            <span class="product-name">{{ $item->name }}</span>
                                            
                                            @if($cartItem->variations->isNotEmpty())
                                                @foreach($cartItem->variations as $variation)
                                                    <div class="product-meta" style="margin-top: 5px; color: #1e293b; font-weight: 600;">
                                                        {{ $variation->name }}: {{ $variation->values->pluck('label')->implode(', ') }}
                                                    </div>
                                                @endforeach
                                            @elseif($vari && $vari->name)
                                                <div class="product-meta" style="margin-top: 5px; color: #1e293b; font-weight: 600;">
                                                    {{ $vari->name }}
                                                </div>
                                            @endif

                                            <div class="product-meta" style="margin-top: 4px;">
                                                @php
                                                    $qty = (float)($item->quantity ?? 0);
                                                    // Get unit suffix from product model appends/relations
                                                    $unitSuffix = $prod ? ($prod->unit_suffix ?: $prod->unit_label) : 'Adet';
                                                    $formattedQty = fmod($qty, 1) === 0.0 ? (int)$qty : $qty;
                                                @endphp
                                                @if($isUpsell && isset($cartItem->upsell['original_price']) && $cartItem->upsell['original_price'] > 0)
                                                    <span class="price-original">{{ \Modules\Support\Money::inDefaultCurrency($cartItem->upsell['original_price'])->format() }}</span>
                                                @endif
                                                {{ $formattedQty }} {{ $unitSuffix ?: 'Adet' }} x {{ $cartItem->unitPrice()->format() }}
                                            </div>
                                        </td>
                                        <td align="right" valign="top" style="font-weight: 700; white-space: nowrap;" class="{{ $isUpsell ? 'price-upsell' : '' }}">
                                            {{ $cartItem->totalPrice()->format() }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        @endforeach
                    </div>

                    @if($coupon)
                        @php
                            $val = is_numeric($coupon->value) ? $coupon->value : (method_exists($coupon->value, 'amount') ? $coupon->value->amount() : $coupon->value);
                        @endphp
                        <div class="coupon-box">
                            <div class="coupon-title">🎁 Sana Özel Sürpriz İndirim!</div>
                            <p style="margin: 0; color: #b91c1c; font-size: 14px;">Alışverişini tamamlaman için sana özel <b>%{{ (int)$val }}</b> indirim tanımladık.</p>
                            <div class="coupon-code">{{ $coupon->code }}</div>
                            <div class="coupon-hint">* Bu kod {{ (int)setting('abandoned_cart_coupon_valid_days', 3) }} gün boyunca geçerlidir.</div>
                        </div>
                    @endif
                </div>

                <div class="cta-wrapper">
                    <a href="{{ $cartUrl }}" class="btn">Alışverişi Tamamla</a>
                    <p style="margin-top: 20px; color: #64748b; font-size: 13px;">Seçtiğin harika ürünler seni bekliyor! Stoklar tükenmeden tamamlamanı isteriz. 💝</p>
                </div>

                <div class="footer">
                    <div style="font-weight: 800; font-size: 16px; color: #64748b; margin-bottom: 5px;">{{ setting('store_name') }}</div>
                    <div style="margin-bottom: 20px;">{{ setting('store_address') }}</div>
                    <div class="footer-links">
                        <a href="{{ url('/') }}">Mağazayı Ziyaret Et</a>
                        <a href="{{ url('/contact') }}">İletişim</a>
                    </div>
                    <p>&copy; {{ date('Y') }} {{ setting('store_name') }}. Tüm hakları saklıdır.</p>
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
