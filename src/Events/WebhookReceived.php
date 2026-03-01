<?php

namespace Climactic\LaravelPolar\Events;

class WebhookReceived extends WebhookEvent
{
    public function __construct(
        /**
         * The payload array.
         *
         * @var array<string, mixed>
         */
        public array $payload,
    ) {}
}
