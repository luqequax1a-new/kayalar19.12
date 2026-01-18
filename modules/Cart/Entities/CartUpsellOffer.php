<?php

namespace Modules\Cart\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

class CartUpsellOffer extends Model
{
    protected $table = 'cart_upsell_offers';

    protected $fillable = [
        'rule_id',
        'order',
        'trigger',
        'product_id',
        'variant_id',
        'discount_type',
        'discount_value',
        'discount_base_price',
        'title',
        'subtitle',
        'hide_if_in_cart',
    ];

    protected $casts = [
        'discount_value' => 'decimal:4',
        'title' => 'array',
        'subtitle' => 'array',
        'order' => 'integer',
        'hide_if_in_cart' => 'boolean',
    ];

    public function rule()
    {
        return $this->belongsTo(CartUpsellRule::class, 'rule_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
