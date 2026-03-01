<?php

namespace Tests\Feature;

use Climactic\LaravelPolar\Contracts\WebhookHandler;
use Climactic\LaravelPolar\Events\WebhookHandled;
use Climactic\LaravelPolar\Events\WebhookReceived;
use Climactic\LaravelPolar\Events\WebhookSkipped;
use Climactic\LaravelPolar\Handlers\ProcessWebhook;
use Illuminate\Support\Facades\Event;
use Spatie\WebhookClient\Models\WebhookCall;

class TestRegistryProcessWebhook extends ProcessWebhook
{
    public function __construct($webhookCall)
    {
        parent::__construct($webhookCall);
    }
}

function createRegistryWebhookCall(array $payload): TestRegistryProcessWebhook
{
    $webhookCall = WebhookCall::create([
        'name' => 'polar',
        'url' => 'https://example.com/webhook',
        'payload' => $payload,
    ]);

    return new TestRegistryProcessWebhook($webhookCall);
}

it('uses custom handler when registered for an event type', function () {
    Event::fake([WebhookReceived::class, WebhookHandled::class, WebhookSkipped::class]);

    $handlerClass = new class implements WebhookHandler {
        public static bool $called = false;

        public function handle(array $data, \DateTime $timestamp, string $type): ?string
        {
            static::$called = true;

            return null;
        }
    };

    $handlerClassName = get_class($handlerClass);
    $handlerClassName::$called = false;
    app()->instance($handlerClassName, $handlerClass);

    config(['polar.webhook_handlers' => [
        'product.created' => $handlerClassName,
    ]]);

    $job = createRegistryWebhookCall([
        'type' => 'product.created',
        'timestamp' => '2024-01-01T00:00:00Z',
        'data' => ['id' => 'prod_123', 'name' => 'Test Product'],
    ]);
    $job->handle();

    expect($handlerClassName::$called)->toBeTrue();
    Event::assertDispatched(WebhookReceived::class);
    Event::assertDispatched(WebhookHandled::class);
    Event::assertNotDispatched(WebhookSkipped::class);
});

it('returns skip reason from custom handler', function () {
    Event::fake([WebhookReceived::class, WebhookHandled::class, WebhookSkipped::class]);

    $handlerClass = new class implements WebhookHandler {
        public function handle(array $data, \DateTime $timestamp, string $type): ?string
        {
            return 'Custom skip reason';
        }
    };

    $handlerClassName = get_class($handlerClass);
    app()->instance($handlerClassName, $handlerClass);

    config(['polar.webhook_handlers' => [
        'product.created' => $handlerClassName,
    ]]);

    $job = createRegistryWebhookCall([
        'type' => 'product.created',
        'timestamp' => '2024-01-01T00:00:00Z',
        'data' => ['id' => 'prod_123'],
    ]);
    $job->handle();

    Event::assertDispatched(WebhookSkipped::class);
    Event::assertNotDispatched(WebhookHandled::class);
});

it('falls through to default handler when no custom handler is registered', function () {
    Event::fake([WebhookReceived::class, WebhookHandled::class, WebhookSkipped::class]);

    config(['polar.webhook_handlers' => []]);

    $job = createRegistryWebhookCall([
        'type' => 'some.unknown.event',
        'timestamp' => '2024-01-01T00:00:00Z',
        'data' => ['id' => 'test_123'],
    ]);
    $job->handle();

    Event::assertDispatched(WebhookReceived::class);
    Event::assertDispatched(WebhookSkipped::class);
});
