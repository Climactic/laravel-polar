<?php

namespace Climactic\LaravelPolar\Contracts;

interface WebhookHandler
{
    /**
     * Handle a webhook event.
     *
     * @param  array<string, mixed>  $data
     * @return string|null  Return a skip reason string to mark the event as skipped, or null if handled.
     */
    public function handle(array $data, \DateTime $timestamp, string $type): ?string;
}
