<?php

return [
    'status_map' => [
        'New' => 'processing',
        'ReadyToShip' => 'shipped',
        'PickedUp' => 'processing',
        'InTransit' => 'shipped',
        'OutForDelivery' => 'shipped',
        'Delivered' => 'completed',
        'OnTheWay' => 'shipped',
        'On Delivery' => 'shipped',
        'Out for delivery' => 'shipped',
        'Yolda' => 'shipped',
        'Teslim Edildi' => 'completed',
        'Exception' => 'on_hold',
        'Canceled' => 'canceled',
        'CanceledByCarrier' => 'canceled',
        'PackageAccepted' => 'shipped',
        'Shipped' => 'shipped',
    ],
    'final_statuses' => ['completed', 'canceled', 'refunded'],
];
