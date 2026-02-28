<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookCustomerSeatClaimedPayload;

class CustomerSeatClaimed extends WebhookEvent
{
    public function __construct(
        public WebhookCustomerSeatClaimedPayload $payload,
    ) {}
}
