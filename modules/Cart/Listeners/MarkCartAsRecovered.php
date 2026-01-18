<?php

namespace Modules\Cart\Listeners;

use Modules\Checkout\Events\OrderPlaced;
use Modules\Cart\Entities\Cart;
use Illuminate\Support\Facades\Log;

class MarkCartAsRecovered
{
    /**
     * Handle the event.
     * 
     * When an order is placed, mark any abandoned cart associated with
     * the current session as recovered.
     *
     * @param OrderPlaced $event
     * @return void
     */
    public function handle(OrderPlaced $event): void
    {
        try {
            $order = $event->order;
            $sessionId = session()->getId();
            
            // PRIORITY 1: Check if this order came from a specific abandoned cart link
            // CartTrackController sets this session variable when user clicks abandoned cart email
            $recoveredFromCartId = session()->get('recovered_from_cart_id');
            
            if ($recoveredFromCartId) {
                $cart = Cart::find($recoveredFromCartId);
                
                if ($cart && !$cart->is_recovered) {
                    $updateData = [
                        'is_recovered' => true,
                        'recovered_at' => now(),
                        'order_id' => $order->id,
                    ];
                    
                    if ($order->customer_email !== $cart->customer_email) {
                        $updateData['recovered_by_email'] = $order->customer_email;
                    }
                    
                    $cart->update($updateData);
                    
                    Log::info('[ABANDONED-CART] Cart marked as recovered from email link', [
                        'cart_id' => $cart->id,
                        'order_id' => $order->id,
                        'original_email' => $cart->customer_email,
                        'order_email' => $order->customer_email,
                        'different_email' => $order->customer_email !== $cart->customer_email,
                    ]);
                    
                    // Clear the session variable to prevent duplicate marking
                    session()->forget('recovered_from_cart_id');
                    return;
                }
            }
            
            // PRIORITY 2: Check current session cart
            // This handles normal checkout flow (not from abandoned cart email)
            $cartId = $sessionId . '_cart_items';
            $cart = Cart::find($cartId);
            
            if ($cart && !$cart->is_recovered) {
                $updateData = [
                    'is_recovered' => true,
                    'recovered_at' => now(),
                    'order_id' => $order->id,
                ];
                
                if ($order->customer_email !== $cart->customer_email) {
                    $updateData['recovered_by_email'] = $order->customer_email;
                }
                
                $cart->update($updateData);
                
                Log::info('[ABANDONED-CART] Current session cart marked as recovered', [
                    'cart_id' => $cart->id,
                    'order_id' => $order->id,
                    'original_email' => $cart->customer_email,
                    'order_email' => $order->customer_email,
                    'different_email' => $order->customer_email !== $cart->customer_email,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('[ABANDONED-CART] Error marking cart as recovered', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
