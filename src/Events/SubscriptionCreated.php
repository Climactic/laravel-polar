<?php

namespace Climactic\LaravelPolar\Events;

use Climactic\LaravelPolar\Subscription;
use Illuminate\Database\Eloquent\Model;
use Polar\Models\Components\WebhookSubscriptionCreatedPayload;

class SubscriptionCreated extends WebhookEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        /**
         * The billable entity.
         */
        public Model $billable,
        /**
         * The subscription entity.
         */
        public Subscription $subscription,
        /**
         * The webhook payload.
         */
        public WebhookSubscriptionCreatedPayload $payload,
    ) {}
}
