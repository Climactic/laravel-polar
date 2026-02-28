<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookOrganizationUpdatedPayload;

class OrganizationUpdated extends WebhookEvent
{
    public function __construct(
        public WebhookOrganizationUpdatedPayload $payload,
    ) {}
}
