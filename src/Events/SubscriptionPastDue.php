<?php

namespace Climactic\LaravelPolar\Events;

use Climactic\LaravelPolar\Subscription;
use Illuminate\Database\Eloquent\Model;
use Polar\Models\Components\WebhookSubscriptionPastDuePayload;

class SubscriptionPastDue extends WebhookEvent
{
    public function __construct(
        public Model $billable,
        public Subscription $subscription,
        public WebhookSubscriptionPastDuePayload $payload,
    ) {}
}
