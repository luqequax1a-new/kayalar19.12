<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Cart\Entities\Cart;

class CartTrackController extends Controller
{
    public function track($id)
    {
        $cart = Cart::where('id', $id)->first();

        if ($cart) {
            $cart->update([
                'is_clicked' => true,
                'clicked_at' => now(),
                'superseded_at' => now(),
            ]);

            // Copy customer data to current session's cart to unify the abandonment flow
            $sessionId = session()->getId();
            Cart::whereIn('id', [$sessionId . '_cart_items', $sessionId . '_cart_conditions'])
                ->update([
                    'customer_email' => $cart->customer_email,
                    'customer_first_name' => $cart->customer_first_name,
                    'customer_last_name' => $cart->customer_last_name,
                    'customer_phone' => $cart->customer_phone,
                    'user_id' => $cart->user_id,
                ]);

            // Store the original abandoned cart ID in session to mark it as recovered later
            session()->put('recovered_from_cart_id', $id);

            // RESTORE CART LOGIC
            // We need to re-add items from the abandoned cart to the active session cart
            try {
                if (!empty($cart->data)) {
                    $items = is_array($cart->data) ? $cart->data : ($cart->data instanceof \Illuminate\Support\Collection ? $cart->data : []);
                    
                    foreach ($items as $item) {
                        $itemId = data_get($item, 'id');
                        $qty = data_get($item, 'quantity');
                        $price = data_get($item, 'price');
                        $name = data_get($item, 'name');
                        $attributes = data_get($item, 'attributes');
                        $associatedModel = data_get($item, 'associatedModel');

                        if ($itemId && $qty) {
                            \Modules\Cart\Facades\Cart::add([
                                'id' => $itemId,
                                'name' => $name,
                                'price' => $price,
                                'quantity' => $qty,
                                'attributes' => $attributes,
                                'associatedModel' => $associatedModel,
                            ]);
                        }
                    }

                    // RESTORE CONDITIONS (COUPONS)
                    $conditionsId = str_replace('_cart_items', '_cart_conditions', $id);
                    $conditionsCart = Cart::where('id', $conditionsId)->first();
                    
                    if ($conditionsCart && !empty($conditionsCart->data)) {
                        $conditions = $conditionsCart->data;
                        
                        // Filter conditions to likely valid coupons/discounts
                        // Darryldecode Cart stores conditions as objects or arrays
                        if (is_iterable($conditions)) {
                            foreach ($conditions as $condition) {
                                // Ensure condition format is compatible
                                 \Modules\Cart\Facades\Cart::condition($condition);
                            }
                        }
                    }
                }

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to restore abandoned cart items: ' . $e->getMessage());
            }
        }

        $redirectTo = request('redirect_to');
        
        if ($redirectTo && filter_var($redirectTo, FILTER_VALIDATE_URL)) {
             return redirect()->to($redirectTo);
        }

        return redirect()->route('cart.index');
    }
}
