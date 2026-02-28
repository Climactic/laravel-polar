<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookCustomerSeatAssignedPayload;

class CustomerSeatAssigned extends WebhookEvent
{
    public function __construct(
        public WebhookCustomerSeatAssignedPayload $payload,
    ) {}
}
