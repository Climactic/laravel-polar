<?php

namespace Climactic\LaravelPolar\Events;

use Climactic\LaravelPolar\Order;
use Illuminate\Database\Eloquent\Model;
use Polar\Models\Components\WebhookOrderPaidPayload;

class OrderPaid extends WebhookEvent
{
    public function __construct(
        public Model $billable,
        public Order $order,
        public WebhookOrderPaidPayload $payload,
    ) {}
}
