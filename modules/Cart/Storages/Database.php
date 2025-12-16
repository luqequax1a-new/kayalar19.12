<?php

namespace Modules\Cart\Storages;

use Modules\Cart\Entities\Cart;
use Darryldecode\Cart\CartCollection;
use Illuminate\Support\Facades\Session;

class Database
{
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

        if ($this->has($normalizedKey)) {
            return new CartCollection(Cart::find($normalizedKey)->data);
        } else {
            return [];
        }
    }

    public function put($key, $value)
    {
        $normalizedKey = $this->normalizeKey($key);

        if ($row = Cart::find($normalizedKey)) {
            $row->data = $value;
            $row->save();
        } else {
            Cart::create([
                'id' => $normalizedKey,
                'data' => $value,
            ]);
        }
    }

    private function has($key)
    {
        return Cart::find($key);
    }
}
