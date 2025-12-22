<?php

namespace Modules\Order\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Mail\OrderStatusChanged as OrderStatusChangedEmail;

class SendOrderStatusChangedEmail
{
    /**
     * Handle the event.
     *
     * @param OrderStatusChanged $event
     *
     * @return void
     */
    public function handle(OrderStatusChanged $event)
    {
        $allowed = [
            \Modules\Order\Entities\Order::CANCELED,
            \Modules\Order\Entities\Order::REFUNDED,
        ];

        if (!in_array($event->order->status, $allowed)) {
            return;
        }

        Mail::to($event->order->customer_email)
            ->send(new OrderStatusChangedEmail($event->order));
    }
}
