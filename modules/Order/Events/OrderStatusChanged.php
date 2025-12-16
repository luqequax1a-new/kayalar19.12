<?php

namespace Modules\Order\Events;

use Modules\Order\Entities\Order;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use SerializesModels;

    /**
     * The instance of order.
     *
     * @var Order
     */
    public $order;

    public $fromStatus;

    public $toStatus;

    public $source;

    public $context;


    /**
     * Create a new event instance.
     *
     * @param Order $order
     *
     * @return void
     */
    public function __construct(Order $order, ?string $fromStatus = null, ?string $toStatus = null, ?string $source = null, ?array $context = null)
    {
        $this->order = $order;
        $this->fromStatus = $fromStatus;
        $this->toStatus = $toStatus;
        $this->source = $source;
        $this->context = $context;
    }
}
