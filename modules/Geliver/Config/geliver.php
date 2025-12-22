<?php

return [
    'status_map' => [
        'New' => 'pending',
        'ReadyToShip' => 'shipped',
        'PickedUp' => 'shipped',
        'InTransit' => 'shipped',
        'OutForDelivery' => 'shipped',
        'Delivered' => 'completed',
        'OnTheWay' => 'shipped',
        'On Delivery' => 'shipped',
        'Out for delivery' => 'shipped',
        'Yolda' => 'shipped',
        'Teslim Edildi' => 'completed',
        'Exception' => 'pending',
        'Canceled' => 'canceled',
        'CanceledByCarrier' => 'canceled',
        'PackageAccepted' => 'shipped',
        'Shipped' => 'shipped',
    ],
    'final_statuses' => ['completed', 'canceled', 'refunded'],
];
