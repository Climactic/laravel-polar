<?php

namespace Climactic\LaravelPolar\Events;

use Climactic\LaravelPolar\Order;
use Illuminate\Database\Eloquent\Model;
use Polar\Models\Components\WebhookOrderRefundedPayload;

class OrderRefunded extends WebhookEvent
{
    public function __construct(
        public Model $billable,
        public Order $order,
        public WebhookOrderRefundedPayload $payload,
    ) {}
}
