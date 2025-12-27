<?php

namespace Modules\Cart\Storages;

use Modules\Cart\Entities\Cart;
use Darryldecode\Cart\CartCollection;
use Illuminate\Support\Facades\Session;

class Database
{
    private static array $rowCache = [];

    private function normalizeKey(string $key): string
    {
        $sessionId = Session::getId();

        if (str_ends_with($key, '_cart_items')) {
            return $sessionId . '_cart_items';
        }

        if (str_ends_with($key, '_cart_conditions')) {
            return $sessionId . '_cart_conditions';
        }

        return $key;
    }

    public function get($key)
    {
        $normalizedKey = $this->normalizeKey($key);

        if (!array_key_exists($normalizedKey, self::$rowCache)) {
            self::$rowCache[$normalizedKey] = Cart::find($normalizedKey);
        }

        $row = self::$rowCache[$normalizedKey];

        if ($row) {
            return new CartCollection($row->data);
        }

        return [];
    }

    public function put($key, $value)
    {
        $normalizedKey = $this->normalizeKey($key);

        if ($row = Cart::find($normalizedKey)) {
            $row->data = $value;
            $row->user_id = auth()->id();
            
            if (auth()->check()) {
                $row->customer_email = auth()->user()->email;
                $row->customer_first_name = auth()->user()->first_name;
                $row->customer_last_name = auth()->user()->last_name;
                $row->customer_phone = auth()->user()->phone;
            }
            
            $row->save();
        } else {
            $data = [
                'id' => $normalizedKey,
                'data' => $value,
                'user_id' => auth()->id(),
            ];

            if (auth()->check()) {
                $data['customer_email'] = auth()->user()->email;
                $data['customer_first_name'] = auth()->user()->first_name;
                $data['customer_last_name'] = auth()->user()->last_name;
                $data['customer_phone'] = auth()->user()->phone;
            }

            Cart::create($data);
        }

        unset(self::$rowCache[$normalizedKey]);
    }

    private function has($key)
    {
        return Cart::query()->whereKey($key)->exists();
    }
}
