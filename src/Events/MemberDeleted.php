<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookMemberDeletedPayload;

class MemberDeleted extends WebhookEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        /**
         * The webhook payload.
         */
        public WebhookMemberDeletedPayload $payload,
    ) {}
}
