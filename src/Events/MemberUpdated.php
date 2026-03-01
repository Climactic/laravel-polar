<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookMemberUpdatedPayload;

class MemberUpdated extends WebhookEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        /**
         * The webhook payload.
         */
        public WebhookMemberUpdatedPayload $payload,
    ) {}
}
