<?php

namespace Modules\Address\Entities;

use Illuminate\Database\Eloquent\Model;

class DefaultAddress extends Model
{
    public $timestamps = false;
    protected $with = ['shippingAddress', 'billingAddress'];
    protected $fillable = [
        'customer_id',
        'default_shipping_address_id',
        'default_billing_address_id',
    ];


    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'default_shipping_address_id');
    }


    public function billingAddress()
    {
        return $this->belongsTo(Address::class, 'default_billing_address_id');
    }


    public function getAddressIdAttribute()
    {
        return $this->default_shipping_address_id;
    }


    public function getAddressAttribute()
    {
        return $this->shippingAddress;
    }


    public function getAddress1Attribute()
    {
        return $this->address?->address_1;
    }


    public function getAddress2Attribute()
    {
        return $this->address?->address_1;
    }


    public function getCityAttribute()
    {
        return $this->address?->city;
    }


    public function getStateAttribute()
    {
        return $this->address?->state;
    }


    public function getZipAttribute()
    {
        return $this->address?->zip;
    }


    public function getCountryAttribute()
    {
        return $this->address?->country;
    }
}
