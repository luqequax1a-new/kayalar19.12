<?php

namespace Modules\Cart\Entities;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'data',
        'user_id',
        'customer_email',
        'customer_first_name',
        'customer_last_name',
        'customer_phone',
        'is_recovered',
        'recovered_at',
        'order_id',
        'is_clicked',
        'clicked_at',
        'last_notified_at',
        'reminder_count',
        'superseded_at',
        'recovered_by_email',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_recovered' => 'boolean',
        'is_clicked' => 'boolean',
        'recovered_at' => 'datetime',
        'clicked_at' => 'datetime',
        'last_notified_at' => 'datetime',
        'superseded_at' => 'datetime',
        'reminder_count' => 'integer',
    ];


    public function getDataAttribute($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $data = @unserialize($value, ['allowed_classes' => true]);

            if ($data === false && $value !== 'b:0;') {
                return [];
            }

            return $data;
        } catch (\Throwable $e) {
            return [];
        }
    }


    public function setDataAttribute($value)
    {
        $this->attributes['data'] = serialize($value);
    }

    /**
     * Get the order associated with this cart.
     */
    public function order()
    {
        return $this->belongsTo(\Modules\Order\Entities\Order::class, 'order_id');
    }
}
