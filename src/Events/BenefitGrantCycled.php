<?php

namespace Climactic\LaravelPolar\Events;

use Illuminate\Database\Eloquent\Model;
use Polar\Models\Components\WebhookBenefitGrantCycledPayload;

class BenefitGrantCycled extends WebhookEvent
{
    public function __construct(
        public Model $billable,
        public WebhookBenefitGrantCycledPayload $payload,
    ) {}
}
