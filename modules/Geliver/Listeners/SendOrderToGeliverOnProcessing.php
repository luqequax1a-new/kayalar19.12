<?php

namespace Modules\Geliver\Listeners;

use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;

class SendOrderToGeliverOnProcessing
{
    public function handle(OrderStatusChanged $event): void
    {
        if ((bool) setting('geliver_enabled') !== true) {
            return;
        }
        $order = $event->order;
        if ($order->geliver_shipment_id) {
            return;
        }
        if ($order->status !== Order::PENDING) {
            return;
        }

        try {
            app(\Modules\Geliver\Services\GeliverService::class)->sendOrderToGeliver($order);
        } catch (\Throwable $e) {
            // sessizce yut, admin ekranında zaten hatalar gösterilir
        }
    }
}

