<?php

namespace Modules\Cart\Http\Controllers;

use Modules\Cart\Facades\Cart;
use Modules\Cart\Services\CartUpsellService;

class CartController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(CartUpsellService $upsellService)
    {
        $cart = Cart::instance();
        $upsellData = $upsellService->resolveBestRule($cart, 'cart');

        return view('storefront::public.cart.index')->with([
            'isCartEmpty' => Cart::isEmpty(),
            'crossSellProducts' => Cart::crossSellProducts(),
            'upsellData' => $upsellData,
        ]);
    }


    public function cart(CartUpsellService $upsellService)
    {
        $cart = Cart::instance();
        
        // Calculate upsell offers for cart items
        try {
            $upsellData = $upsellService->resolveBestRule($cart, 'cart');
            
            // Attach upsell info to cart response
            if ($upsellData && isset($upsellData['offers'])) {
                $cart->upsellData = $upsellData;
            }
        } catch (\Throwable $e) {
            \Log::warning('[CART] Failed to calculate upsell offers: ' . $e->getMessage());
        }

        return $cart;
    }


    /**
     * Clear the cart.
     *
     * @return \Modules\Cart\Cart
     */
    public function clear()
    {
        Cart::clear();

        return Cart::instance();
    }
}
