<?php

namespace Climactic\LaravelPolar\Events;

class WebhookSkipped extends WebhookEvent
{
    public function __construct(
        /**
         * The payload array.
         *
         * @var array<string, mixed>
         */
        public array $payload,
        /**
         * The reason the webhook was skipped.
         */
        public string $reason,
    ) {}
}
