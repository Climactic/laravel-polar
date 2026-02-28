<?php

namespace Climactic\LaravelPolar\Events;

use Polar\Models\Components\WebhookCheckoutCreatedPayload;

class CheckoutCreated extends WebhookEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        /**
         * The webhook payload.
         */
        public WebhookCheckoutCreatedPayload $payload,
    ) {}
}
