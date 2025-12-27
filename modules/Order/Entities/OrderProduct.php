<?php

namespace Modules\Order\Entities;

use Modules\Support\Money;
use Modules\Support\Eloquent\Model;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Collection;
use Modules\Product\Entities\ProductVariant;


class OrderProduct extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['product', 'product_variant' ,'options', 'variations'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'qty' => 'decimal:2',
    ];


    public function url()
    {
        $slug = $this->product ? $this->product->slug : ($this->product_slug ?: null);
        return $slug ? route('products.show', ['slug' => $slug]) : '#';
    }


    public function hasAnyOption()
    {
        return $this->options->isNotEmpty();
    }


    public function hasAnyVariation()
    {
        return $this->variations->isNotEmpty();
    }


    /**
     * Determine if order product has been deleted.
     *
     * @return bool
     */
    public function trashed()
    {
        return $this->product ? false : true;
    }


    /**
     * Store order product's variations.
     *
     * @param Collection $variations
     *
     * @return void
     */
    public function storeVariations($variations)
    {
        collect($variations)->unique('id')->each(function ($variation) {
            $exists = \Illuminate\Support\Facades\DB::table('order_product_variations')
                ->where('order_product_id', $this->id)
                ->where('variation_id', $variation->id)
                ->exists();

            if ($exists) {
                return;
            }

            $orderProductVariation = $this->variations()->create([
                'order_product_id' => $this->id,
                'variation_id' => $variation->id,
                'type' => $variation->type,
                'value' => $variation->values->first()->label,
            ]);

            $orderProductVariation->storeValues($variation->values);
        });
    }


    public function variations()
    {
        return $this->hasMany(OrderProductVariation::class);
    }


    /**
     * Store order product's options.
     *
     * @param Collection $options
     *
     * @return void
     */
    public function storeOptions($options)
    {
        collect($options)->unique('id')->each(function ($option) {
            $exists = \Illuminate\Support\Facades\DB::table('order_product_options')
                ->where('order_product_id', $this->id)
                ->where('option_id', $option->id)
                ->exists();

            if ($exists) {
                return;
            }

            $orderProductOption = $this->options()->create([
                'order_product_id' => $this->id,
                'option_id' => $option->id,
                'value' => $option->isFieldType() ? $option->values->first()->label : null,
            ]);

            $orderProductOption->storeValues($this->product, $option->values);
        });
    }


    public function options()
    {
        return $this->hasMany(OrderProductOption::class);
    }


    public function product()
    {
        return $this->belongsTo(Product::class)
            ->withoutGlobalScope('active');
    }

    public function product_variant()
    {
        return $this->belongsTo(ProductVariant::class)
            ->withoutGlobalScope('active')
            ->withTrashed();
    }


    /**
     * Get the order product's name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->product ? $this->product->name : ($this->attributes['product_name'] ?? '');
    }


    /**
     * Get the order product's slug.
     *
     * @return string
     */
    public function getSlugAttribute()
    {
        return $this->product ? $this->product->slug : ($this->attributes['product_slug'] ?? '');
    }


    public function getUnitPriceAttribute($unitPrice)
    {
        return Money::inDefaultCurrency($unitPrice);
    }


    public function getLineTotalAttribute($total)
    {
        return Money::inDefaultCurrency($total);
    }


    /**
     * Get the order product's SKU.
     *
     * @return string
     */
    public function getSkuAttribute()
    {
        if ($this->product_variant) {
            return $this->product_variant->sku;
        }
        if ($this->product) {
            return $this->product->sku;
        }
        return $this->attributes['product_sku'] ?? '';
    }

    public function getUnitPriceAtOrderAttribute($value)
    {
        return $value === null ? null : Money::inDefaultCurrency($value);
    }

    public function getFormattedQuantityWithUnit(): string
    {
        $qty = $this->qty;

        if ($qty === null) {
            return '';
        }

        $value = rtrim(rtrim(number_format((float)$qty, 2, '.', ''), '0'), '.');
        $suffix = $this->unit_short_suffix ?: $this->unit_label;

        if (!$suffix) {
            return $value;
        }

        return trim($value . ' ' . $suffix);
    }
}
