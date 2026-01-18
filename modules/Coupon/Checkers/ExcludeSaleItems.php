<?php

namespace Modules\Coupon\Checkers;

use Closure;
use Modules\Cart\Facades\Cart;
use Modules\Coupon\Exceptions\ExcludeSaleItemsException;

class ExcludeSaleItems
{
    public function handle($coupon, Closure $next)
    {
        if ($coupon->exclude_sale_items) {
            $hasApplicableItems = Cart::items()->reject(function ($cartItem) {
                return $cartItem->product->hasSpecialPrice();
            })->isNotEmpty();

            if (!$hasApplicableItems) {
                throw new ExcludeSaleItemsException;
            }
        }

        return $next($coupon);
    }
}
