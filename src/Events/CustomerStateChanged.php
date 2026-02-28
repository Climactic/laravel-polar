<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookCustomerStateChangedPayload;

class CustomerStateChanged extends WebhookEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        /**
         * The webhook payload.
         */
        public WebhookCustomerStateChangedPayload $payload,
    ) {}
}
