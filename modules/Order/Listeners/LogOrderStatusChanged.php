<?php

namespace Modules\Order\Listeners;

use Modules\Order\Entities\OrderStatusLog;
use Modules\Order\Events\OrderStatusChanged;

class LogOrderStatusChanged
{
    public function handle(OrderStatusChanged $event): void
    {
        $source = $event->source;
        if ($source === null || $source === '') {
            $source = request()?->attributes?->get('order_status_change_source');
        }
        if ($source === null || $source === '') {
            $source = 'system';
        }

        $adminUserId = null;
        try {
            $adminUserId = auth()->id();
        } catch (\Throwable $e) {
        }

        OrderStatusLog::create([
            'order_id' => $event->order->id,
            'from_status' => $event->fromStatus,
            'to_status' => $event->toStatus ?? $event->order->status,
            'source' => $source,
            'admin_user_id' => $adminUserId,
            'context' => $event->context,
        ]);
    }
}
