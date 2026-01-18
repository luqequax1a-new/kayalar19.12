<?php

namespace FleetCart\Listeners;

use FleetCart\Services\NotificationService;
use Modules\User\Events\CustomerRegistered;

class SendCustomerNotification
{
    public function handle(CustomerRegistered $event)
    {
        NotificationService::newCustomer($event->user);
    }
}
