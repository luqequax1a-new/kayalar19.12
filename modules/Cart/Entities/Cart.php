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
    ];


    public function getDataAttribute($value)
    {
        return unserialize($value);
    }


    public function setDataAttribute($value)
    {
        $this->attributes['data'] = serialize($value);
    }
}
