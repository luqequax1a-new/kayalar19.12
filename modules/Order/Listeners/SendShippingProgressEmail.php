<?php

namespace Modules\Order\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Mail\ShippingProgress as ShippingProgressEmail;

class SendShippingProgressEmail
{
    public function handle(OrderStatusChanged $event): void
    {
        $to = $event->toStatus ?? $event->order->status;
        $from = $event->fromStatus;

        Log::info('ShippingProgress listener hit', [
            'order_id' => $event->order->id ?? null,
            'from' => $from,
            'to' => $to,
            'source' => $event->source ?? null,
            'order_status' => $event->order->status ?? null,
            'customer_email' => $event->order->customer_email ?? null,
        ]);

        if ($to === null || $to === '' || $to === $from) {
            Log::info('ShippingProgress skipped: empty_or_same_status', [
                'order_id' => $event->order->id ?? null,
                'from' => $from,
                'to' => $to,
                'source' => $event->source ?? null,
            ]);
            return;
        }

        $allowed = [
            Order::SHIPPED,
            Order::ON_THE_WAY,
            Order::OUT_FOR_DELIVERY,
            Order::COMPLETED,
        ];

        if (!in_array($to, $allowed, true)) {
            Log::info('ShippingProgress skipped: status_not_allowed', [
                'order_id' => $event->order->id ?? null,
                'from' => $from,
                'to' => $to,
                'allowed' => $allowed,
                'source' => $event->source ?? null,
            ]);
            return;
        }

        Log::info('ShippingProgress allowed', [
            'order_id' => $event->order->id ?? null,
            'from' => $from,
            'to' => $to,
            'source' => $event->source ?? null,
        ]);

        if (empty($event->order->customer_email)) {
            Log::info('ShippingProgress skipped: empty_customer_email', [
                'order_id' => $event->order->id ?? null,
                'from' => $from,
                'to' => $to,
                'source' => $event->source ?? null,
            ]);
            return;
        }

        try {
            Log::info('ShippingProgress sending', [
                'order_id' => $event->order->id ?? null,
                'to_email' => $event->order->customer_email,
                'from' => $from,
                'to' => $to,
                'source' => $event->source ?? null,
            ]);
            Mail::to($event->order->customer_email)->send(new ShippingProgressEmail($event->order));

            Log::info('ShippingProgress sent', [
                'order_id' => $event->order->id ?? null,
                'to_email' => $event->order->customer_email,
                'from' => $from,
                'to' => $to,
                'source' => $event->source ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Shipping progress email failed', [
                'order_id' => $event->order->id ?? null,
                'from' => $from,
                'to' => $to,
                'source' => $event->source ?? null,
                'exception' => $e,
            ]);
        }
    }
}
