<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookRefundCreatedPayload;

class RefundCreated extends WebhookEvent
{
    public function __construct(
        public WebhookRefundCreatedPayload $payload,
    ) {}
}
