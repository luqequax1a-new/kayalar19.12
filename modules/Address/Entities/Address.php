<?php

namespace Modules\Address\Entities;

use Modules\Support\State;
use Modules\Support\Country;
use Modules\User\Entities\User;
use Modules\Support\Eloquent\Model;

class Address extends Model
{
    protected static $cities = null;
    protected static $districts = null;
    public const TYPE_SHIPPING = 'shipping';
    public const TYPE_BILLING = 'billing';

    protected $fillable = [
        'type',
        'address_title',
        'customer_id',
        'user_id',
        'first_name',
        'last_name',
        'company_name',
        'tax_number',
        'tax_office',
        'address_1',
        'address_2',
        'city',
        'state',
        'zip',
        'country',
        'phone',
        'address_line',
        'city_id',
        'district_id',
        'invoice_title',
        'invoice_tax_number',
        'invoice_tax_office',
        'billing_email',
    ];

    protected $appends = ['full_name', 'state_name', 'country_name', 'city_title', 'district_title'];


    public function customer()
    {
        return $this->belongsTo(User::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ordersShipping()
    {
        return $this->hasMany(\Modules\Order\Entities\Order::class, 'shipping_address_id');
    }

    public function ordersBilling()
    {
        return $this->hasMany(\Modules\Order\Entities\Order::class, 'billing_address_id');
    }


    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }


    public function getStateNameAttribute()
    {
        return State::name($this->country, $this->state);
    }


    public function getCountryNameAttribute()
    {
        return Country::name($this->country);
    }

    public function getCityTitleAttribute()
    {
        $name = $this->city;
        if ($name === null || $name === '') {
            $id = $this->city_id;
            if (!$id) return $name;
            $title = $this->resolveCityNameById($id);
            return $this->titleCaseTR($title);
        }
        return $this->titleCaseTR($name);
    }

    public function getDistrictTitleAttribute()
    {
        $id = $this->district_id;
        if (!$id) return null;
        $title = $this->resolveDistrictNameById($id);
        return $this->titleCaseTR($title);
    }

    protected function resolveCityNameById($id)
    {
        if (self::$cities === null) {
            $path = base_path('sehirler.json');
            self::$cities = is_file($path) ? json_decode(file_get_contents($path), true) : [];
        }
        foreach (self::$cities as $c) {
            if ((string)($c['sehir_id'] ?? '') === (string)$id) {
                return $c['sehir_adi'] ?? null;
            }
        }
        return null;
    }

    protected function resolveDistrictNameById($id)
    {
        if (self::$districts === null) {
            $path = base_path('ilceler.json');
            self::$districts = is_file($path) ? json_decode(file_get_contents($path), true) : [];
        }
        foreach (self::$districts as $d) {
            if ((string)($d['ilce_id'] ?? '') === (string)$id) {
                return $d['ilce_adi'] ?? null;
            }
        }
        return null;
    }

    protected function titleCaseTR($name)
    {
        if ($name === null || $name === '') {
            return $name;
        }
        $mapUpperToLower = [
            'I' => 'ı', 'İ' => 'i', 'Ç' => 'ç', 'Ş' => 'ş', 'Ğ' => 'ğ', 'Ü' => 'ü', 'Ö' => 'ö',
        ];
        $s = strtr($name, $mapUpperToLower);
        if (function_exists('mb_strtolower')) {
            $s = mb_strtolower($s, 'UTF-8');
        } else {
            $s = strtolower($s);
        }
        $parts = preg_split('/([\s\-]+)/u', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
        $mapLowerToUpper = [
            'i' => 'İ', 'ı' => 'I', 'ç' => 'Ç', 'ş' => 'Ş', 'ğ' => 'Ğ', 'ü' => 'Ü', 'ö' => 'Ö',
        ];
        $res = '';
        foreach ($parts as $idx => $p) {
            if ($idx % 2 === 0) {
                if ($p === '') { $res .= $p; continue; }
                if (function_exists('mb_substr')) {
                    $first = mb_substr($p, 0, 1, 'UTF-8');
                    $rest = mb_substr($p, 1, null, 'UTF-8');
                } else {
                    $first = substr($p, 0, 1);
                    $rest = substr($p, 1);
                }
                if (function_exists('mb_strtoupper')) {
                    $firstU = $mapLowerToUpper[$first] ?? mb_strtoupper($first, 'UTF-8');
                } else {
                    $firstU = $mapLowerToUpper[$first] ?? strtoupper($first);
                }
                $res .= $firstU . $rest;
            } else {
                $res .= $p;
            }
        }
        return $res;
    }
}
