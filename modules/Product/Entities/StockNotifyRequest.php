<?php

namespace Modules\Product\Entities;

use Modules\Support\Eloquent\Model;
use Modules\Product\Entities\ProductVariant;

class StockNotifyRequest extends Model
{
    protected $table = 'stock_notify_requests';

    protected $fillable = [
        'product_id',
        'variant_id',
        'email',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
