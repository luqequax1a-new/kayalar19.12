<?php

namespace Modules\Payment\Responses;

use Modules\Order\Entities\Order;
use Modules\Payment\GatewayResponse;
use Modules\Payment\HasTransactionReference;

class PaytrResponse extends GatewayResponse implements HasTransactionReference
{
    private Order $order;
    private ?string $token;

    public function __construct(Order $order, ?string $token = null)
    {
        $this->order = $order;
        $this->token = $token;
    }

    public function getOrderId()
    {
        return $this->order->id;
    }

    public function getTransactionReference()
    {
        return request('merchant_oid') ?: $this->token;
    }

    public function toArray(): array
    {
        return [
            'orderId' => $this->getOrderId(),
            'redirectUrl' => $this->token ? "https://www.paytr.com/odeme/guvenli/{$this->token}" : null,
        ];
    }
}
