<?php

namespace Climactic\LaravelPolar\Handlers;

use Carbon\Carbon;
use Climactic\LaravelPolar\Events\BenefitCreated;
use Polar\Models\Components\OrderStatus;
use Polar\Models\Components\SubscriptionStatus;
use Climactic\LaravelPolar\Events\BenefitGrantCreated;
use Climactic\LaravelPolar\Events\BenefitGrantRevoked;
use Climactic\LaravelPolar\Events\BenefitGrantUpdated;
use Climactic\LaravelPolar\Events\BenefitUpdated;
use Climactic\LaravelPolar\Events\CheckoutCreated;
use Climactic\LaravelPolar\Events\CheckoutExpired;
use Climactic\LaravelPolar\Events\CheckoutUpdated;
use Climactic\LaravelPolar\Events\CustomerCreated;
use Climactic\LaravelPolar\Events\CustomerDeleted;
use Climactic\LaravelPolar\Events\CustomerStateChanged;
use Climactic\LaravelPolar\Events\CustomerUpdated;
use Climactic\LaravelPolar\Events\OrderCreated;
use Climactic\LaravelPolar\Events\OrderUpdated;
use Climactic\LaravelPolar\Events\ProductCreated;
use Climactic\LaravelPolar\Events\ProductUpdated;
use Climactic\LaravelPolar\Events\SubscriptionActive;
use Climactic\LaravelPolar\Events\SubscriptionCanceled;
use Climactic\LaravelPolar\Events\SubscriptionCreated;
use Climactic\LaravelPolar\Events\SubscriptionRevoked;
use Climactic\LaravelPolar\Events\SubscriptionUpdated;
use Climactic\LaravelPolar\Events\WebhookHandled;
use Climactic\LaravelPolar\Events\WebhookReceived;
use Climactic\LaravelPolar\Events\WebhookSkipped;
use Climactic\LaravelPolar\Exceptions\InvalidMetadataPayload;
use Climactic\LaravelPolar\LaravelPolar;
use Climactic\LaravelPolar\Order as EloquentOrder;
use Climactic\LaravelPolar\Subscription as EloquentSubscription;
use Illuminate\Support\Facades\Log;
use Polar\Models\Components;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessWebhook extends ProcessWebhookJob
{
    private ?\Speakeasy\Serializer\Serializer $serializer = null;

    private function getSerializer(): \Speakeasy\Serializer\Serializer
    {
        if ($this->serializer === null) {
            $this->serializer = \Polar\Utils\JSON::createSerializer();
        }

        return $this->serializer;
    }

    public function handle(): void
    {
        $decoded = json_decode($this->webhookCall, true);
        if ($decoded === null || !isset($decoded['payload'])) {
            Log::error('Invalid webhook payload: failed to decode JSON or missing payload');
            return;
        }
        $payload = $decoded['payload'];
        $type = $payload['type'];
        $data = $payload['data'];
        $timestamp = $this->parseTimestamp($payload['timestamp'] ?? null);

        WebhookReceived::dispatch($payload);

        $skippedReason = match ($type) {
            'order.created' => $this->handleOrderCreated($data, $timestamp, $type),
            'order.updated' => $this->handleOrderUpdated($data, $timestamp, $type),
            'subscription.created' => $this->handleSubscriptionCreated($data, $timestamp, $type),
            'subscription.updated' => $this->handleSubscriptionSyncEvent($data, $timestamp, $type, SubscriptionUpdated::class),
            'subscription.active' => $this->handleSubscriptionSyncEvent($data, $timestamp, $type, SubscriptionActive::class),
            'subscription.canceled' => $this->handleSubscriptionSyncEvent($data, $timestamp, $type, SubscriptionCanceled::class),
            'subscription.revoked' => $this->handleSubscriptionSyncEvent($data, $timestamp, $type, SubscriptionRevoked::class),
            'benefit_grant.created' => $this->handleBenefitGrantEvent($data, $timestamp, $type, BenefitGrantCreated::class),
            'benefit_grant.updated' => $this->handleBenefitGrantEvent($data, $timestamp, $type, BenefitGrantUpdated::class),
            'benefit_grant.revoked' => $this->handleBenefitGrantEvent($data, $timestamp, $type, BenefitGrantRevoked::class),
            'checkout.created' => $this->dispatchSimpleEvent($data, $timestamp, $type, CheckoutCreated::class, Components\Checkout::class),
            'checkout.updated' => $this->dispatchSimpleEvent($data, $timestamp, $type, CheckoutUpdated::class, Components\Checkout::class),
            'checkout.expired' => $this->dispatchSimpleEvent($data, $timestamp, $type, CheckoutExpired::class, Components\Checkout::class),
            'customer.created' => $this->dispatchSimpleEvent($data, $timestamp, $type, CustomerCreated::class, Components\Customer::class),
            'customer.updated' => $this->dispatchSimpleEvent($data, $timestamp, $type, CustomerUpdated::class, Components\Customer::class),
            'customer.deleted' => $this->dispatchSimpleEvent($data, $timestamp, $type, CustomerDeleted::class, Components\Customer::class),
            'customer.state_changed' => $this->dispatchSimpleEvent($data, $timestamp, $type, CustomerStateChanged::class, Components\CustomerState::class),
            'product.created' => $this->dispatchSimpleEvent($data, $timestamp, $type, ProductCreated::class, Components\Product::class),
            'product.updated' => $this->dispatchSimpleEvent($data, $timestamp, $type, ProductUpdated::class, Components\Product::class),
            'benefit.created' => $this->handleBenefitEvent($data, $timestamp, $type, BenefitCreated::class),
            'benefit.updated' => $this->handleBenefitEvent($data, $timestamp, $type, BenefitUpdated::class),
            default => "Unknown event type: $type",
        };

        if ($skippedReason !== null) {
            WebhookSkipped::dispatch($payload, $skippedReason);
        } else {
            WebhookHandled::dispatch($payload);
        }
    }

    /**
     * Handle the order created event.
     *
     * @param  array<string, mixed>  $data
     */
    private function handleOrderCreated(array $data, \DateTime $timestamp, string $type): ?string // @phpstan-ignore return.unusedType
    {
        $billable = $this->resolveBillable($data);

        $order = $billable->orders()->firstOrCreate(['polar_id' => $data['id']], [
            'status' => \is_string($data['status']) ? OrderStatus::from($data['status']) : $data['status'],
            'amount' => $data['amount'],
            'tax_amount' => $data['tax_amount'],
            'refunded_amount' => $data['refunded_amount'],
            'refunded_tax_amount' => $data['refunded_tax_amount'],
            'currency' => $data['currency'],
            'billing_reason' => $data['billing_reason'],
            'customer_id' => $data['customer_id'],
            'product_id' => $data['product_id'],
            'ordered_at' => Carbon::make($data['created_at']),
        ]);

        $sdkOrder = $this->arrayToComponent($data, Components\Order::class);
        $payload = new Components\WebhookOrderCreatedPayload($timestamp, $sdkOrder, $type);
        OrderCreated::dispatch($billable, $order, $payload);

        return null;
    }

    /**
     * Handle the order updated event.
     *
     * @param  array<string, mixed>  $data
     */
    private function handleOrderUpdated(array $data, \DateTime $timestamp, string $type): ?string
    {
        $billable = $this->resolveBillable($data);

        if (!($order = $this->findOrder($data['id'])) instanceof EloquentOrder) {
            return "Order not found: {$data['id']}";
        }

        $status = $data['status'];
        $isRefunded = $status === OrderStatus::Refunded->value || $status === OrderStatus::PartiallyRefunded->value;

        $order->sync([
            ...$data,
            'status' => $status,
            'refunded_at' => $isRefunded ? Carbon::make($data['refunded_at']) : null,
        ]);

        $sdkOrder = $this->arrayToComponent($data, Components\Order::class);
        $payload = new Components\WebhookOrderUpdatedPayload($timestamp, $sdkOrder, $type);
        OrderUpdated::dispatch($billable, $order, $payload, $isRefunded);

        return null;
    }

    /**
     * Handle the subscription created event.
     *
     * @param  array<string, mixed>  $data
     */
    private function handleSubscriptionCreated(array $data, \DateTime $timestamp, string $type): ?string // @phpstan-ignore return.unusedType
    {
        $customerMetadata = $data['customer']['metadata'];
        $billable = $this->resolveBillable($data);

        $subscription = $billable->subscriptions()->create([
            'type' => $customerMetadata['subscription_type'] ?? 'default',
            'polar_id' => $data['id'],
            'status' => \is_string($data['status']) ? SubscriptionStatus::from($data['status']) : $data['status'],
            'product_id' => $data['product_id'],
            'current_period_end' => $data['current_period_end'] ? Carbon::make($data['current_period_end']) : null,
            'trial_ends_at' => isset($data['trial_ends_at']) ? Carbon::make($data['trial_ends_at']) : null,
            'ends_at' => $data['ends_at'] ? Carbon::make($data['ends_at']) : null,
        ]);

        if ($billable->customer->polar_id === null) {
            $billable->customer->update(['polar_id' => $data['customer_id']]);
        }

        $sdkSubscription = $this->arrayToComponent($data, Components\Subscription::class);
        $payload = new Components\WebhookSubscriptionCreatedPayload($timestamp, $sdkSubscription, $type);
        SubscriptionCreated::dispatch($billable, $subscription, $payload);

        return null;
    }

    /**
     * Handle a subscription sync event (updated/active/canceled/revoked).
     *
     * @param  array<string, mixed>  $data
     * @param  class-string  $eventClass
     */
    private function handleSubscriptionSyncEvent(array $data, \DateTime $timestamp, string $type, string $eventClass): ?string
    {
        if (!($subscription = $this->findSubscription($data['id'])) instanceof EloquentSubscription) {
            return "Subscription not found: {$data['id']}";
        }

        $subscription->sync($data);

        $sdkSubscription = $this->arrayToComponent($data, Components\Subscription::class);
        $payloadClass = $this->subscriptionPayloadClass($type);
        $payload = new $payloadClass($timestamp, $sdkSubscription, $type);
        $eventClass::dispatch($subscription->billable, $subscription, $payload);

        return null;
    }

    /**
     * Resolve the webhook payload class for a subscription event type.
     *
     * @return class-string
     */
    private function subscriptionPayloadClass(string $type): string
    {
        return match ($type) {
            'subscription.updated' => Components\WebhookSubscriptionUpdatedPayload::class,
            'subscription.active' => Components\WebhookSubscriptionActivePayload::class,
            'subscription.canceled' => Components\WebhookSubscriptionCanceledPayload::class,
            default => Components\WebhookSubscriptionRevokedPayload::class,
        };
    }

    /**
     * Handle a benefit grant event (created/updated/revoked).
     *
     * @param  array<string, mixed>  $data
     * @param  class-string  $eventClass
     */
    private function handleBenefitGrantEvent(array $data, \DateTime $timestamp, string $type, string $eventClass): ?string // @phpstan-ignore return.unusedType
    {
        $billable = $this->resolveBillable($data);

        $benefitGrant = $this->arrayToBenefitGrant($data);
        $payloadClass = match ($type) {
            'benefit_grant.created' => Components\WebhookBenefitGrantCreatedPayload::class,
            'benefit_grant.updated' => Components\WebhookBenefitGrantUpdatedPayload::class,
            default => Components\WebhookBenefitGrantRevokedPayload::class,
        };
        $payload = new $payloadClass($timestamp, $benefitGrant, $type);
        $eventClass::dispatch($billable, $payload);

        return null;
    }

    /**
     * Dispatch a simple payload-only event (checkout/customer/product).
     *
     * @param  array<string, mixed>  $data
     * @param  class-string  $eventClass
     * @param  class-string  $componentClass
     */
    private function dispatchSimpleEvent(array $data, \DateTime $timestamp, string $type, string $eventClass, string $componentClass): ?string // @phpstan-ignore return.unusedType
    {
        $component = $this->arrayToComponent($data, $componentClass);
        $payloadClass = $this->simplePayloadClass($type);
        $payload = new $payloadClass($timestamp, $component, $type);
        $eventClass::dispatch($payload);

        return null;
    }

    /**
     * Resolve the webhook payload class for simple (payload-only) event types.
     *
     * @return class-string
     */
    private function simplePayloadClass(string $type): string
    {
        return match ($type) {
            'checkout.created' => Components\WebhookCheckoutCreatedPayload::class,
            'checkout.updated' => Components\WebhookCheckoutUpdatedPayload::class,
            'checkout.expired' => Components\WebhookCheckoutExpiredPayload::class,
            'customer.created' => Components\WebhookCustomerCreatedPayload::class,
            'customer.updated' => Components\WebhookCustomerUpdatedPayload::class,
            'customer.deleted' => Components\WebhookCustomerDeletedPayload::class,
            'customer.state_changed' => Components\WebhookCustomerStateChangedPayload::class,
            'product.created' => Components\WebhookProductCreatedPayload::class,
            default => Components\WebhookProductUpdatedPayload::class,
        };
    }

    /**
     * Handle a benefit event (created/updated) — dispatches payload-only.
     *
     * @param  array<string, mixed>  $data
     * @param  class-string  $eventClass
     */
    private function handleBenefitEvent(array $data, \DateTime $timestamp, string $type, string $eventClass): ?string // @phpstan-ignore return.unusedType
    {
        $benefit = $this->arrayToBenefit($data);
        $payloadClass = match ($type) {
            'benefit.created' => Components\WebhookBenefitCreatedPayload::class,
            default => Components\WebhookBenefitUpdatedPayload::class,
        };
        $payload = new $payloadClass($timestamp, $benefit, $type);
        $eventClass::dispatch($payload);

        return null;
    }

    /**
     * Resolve the billable from the payload.
     *
     * @param  array<string, mixed>  $payload
     * @return \Climactic\LaravelPolar\Billable
     *
     * @throws InvalidMetadataPayload
     */
    private function resolveBillable(array $payload)
    {
        $customerMetadata = $payload['customer']['metadata'] ?? null;

        if (!isset($customerMetadata) || !is_array($customerMetadata) || !isset($customerMetadata['billable_id'], $customerMetadata['billable_type'])) {
            throw new InvalidMetadataPayload();
        }

        return $this->findOrCreateCustomer(
            $customerMetadata['billable_id'],
            (string) $customerMetadata['billable_type'],
            (string) $payload['customer_id'],
        );
    }

    /**
     * Find or create a customer.
     *
     * @return \Climactic\LaravelPolar\Billable
     */
    private function findOrCreateCustomer(int|string $billableId, string $billableType, string $customerId)
    {
        return LaravelPolar::$customerModel::firstOrCreate([
            'billable_id' => $billableId,
            'billable_type' => $billableType,
        ], [
            'polar_id' => $customerId,
        ])->billable;
    }

    private function findSubscription(string $subscriptionId): ?EloquentSubscription
    {
        return LaravelPolar::$subscriptionModel::firstWhere('polar_id', $subscriptionId);
    }

    private function findOrder(string $orderId): ?EloquentOrder
    {
        return LaravelPolar::$orderModel::firstWhere('polar_id', $orderId);
    }

    private function parseTimestamp($timestampValue): \DateTime
    {
        if ($timestampValue === null) {
            return new \DateTime();
        }

        $parsed = \DateTime::createFromFormat(\DateTime::ATOM, $timestampValue);
        if ($parsed !== false) {
            return $parsed;
        }

        $parsed = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $timestampValue);
        if ($parsed !== false) {
            return $parsed;
        }

        $timestamp = strtotime($timestampValue);
        if ($timestamp !== false) {
            $dateTime = new \DateTime();
            $dateTime->setTimestamp($timestamp);
            return $dateTime;
        }

        try {
            return new \DateTime($timestampValue);
        } catch (\Exception $e) {
            Log::warning('Failed to parse webhook timestamp', [
                'timestamp' => $timestampValue,
                'error' => $e->getMessage(),
            ]);

            return new \DateTime();
        }
    }

    /**
     * Deserialize array data into an SDK component.
     *
     * @template T
     * @param  array<string, mixed>  $data
     * @param  class-string<T>  $class
     * @return T
     */
    private function arrayToComponent(array $data, string $class): mixed
    {
        $json = json_encode($data);
        if ($json === false) {
            throw new \RuntimeException("Failed to encode data to JSON for {$class}: " . json_last_error_msg());
        }
        return $this->getSerializer()->deserialize($json, $class, 'json');
    }

    private function arrayToBenefitGrant(array $data): Components\BenefitGrantDiscordWebhook|Components\BenefitGrantCustomWebhook|Components\BenefitGrantGitHubRepositoryWebhook|Components\BenefitGrantDownloadablesWebhook|Components\BenefitGrantLicenseKeysWebhook|Components\BenefitGrantMeterCreditWebhook
    {
        $type = $data['type'] ?? $data['benefit']['type'] ?? 'custom';
        $json = json_encode($data);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode benefit grant data to JSON: ' . json_last_error_msg());
        }

        $serializer = $this->getSerializer();

        return match ($type) {
            'discord' => $serializer->deserialize($json, Components\BenefitGrantDiscordWebhook::class, 'json'),
            'custom' => $serializer->deserialize($json, Components\BenefitGrantCustomWebhook::class, 'json'),
            'github_repository' => $serializer->deserialize($json, Components\BenefitGrantGitHubRepositoryWebhook::class, 'json'),
            'downloadables' => $serializer->deserialize($json, Components\BenefitGrantDownloadablesWebhook::class, 'json'),
            'license_keys' => $serializer->deserialize($json, Components\BenefitGrantLicenseKeysWebhook::class, 'json'),
            'meter_credit' => $serializer->deserialize($json, Components\BenefitGrantMeterCreditWebhook::class, 'json'),
            default => $serializer->deserialize($json, Components\BenefitGrantCustomWebhook::class, 'json'),
        };
    }

    private function arrayToBenefit(array $data): Components\BenefitCustom|Components\BenefitDiscord|Components\BenefitGitHubRepository|Components\BenefitDownloadables|Components\BenefitLicenseKeys|Components\BenefitMeterCredit
    {
        $type = $data['type'] ?? 'custom';
        $json = json_encode($data);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode benefit data to JSON: ' . json_last_error_msg());
        }

        $serializer = $this->getSerializer();

        return match ($type) {
            'discord' => $serializer->deserialize($json, Components\BenefitDiscord::class, 'json'),
            'custom' => $serializer->deserialize($json, Components\BenefitCustom::class, 'json'),
            'github_repository' => $serializer->deserialize($json, Components\BenefitGitHubRepository::class, 'json'),
            'downloadables' => $serializer->deserialize($json, Components\BenefitDownloadables::class, 'json'),
            'license_keys' => $serializer->deserialize($json, Components\BenefitLicenseKeys::class, 'json'),
            'meter_credit' => $serializer->deserialize($json, Components\BenefitMeterCredit::class, 'json'),
            default => $serializer->deserialize($json, Components\BenefitCustom::class, 'json'),
        };
    }
}
