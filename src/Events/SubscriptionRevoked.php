<?php

namespace Climactic\LaravelPolar\Events;

use Climactic\LaravelPolar\Subscription;
use Illuminate\Database\Eloquent\Model;
use Polar\Models\Components\WebhookSubscriptionRevokedPayload;

class SubscriptionRevoked extends WebhookEvent
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
         * The subscription instance.
         */
        public Subscription $subscription,
        /**
         * The webhook payload.
         */
        public WebhookSubscriptionRevokedPayload $payload,
    ) {}
}
