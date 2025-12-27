<?php

return [
    'status_map' => [
        'New' => 'pending',
        'ReadyToShip' => 'shipped',
        'PickedUp' => 'shipped',
        'InTransit' => 'on_the_way',
        'OutForDelivery' => 'out_for_delivery',
        'Delivered' => 'completed',
        'OnTheWay' => 'on_the_way',
        'On Delivery' => 'out_for_delivery',
        'Out for delivery' => 'out_for_delivery',
        'Yolda' => 'on_the_way',
        'Teslim Edildi' => 'completed',
        'Exception' => 'pending',
        'Canceled' => 'canceled',
        'CanceledByCarrier' => 'canceled',
        'PackageAccepted' => 'shipped',
        'Shipped' => 'shipped',
    ],
    'final_statuses' => ['completed', 'canceled', 'refunded'],
];
