<?php

namespace Climactic\LaravelPolar\Events;

use Climactic\LaravelPolar\Order;
use Illuminate\Database\Eloquent\Model;
use Polar\Models\Components\WebhookOrderCreatedPayload;

class OrderCreated extends WebhookEvent
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
         * The order entity.
         */
        public Order $order,
        /**
         * The webhook payload.
         */
        public WebhookOrderCreatedPayload $payload,
    ) {}
}
