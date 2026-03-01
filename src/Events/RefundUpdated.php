<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookRefundUpdatedPayload;

class RefundUpdated extends WebhookEvent
{
    public function __construct(
        public WebhookRefundUpdatedPayload $payload,
    ) {}
}
