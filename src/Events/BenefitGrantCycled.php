<?php

namespace Climactic\LaravelPolar\Events;

use Illuminate\Database\Eloquent\Model;
use Polar\Models\Components\WebhookBenefitGrantCycledPayload;

class BenefitGrantCycled extends WebhookEvent
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
         * The webhook payload.
         */
        public WebhookBenefitGrantCycledPayload $payload,
    ) {}
}
