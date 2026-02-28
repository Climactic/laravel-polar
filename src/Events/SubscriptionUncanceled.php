<?php

namespace Climactic\LaravelPolar\Events;

use Climactic\LaravelPolar\Subscription;
use Illuminate\Database\Eloquent\Model;
use Polar\Models\Components\WebhookSubscriptionUncanceledPayload;

class SubscriptionUncanceled extends WebhookEvent
{
    public function __construct(
        public Model $billable,
        public Subscription $subscription,
        public WebhookSubscriptionUncanceledPayload $payload,
    ) {}
}
