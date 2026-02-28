<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookCustomerSeatRevokedPayload;

class CustomerSeatRevoked extends WebhookEvent
{
    public function __construct(
        public WebhookCustomerSeatRevokedPayload $payload,
    ) {}
}
