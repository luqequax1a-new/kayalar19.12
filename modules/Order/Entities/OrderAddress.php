<?php

namespace Modules\Order\Entities;

use Modules\Support\Eloquent\Model;

class OrderAddress extends Model
{
    protected $table = 'order_addresses';

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
