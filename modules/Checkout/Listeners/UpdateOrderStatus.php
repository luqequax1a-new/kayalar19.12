<?php

namespace Modules\Checkout\Listeners;

use Modules\Order\Entities\Order;
use Modules\Checkout\Events\OrderPlaced;

class UpdateOrderStatus
{
    /**
     * Handle the event.
     *
     * @param OrderPlaced $event
     *
     * @return void
     */
    public function handle($event)
    {
        $paymentMethod = (string) $event->order->payment_method;
        $bankLabel = (string) setting('bank_transfer_label');
        $codLabel = (string) setting('cod_label');

        if ($paymentMethod !== '' && (
            $paymentMethod === 'Bank Transfer' ||
            ($bankLabel !== '' && $paymentMethod === $bankLabel)
        )) {
            $event->order->transitionTo(Order::PENDING_PAYMENT);
            return;
        }

        if ($codLabel !== '' && $paymentMethod === $codLabel) {
            $event->order->transitionTo(Order::PENDING);
            $this->sendToGeliverIfEnabled($event->order);
            return;
        }

        $event->order->transitionTo(Order::PENDING);
        // Diğer tüm siparişler: otomatik Geliver'e gönder
        $this->sendToGeliverIfEnabled($event->order);
    }

    private function sendToGeliverIfEnabled(Order $order): void
    {
        if ((bool) setting('geliver_enabled') !== true) {
            return;
        }
        if ($order->geliver_shipment_id) {
            return;
        }
        try {
            app(\Modules\Geliver\Services\GeliverService::class)->sendOrderToGeliver($order);
        } catch (\Throwable $e) {
            // sessizce yut, admin ekranında zaten hatalar gösterilir
        }
    }
}
