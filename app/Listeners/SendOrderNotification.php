<?php

namespace FleetCart\Listeners;

use FleetCart\Services\NotificationService;
use Modules\Checkout\Events\OrderPlaced;

class SendOrderNotification
{
    public function handle(OrderPlaced $event)
    {
        NotificationService::newOrder($event->order);
        
        // Check if this order recovered an abandoned cart
        if (session()->has('recovered_from_cart_id')) {
            $cartId = session()->get('recovered_from_cart_id');
            $cart = \Modules\Cart\Entities\Cart::find($cartId);
            
            if ($cart && $cart->is_recovered) {
                NotificationService::cartRecovered($cart, $event->order);
            }
        }
    }
}
