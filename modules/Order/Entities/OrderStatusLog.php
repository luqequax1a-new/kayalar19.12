<?php

namespace Modules\Order\Entities;

use Modules\Support\Eloquent\Model;

class OrderStatusLog extends Model
{
    protected $table = 'order_status_logs';

    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
