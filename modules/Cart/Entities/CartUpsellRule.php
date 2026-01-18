<?php

namespace Modules\Cart\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

class CartUpsellRule extends Model
{
    protected $table = 'cart_upsell_rules';

    protected $fillable = [
        'status',
        'trigger_type',
        'main_product_id',
        'main_category_id',
        'upsell_product_id',
        'preselected_variant_id',
        'discount_type',
        'discount_value',
        'discount_base_price',
        'title',
        'subtitle',
        'description',
        'internal_name',
        'show_on',
        'min_cart_total',
        'max_cart_total',
        'hide_if_already_in_cart',
        'exclude_discounted_products',
        'has_countdown',
        'countdown_minutes',
        'starts_at',
        'ends_at',
        'sort_order',
        'usage_limit',
        'times_shown',
        'times_added_to_cart',
        'times_purchased',
        'total_revenue',
    ];

    protected $casts = [
        'status' => 'boolean',
        'discount_value' => 'decimal:4',
        'min_cart_total' => 'decimal:4',
        'max_cart_total' => 'decimal:4',
        'hide_if_already_in_cart' => 'boolean',
        'exclude_discounted_products' => 'boolean',
        'has_countdown' => 'boolean',
        'countdown_minutes' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'title' => 'array',
        'subtitle' => 'array',
        'description' => 'array',
    ];

    public function mainCategory()
    {
        return $this->belongsTo(\Modules\Category\Entities\Category::class, 'main_category_id');
    }

    public function mainProduct()
    {
        return $this->belongsTo(Product::class, 'main_product_id');
    }

    public function upsellProduct()
    {
        return $this->belongsTo(Product::class, 'upsell_product_id');
    }

    public function preselectedVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'preselected_variant_id');
    }

    public function offers()
    {
        return $this->hasMany(CartUpsellOffer::class, 'rule_id')->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeForPlacement($query, string $placement)
    {
        return $query->where('show_on', $placement);
    }

    public function scopeWithinDateRange($query)
    {
        $now = now();

        return $query
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }
}
