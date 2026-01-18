@php
    $topProducts = \Modules\Order\Entities\OrderProduct::whereHas('order', function($q) use ($user) {
            $q->where('customer_id', $user->id)
              ->whereNotIn('status', ['canceled', 'refunded']);
        })
        ->select(
            'product_id',
            'product_variant_id',
            'product_name',
            'product_image_path',
            'unit_label',
            'unit_short_suffix',
            \DB::raw('SUM(qty) as total_qty'),
            \DB::raw('SUM(line_total) as total_revenue'),
            \DB::raw('MAX(id) as last_order_product_id')
        )
        ->groupBy('product_id', 'product_variant_id', 'product_name', 'product_image_path', 'unit_label', 'unit_short_suffix')
        ->orderByDesc('total_qty')
        ->take(10)
        ->get();

    $maxQty = $topProducts->max('total_qty') ?: 1;
@endphp

<div class="premium-card">
    <div class="premium-card-header">
        <h3 class="premium-card-title">
            <div class="premium-icon-box" style="background: rgba(245, 158, 11, 0.08); color: #f59e0b;">
                <i class="fa fa-star"></i>
            </div>
            En Çok Satın Alınan Ürünler
        </h3>
        <span class="insight-badge">{{ $topProducts->count() }} Farklı Kalem</span>
    </div>
    
    <div class="box-body" style="padding: 0;">
        <div class="table-responsive dashboard-top-products-table">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th style="width: 55%;">Ürün</th>
                        <th style="width: 20%; text-align: right;">Adet</th>
                        <th style="width: 25%; text-align: right;">Ciro</th>
                    </tr>
                </thead>
                <tbody data-top-products-body>
                    @forelse ($topProducts as $item)
                        @php
                            $qty = (float) $item->total_qty;
                            $qtyValue = rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
                            $unitSuffix = $item->unit_short_suffix ?: $item->unit_label;
                            $qtyDisplay = $unitSuffix ? trim($qtyValue . ' ' . $unitSuffix) : $qtyValue;
                            
                            $pct = round(($qty / $maxQty) * 100);

                            $lastOp = \Modules\Order\Entities\OrderProduct::with(['product', 'product.files', 'product_variant', 'product_variant.files', 'variations', 'variations.values'])->find($item->last_order_product_id);
                            
                            // Build variant text
                            $variantText = '';
                            if ($lastOp && $lastOp->hasAnyVariation()) {
                                $variantParts = [];
                                foreach($lastOp->variations as $variation) {
                                    $val = $variation->values->first()->label ?? $variation->value ?? '';
                                    if ($val) $variantParts[] = $val;
                                }
                                $variantText = implode(' / ', $variantParts);
                            }

                            // Determine high quality image (Admin Hover Preview depends on src being high-res or original)
                            $file = null;
                            if ($lastOp) {
                                if ($lastOp->product_variant && $lastOp->product_variant->base_image && $lastOp->product_variant->base_image->exists) {
                                    $file = $lastOp->product_variant->base_image;
                                } elseif ($lastOp->product && $lastOp->product->base_image && $lastOp->product->base_image->exists) {
                                    $file = $lastOp->product->base_image;
                                }
                            }
                            $imageUrl = $file ? $file->path : null;
                        @endphp
                        <tr class="premium-row">
                            <td>
                                <div class="tp-row" style="display: flex; gap: 12px; align-items: center;">
                                    <div class="thumbnail-holder" style="width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0; background: #f8fafc; border: 1px solid #e2e8f0; cursor: default; position: relative;">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" 
                                                 style="width: 100%; height: 100%; object-fit: cover; display: block;"
                                                 alt="{{ $item->product_name }}">
                                        @else
                                            <i class="fa fa-picture-o" style="color: #cbd5e1; font-size: 20px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>
                                        @endif
                                    </div>

                                    <div class="tp-meta" style="min-width: 0; flex: 1;">
                                        <div class="tp-name" style="font-weight: 600; color: #0e1e3e; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: default;" title="{{ $item->product_name }}">
                                            {{ $item->product_name }}
                                        </div>
                                        @if($variantText)
                                            <div class="tp-variant" style="font-size: 11px; color: #64748b; margin-top: 1px; font-weight: 500; cursor: default;">
                                                {{ $variantText }}
                                            </div>
                                        @endif
                                        <div class="tp-bar" style="margin-top: 6px; height: 6px; background: #e8eef6; border-radius: 999px; overflow: hidden;">
                                            <span style="display: block; height: 100%; background: rgba(76, 201, 254, .9); width: {{ $pct }}%; border-radius: 999px;"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-right" style="vertical-align: middle;">
                                <span class="text-mono" style="font-weight: 800; color: #475569; font-size: 13px; cursor: default;">
                                    {{ $qtyDisplay }}
                                </span>
                            </td>
                            <td class="text-right" style="vertical-align: middle;">
                                <span class="text-mono" style="font-weight: 800; color: #0f172a; font-size: 14px; cursor: default;">
                                    {{ \Modules\Support\Money::inDefaultCurrency($item->total_revenue)->format() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center" style="padding: 50px 0; color: #94a3b8;">
                                <i class="fa fa-shopping-basket" style="display: block; font-size: 32px; margin-bottom: 10px; opacity: 0.2;"></i>
                                <span style="font-size: 13px; font-weight: 500;">Henüz satın alma verisi bulunmuyor.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .dashboard-top-products-table .premium-table td {
        padding: 12px 15px !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .tp-bar span {
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    /* Simple indicator for hoverable images */
    .thumbnail-holder img {
        transition: transform 0.2s ease;
    }
    .thumbnail-holder:hover img {
        transform: scale(1.05);
    }
</style>

<script>
    // Relying on global admin imageHoverPreview script
</script>
