<?php

namespace Climactic\LaravelPolar;

use Climactic\LaravelPolar\Exceptions\PolarApiError;
use Climactic\LaravelPolar\Testing\PolarFake;
use Polar\Models\Components;
use Polar\Models\Errors;
use Polar\Models\Operations;
use Polar\Polar;

class LaravelPolar
{
    /**
     * The cached Polar SDK instance.
     */
    private static ?Polar $sdkInstance = null;

    /**
     * The active fake instance, if any.
     */
    private static ?PolarFake $fakeInstance = null;

    /**
     * The customer model class name.
     */
    public static string $customerModel = Customer::class;

    /**
     * The subscription model class name.
     */
    public static string $subscriptionModel = Subscription::class;

    /**
     * The order model class name.
     */
    public static string $orderModel = Order::class;

    /**
     * If a fake is active, record the call and return the stub value.
     *
     * @param  list<mixed>  $args
     * @return array{0: true, 1: mixed}|array{0: false}
     */
    private static function recordIfFaking(string $method, array $args): array
    {
        if (self::$fakeInstance !== null) {
            return [true, self::$fakeInstance->recordCall($method, $args)];
        }

        return [false];
    }

    /**
     * Create a checkout session.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createCheckoutSession(Components\CheckoutCreate $request): Components\Checkout
    {
        $fake = self::recordIfFaking('createCheckoutSession', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->checkouts->create(request: $request);

        if ($response->statusCode === 201 && $response->checkout !== null) {
            return $response->checkout;
        }

        throw new Errors\APIException('Failed to create checkout session', $response->statusCode ?? 500, '', null);
    }

    /**
     * Update a subscription.
     *
     * @param Components\SubscriptionUpdateProduct|Components\SubscriptionCancel|Components\SubscriptionUpdateDiscount|Components\SubscriptionUpdateTrial|Components\SubscriptionUpdateSeats|Components\SubscriptionRevoke $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateSubscription(string $subscriptionId, Components\SubscriptionUpdateProduct|Components\SubscriptionCancel|Components\SubscriptionUpdateDiscount|Components\SubscriptionUpdateTrial|Components\SubscriptionUpdateSeats|Components\SubscriptionRevoke $request): Components\Subscription
    {
        $fake = self::recordIfFaking('updateSubscription', [$subscriptionId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->subscriptions->update(
            id: $subscriptionId,
            subscriptionUpdate: $request,
        );

        if ($response->statusCode === 200 && $response->subscription !== null) {
            return $response->subscription;
        }

        throw new Errors\APIException('Failed to update subscription', $response->statusCode ?? 500, '', null);
    }

    /**
     * List all products.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listProducts(?Operations\ProductsListRequest $request = null): Operations\ProductsListResponse
    {
        $fake = self::recordIfFaking('listProducts', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $request ??= new Operations\ProductsListRequest();

        $generator = $sdk->products->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list products', 500, '', null);
    }

    /**
     * Create a customer session.
     *
     * @param Components\CustomerSessionCustomerIDCreate|Components\CustomerSessionCustomerExternalIDCreate $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createCustomerSession(Components\CustomerSessionCustomerIDCreate|Components\CustomerSessionCustomerExternalIDCreate $request): Components\CustomerSession
    {
        $fake = self::recordIfFaking('createCustomerSession', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customerSessions->create(request: $request);

        if ($response->statusCode === 201 && $response->customerSession !== null) {
            return $response->customerSession;
        }

        throw new Errors\APIException('Failed to create customer session', $response->statusCode ?? 500, '', null);
    }

    /**
     * Create a benefit.
     *
     * @param Components\BenefitCustomCreate|Components\BenefitDiscordCreate|Components\BenefitGitHubRepositoryCreate|Components\BenefitDownloadablesCreate|Components\BenefitLicenseKeysCreate|Components\BenefitMeterCreditCreate $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createBenefit(Components\BenefitCustomCreate|Components\BenefitDiscordCreate|Components\BenefitGitHubRepositoryCreate|Components\BenefitDownloadablesCreate|Components\BenefitLicenseKeysCreate|Components\BenefitMeterCreditCreate $request): Components\BenefitCustom|Components\BenefitDiscord|Components\BenefitGitHubRepository|Components\BenefitDownloadables|Components\BenefitLicenseKeys|Components\BenefitMeterCredit
    {
        $fake = self::recordIfFaking('createBenefit', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->benefits->create(request: $request);

        if ($response->statusCode === 201 && $response->benefit !== null) {
            return $response->benefit;
        }

        throw new Errors\APIException('Failed to create benefit', $response->statusCode ?? 500, '', null);
    }

    /**
     * Update a benefit.
     *
     * @param Components\BenefitCustomUpdate|Components\BenefitDiscordUpdate|Components\BenefitGitHubRepositoryUpdate|Components\BenefitDownloadablesUpdate|Components\BenefitLicenseKeysUpdate|Components\BenefitMeterCreditUpdate $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateBenefit(string $benefitId, Components\BenefitCustomUpdate|Components\BenefitDiscordUpdate|Components\BenefitGitHubRepositoryUpdate|Components\BenefitDownloadablesUpdate|Components\BenefitLicenseKeysUpdate|Components\BenefitMeterCreditUpdate $request): Components\BenefitCustom|Components\BenefitDiscord|Components\BenefitGitHubRepository|Components\BenefitDownloadables|Components\BenefitLicenseKeys|Components\BenefitMeterCredit
    {
        $fake = self::recordIfFaking('updateBenefit', [$benefitId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->benefits->update(id: $benefitId, requestBody: $request);

        if ($response->statusCode === 200 && $response->benefit !== null) {
            return $response->benefit;
        }

        throw new Errors\APIException('Failed to update benefit', $response->statusCode ?? 500, '', null);
    }

    /**
     * Delete a benefit.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function deleteBenefit(string $benefitId): void
    {
        $fake = self::recordIfFaking('deleteBenefit', [$benefitId]);
        if ($fake[0]) {
            return;
        }

        $sdk = self::sdk();

        $response = $sdk->benefits->delete(id: $benefitId);

        if ($response->statusCode !== 200 && $response->statusCode !== 204) {
            throw new Errors\APIException('Failed to delete benefit', $response->statusCode, '', null);
        }
    }

    /**
     * List all benefits.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listBenefits(Operations\BenefitsListRequest $request): Operations\BenefitsListResponse
    {
        $fake = self::recordIfFaking('listBenefits', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $generator = $sdk->benefits->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list benefits', 500, '', null);
    }

    /**
     * Get a specific benefit by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getBenefit(string $benefitId): Components\BenefitCustom|Components\BenefitDiscord|Components\BenefitGitHubRepository|Components\BenefitDownloadables|Components\BenefitLicenseKeys|Components\BenefitMeterCredit
    {
        $fake = self::recordIfFaking('getBenefit', [$benefitId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->benefits->get(id: $benefitId);

        if ($response->statusCode === 200 && $response->benefit !== null) {
            return $response->benefit;
        }

        throw new Errors\APIException('Failed to get benefit', $response->statusCode ?? 500, '', null);
    }

    /**
     * List all grants for a specific benefit.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listBenefitGrants(Operations\BenefitsGrantsRequest $request): Operations\BenefitsGrantsResponse
    {
        $fake = self::recordIfFaking('listBenefitGrants', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $generator = $sdk->benefits->grants(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list benefit grants', 500, '', null);
    }

    /**
     * Ingest usage events for metered billing.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function ingestEvents(Components\EventsIngest $request): void
    {
        $fake = self::recordIfFaking('ingestEvents', [$request]);
        if ($fake[0]) {
            return;
        }

        $sdk = self::sdk();

        $response = $sdk->events->ingest(request: $request);

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw new Errors\APIException('Failed to ingest events', $response->statusCode, '', null);
        }
    }

    /**
     * List customer meters.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listCustomerMeters(Operations\CustomerMetersListRequest $request): Operations\CustomerMetersListResponse
    {
        $fake = self::recordIfFaking('listCustomerMeters', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $generator = $sdk->customerMeters->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list customer meters', 500, '', null);
    }

    /**
     * Get a specific customer meter by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getCustomerMeter(string $meterId): Components\CustomerMeter
    {
        $fake = self::recordIfFaking('getCustomerMeter', [$meterId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customerMeters->get(id: $meterId);

        if ($response->statusCode === 200 && $response->customerMeter !== null) {
            return $response->customerMeter;
        }

        throw new Errors\APIException('Failed to get customer meter', $response->statusCode ?? 500, '', null);
    }

    // ──────────────────────────────────────────────────────────────
    //  Customer CRUD
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a customer.
     *
     * @param Components\CustomerIndividualCreate|Components\CustomerTeamCreate $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createCustomer(Components\CustomerIndividualCreate|Components\CustomerTeamCreate $request): Components\CustomerIndividual|Components\CustomerTeam
    {
        $fake = self::recordIfFaking('createCustomer', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customers->create(request: $request);

        if ($response->statusCode === 201 && $response->customer !== null) {
            return $response->customer;
        }

        throw new Errors\APIException('Failed to create customer', $response->statusCode ?? 500, '', null);
    }

    /**
     * Get a customer by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getCustomer(string $customerId): Components\CustomerIndividual|Components\CustomerTeam
    {
        $fake = self::recordIfFaking('getCustomer', [$customerId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customers->get(id: $customerId);

        if ($response->statusCode === 200 && $response->customer !== null) {
            return $response->customer;
        }

        throw new Errors\APIException('Failed to get customer', $response->statusCode ?? 500, '', null);
    }

    /**
     * Update a customer.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateCustomer(string $customerId, Components\CustomerUpdate $request): Components\CustomerIndividual|Components\CustomerTeam
    {
        $fake = self::recordIfFaking('updateCustomer', [$customerId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customers->update(id: $customerId, customerUpdate: $request);

        if ($response->statusCode === 200 && $response->customer !== null) {
            return $response->customer;
        }

        throw new Errors\APIException('Failed to update customer', $response->statusCode ?? 500, '', null);
    }

    /**
     * Delete a customer.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function deleteCustomer(string $customerId): void
    {
        $fake = self::recordIfFaking('deleteCustomer', [$customerId]);
        if ($fake[0]) {
            return;
        }

        $sdk = self::sdk();

        $response = $sdk->customers->delete(id: $customerId);

        if ($response->statusCode !== 200 && $response->statusCode !== 204) {
            throw new Errors\APIException('Failed to delete customer', $response->statusCode, '', null);
        }
    }

    /**
     * List all customers.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listCustomers(?Operations\CustomersListRequest $request = null): Operations\CustomersListResponse
    {
        $fake = self::recordIfFaking('listCustomers', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $request ??= new Operations\CustomersListRequest();

        $generator = $sdk->customers->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list customers', 500, '', null);
    }

    /**
     * Get a customer by external ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getCustomerByExternalId(string $externalId): Components\CustomerIndividual|Components\CustomerTeam
    {
        $fake = self::recordIfFaking('getCustomerByExternalId', [$externalId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customers->getExternal(externalId: $externalId);

        if ($response->statusCode === 200 && $response->customer !== null) {
            return $response->customer;
        }

        throw new Errors\APIException('Failed to get customer by external ID', $response->statusCode ?? 500, '', null);
    }

    /**
     * Get a customer's state.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getCustomerState(string $customerId): Components\CustomerStateIndividual|Components\CustomerStateTeam
    {
        $fake = self::recordIfFaking('getCustomerState', [$customerId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customers->getState(id: $customerId);

        if ($response->statusCode === 200 && $response->customerState !== null) {
            return $response->customerState;
        }

        throw new Errors\APIException('Failed to get customer state', $response->statusCode ?? 500, '', null);
    }

    // ──────────────────────────────────────────────────────────────
    //  Subscription CRUD
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a subscription.
     *
     * @param Components\SubscriptionCreateCustomer|Components\SubscriptionCreateExternalCustomer $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createSubscription(Components\SubscriptionCreateCustomer|Components\SubscriptionCreateExternalCustomer $request): Components\Subscription
    {
        $fake = self::recordIfFaking('createSubscription', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->subscriptions->create(request: $request);

        if ($response->statusCode === 201 && $response->subscription !== null) {
            return $response->subscription;
        }

        throw new Errors\APIException('Failed to create subscription', $response->statusCode ?? 500, '', null);
    }

    /**
     * List all subscriptions.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listSubscriptions(?Operations\SubscriptionsListRequest $request = null): Operations\SubscriptionsListResponse
    {
        $fake = self::recordIfFaking('listSubscriptions', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $request ??= new Operations\SubscriptionsListRequest();

        $generator = $sdk->subscriptions->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list subscriptions', 500, '', null);
    }

    /**
     * Get a subscription by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getSubscription(string $subscriptionId): Components\Subscription
    {
        $fake = self::recordIfFaking('getSubscription', [$subscriptionId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->subscriptions->get(id: $subscriptionId);

        if ($response->statusCode === 200 && $response->subscription !== null) {
            return $response->subscription;
        }

        throw new Errors\APIException('Failed to get subscription', $response->statusCode ?? 500, '', null);
    }

    /**
     * Revoke a subscription (cancel immediately).
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function revokeSubscription(string $subscriptionId): Components\Subscription
    {
        $fake = self::recordIfFaking('revokeSubscription', [$subscriptionId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->subscriptions->revoke(id: $subscriptionId);

        if ($response->statusCode === 200 && $response->subscription !== null) {
            return $response->subscription;
        }

        throw new Errors\APIException('Failed to revoke subscription', $response->statusCode ?? 500, '', null);
    }

    // ──────────────────────────────────────────────────────────────
    //  Order CRUD + Invoicing
    // ──────────────────────────────────────────────────────────────

    /**
     * List all orders.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listOrders(?Operations\OrdersListRequest $request = null): Operations\OrdersListResponse
    {
        $fake = self::recordIfFaking('listOrders', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $request ??= new Operations\OrdersListRequest();

        $generator = $sdk->orders->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list orders', 500, '', null);
    }

    /**
     * Get an order by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getOrder(string $orderId): Components\Order
    {
        $fake = self::recordIfFaking('getOrder', [$orderId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->orders->get(id: $orderId);

        if ($response->statusCode === 200 && $response->order !== null) {
            return $response->order;
        }

        throw new Errors\APIException('Failed to get order', $response->statusCode ?? 500, '', null);
    }

    /**
     * Get an order's invoice data.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getOrderInvoice(string $orderId): Components\OrderInvoice
    {
        $fake = self::recordIfFaking('getOrderInvoice', [$orderId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->orders->invoice(id: $orderId);

        if ($response->statusCode === 200 && $response->orderInvoice !== null) {
            return $response->orderInvoice;
        }

        throw new Errors\APIException('Failed to get order invoice', $response->statusCode ?? 500, '', null);
    }

    /**
     * Generate/trigger invoice creation for an order.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function generateOrderInvoice(string $orderId): void
    {
        $fake = self::recordIfFaking('generateOrderInvoice', [$orderId]);
        if ($fake[0]) {
            return;
        }

        $sdk = self::sdk();

        $response = $sdk->orders->generateInvoice(id: $orderId);

        if ($response->statusCode !== 202) {
            throw new Errors\APIException('Failed to generate order invoice', $response->statusCode, '', null);
        }
    }

    /**
     * Create a refund.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createRefund(Components\RefundCreate $request): Components\Refund
    {
        $fake = self::recordIfFaking('createRefund', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->refunds->create(request: $request);

        if ($response->statusCode === 201 && $response->refund !== null) {
            return $response->refund;
        }

        throw new Errors\APIException('Failed to create refund', $response->statusCode ?? 500, '', null);
    }

    /**
     * List all refunds.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listRefunds(?Operations\RefundsListRequest $request = null): Operations\RefundsListResponse
    {
        $fake = self::recordIfFaking('listRefunds', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $request ??= new Operations\RefundsListRequest();

        $generator = $sdk->refunds->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list refunds', 500, '', null);
    }

    // ──────────────────────────────────────────────────────────────
    //  Discounts
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a discount.
     *
     * @param Components\DiscountFixedOnceForeverDurationCreate|Components\DiscountFixedRepeatDurationCreate|Components\DiscountPercentageOnceForeverDurationCreate|Components\DiscountPercentageRepeatDurationCreate $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createDiscount(Components\DiscountFixedOnceForeverDurationCreate|Components\DiscountFixedRepeatDurationCreate|Components\DiscountPercentageOnceForeverDurationCreate|Components\DiscountPercentageRepeatDurationCreate $request): Components\DiscountFixedOnceForeverDuration|Components\DiscountFixedRepeatDuration|Components\DiscountPercentageOnceForeverDuration|Components\DiscountPercentageRepeatDuration
    {
        $fake = self::recordIfFaking('createDiscount', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->discounts->create(request: $request);

        if ($response->statusCode === 201 && $response->discount !== null) {
            return $response->discount;
        }

        throw new Errors\APIException('Failed to create discount', $response->statusCode ?? 500, '', null);
    }

    /**
     * List all discounts.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listDiscounts(?Operations\DiscountsListRequest $request = null): Operations\DiscountsListResponse
    {
        $fake = self::recordIfFaking('listDiscounts', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $request ??= new Operations\DiscountsListRequest();

        $generator = $sdk->discounts->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list discounts', 500, '', null);
    }

    /**
     * Get a discount by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getDiscount(string $discountId): Components\DiscountFixedOnceForeverDuration|Components\DiscountFixedRepeatDuration|Components\DiscountPercentageOnceForeverDuration|Components\DiscountPercentageRepeatDuration
    {
        $fake = self::recordIfFaking('getDiscount', [$discountId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->discounts->get(id: $discountId);

        if ($response->statusCode === 200 && $response->discount !== null) {
            return $response->discount;
        }

        throw new Errors\APIException('Failed to get discount', $response->statusCode ?? 500, '', null);
    }

    /**
     * Update a discount.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateDiscount(string $discountId, Components\DiscountUpdate $request): Components\DiscountFixedOnceForeverDuration|Components\DiscountFixedRepeatDuration|Components\DiscountPercentageOnceForeverDuration|Components\DiscountPercentageRepeatDuration
    {
        $fake = self::recordIfFaking('updateDiscount', [$discountId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->discounts->update(id: $discountId, discountUpdate: $request);

        if ($response->statusCode === 200 && $response->discount !== null) {
            return $response->discount;
        }

        throw new Errors\APIException('Failed to update discount', $response->statusCode ?? 500, '', null);
    }

    /**
     * Delete a discount.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function deleteDiscount(string $discountId): void
    {
        $fake = self::recordIfFaking('deleteDiscount', [$discountId]);
        if ($fake[0]) {
            return;
        }

        $sdk = self::sdk();

        $response = $sdk->discounts->delete(id: $discountId);

        if ($response->statusCode !== 200 && $response->statusCode !== 204) {
            throw new Errors\APIException('Failed to delete discount', $response->statusCode, '', null);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  License Keys
    // ──────────────────────────────────────────────────────────────

    /**
     * List all license keys.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listLicenseKeys(?Operations\LicenseKeysListRequest $request = null): Operations\LicenseKeysListResponse
    {
        $fake = self::recordIfFaking('listLicenseKeys', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $request ??= new Operations\LicenseKeysListRequest();

        $generator = $sdk->licenseKeys->list(
            organizationId: $request->organizationId,
            benefitId: $request->benefitId,
            page: $request->page,
            limit: $request->limit,
        );

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list license keys', 500, '', null);
    }

    /**
     * Get a license key by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getLicenseKey(string $licenseKeyId): Components\LicenseKeyWithActivations
    {
        $fake = self::recordIfFaking('getLicenseKey', [$licenseKeyId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->licenseKeys->get(id: $licenseKeyId);

        if ($response->statusCode === 200 && $response->licenseKeyWithActivations !== null) {
            return $response->licenseKeyWithActivations;
        }

        throw new Errors\APIException('Failed to get license key', $response->statusCode ?? 500, '', null);
    }

    /**
     * Validate a license key.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function validateLicenseKey(Components\LicenseKeyValidate $request): Components\ValidatedLicenseKey
    {
        $fake = self::recordIfFaking('validateLicenseKey', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->licenseKeys->validate(request: $request);

        if ($response->statusCode === 200 && $response->validatedLicenseKey !== null) {
            return $response->validatedLicenseKey;
        }

        throw new Errors\APIException('Failed to validate license key', $response->statusCode ?? 500, '', null);
    }

    /**
     * Activate a license key.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function activateLicenseKey(Components\LicenseKeyActivate $request): Components\LicenseKeyActivationRead
    {
        $fake = self::recordIfFaking('activateLicenseKey', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->licenseKeys->activate(request: $request);

        if ($response->statusCode === 200 && $response->licenseKeyActivationRead !== null) {
            return $response->licenseKeyActivationRead;
        }

        throw new Errors\APIException('Failed to activate license key', $response->statusCode ?? 500, '', null);
    }

    /**
     * Deactivate a license key.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function deactivateLicenseKey(Components\LicenseKeyDeactivate $request): void
    {
        $fake = self::recordIfFaking('deactivateLicenseKey', [$request]);
        if ($fake[0]) {
            return;
        }

        $sdk = self::sdk();

        $response = $sdk->licenseKeys->deactivate(request: $request);

        if ($response->statusCode !== 200 && $response->statusCode !== 204) {
            throw new Errors\APIException('Failed to deactivate license key', $response->statusCode, '', null);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  Products Full CRUD
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a product.
     *
     * @param Components\ProductCreateRecurring|Components\ProductCreateOneTime $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createProduct(Components\ProductCreateRecurring|Components\ProductCreateOneTime $request): Components\Product
    {
        $fake = self::recordIfFaking('createProduct', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->products->create(request: $request);

        if ($response->statusCode === 201 && $response->product !== null) {
            return $response->product;
        }

        throw new Errors\APIException('Failed to create product', $response->statusCode ?? 500, '', null);
    }

    /**
     * Get a product by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getProduct(string $productId): Components\Product
    {
        $fake = self::recordIfFaking('getProduct', [$productId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->products->get(id: $productId);

        if ($response->statusCode === 200 && $response->product !== null) {
            return $response->product;
        }

        throw new Errors\APIException('Failed to get product', $response->statusCode ?? 500, '', null);
    }

    /**
     * Update a product.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateProduct(string $productId, Components\ProductUpdate $request): Components\Product
    {
        $fake = self::recordIfFaking('updateProduct', [$productId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->products->update(id: $productId, productUpdate: $request);

        if ($response->statusCode === 200 && $response->product !== null) {
            return $response->product;
        }

        throw new Errors\APIException('Failed to update product', $response->statusCode ?? 500, '', null);
    }

    /**
     * Update a product's benefits.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateProductBenefits(string $productId, Components\ProductBenefitsUpdate $request): Components\Product
    {
        $fake = self::recordIfFaking('updateProductBenefits', [$productId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->products->updateBenefits(id: $productId, productBenefitsUpdate: $request);

        if ($response->statusCode === 200 && $response->product !== null) {
            return $response->product;
        }

        throw new Errors\APIException('Failed to update product benefits', $response->statusCode ?? 500, '', null);
    }

    // ──────────────────────────────────────────────────────────────
    //  Custom Fields Full CRUD
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a custom field.
     *
     * @param Components\CustomFieldCreateText|Components\CustomFieldCreateNumber|Components\CustomFieldCreateDate|Components\CustomFieldCreateCheckbox|Components\CustomFieldCreateSelect $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createCustomField(Components\CustomFieldCreateText|Components\CustomFieldCreateNumber|Components\CustomFieldCreateDate|Components\CustomFieldCreateCheckbox|Components\CustomFieldCreateSelect $request): Components\CustomFieldText|Components\CustomFieldNumber|Components\CustomFieldDate|Components\CustomFieldCheckbox|Components\CustomFieldSelect
    {
        $fake = self::recordIfFaking('createCustomField', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customFields->create(request: $request);

        if ($response->statusCode === 201 && $response->customField !== null) {
            return $response->customField;
        }

        throw new Errors\APIException('Failed to create custom field', $response->statusCode ?? 500, '', null);
    }

    /**
     * Update a custom field.
     *
     * @param Components\CustomFieldUpdateText|Components\CustomFieldUpdateNumber|Components\CustomFieldUpdateDate|Components\CustomFieldUpdateCheckbox|Components\CustomFieldUpdateSelect $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateCustomField(string $customFieldId, Components\CustomFieldUpdateText|Components\CustomFieldUpdateNumber|Components\CustomFieldUpdateDate|Components\CustomFieldUpdateCheckbox|Components\CustomFieldUpdateSelect $request): Components\CustomFieldText|Components\CustomFieldNumber|Components\CustomFieldDate|Components\CustomFieldCheckbox|Components\CustomFieldSelect
    {
        $fake = self::recordIfFaking('updateCustomField', [$customFieldId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customFields->update(customFieldUpdate: $request, id: $customFieldId);

        if ($response->statusCode === 200 && $response->customField !== null) {
            return $response->customField;
        }

        throw new Errors\APIException('Failed to update custom field', $response->statusCode ?? 500, '', null);
    }

    /**
     * Delete a custom field.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function deleteCustomField(string $customFieldId): void
    {
        $fake = self::recordIfFaking('deleteCustomField', [$customFieldId]);
        if ($fake[0]) {
            return;
        }

        $sdk = self::sdk();

        $response = $sdk->customFields->delete(id: $customFieldId);

        if ($response->statusCode !== 200 && $response->statusCode !== 204) {
            throw new Errors\APIException('Failed to delete custom field', $response->statusCode, '', null);
        }
    }

    /**
     * List custom fields.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listCustomFields(?Operations\CustomFieldsListRequest $request = null): Operations\CustomFieldsListResponse
    {
        $fake = self::recordIfFaking('listCustomFields', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        if ($request === null) {
            $request = new Operations\CustomFieldsListRequest();
        }

        $generator = $sdk->customFields->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list custom fields', 500, '', null);
    }

    /**
     * Get a specific custom field by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getCustomField(string $customFieldId): Components\CustomFieldText|Components\CustomFieldNumber|Components\CustomFieldDate|Components\CustomFieldCheckbox|Components\CustomFieldSelect
    {
        $fake = self::recordIfFaking('getCustomField', [$customFieldId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customFields->get(id: $customFieldId);

        if ($response->statusCode === 200 && $response->customField !== null) {
            return $response->customField;
        }

        throw new Errors\APIException('Failed to get custom field', $response->statusCode ?? 500, '', null);
    }

    // ──────────────────────────────────────────────────────────────
    //  Checkout Links Full CRUD
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a checkout link.
     *
     * @param Components\CheckoutLinkCreateProductPrice|Components\CheckoutLinkCreateProduct|Components\CheckoutLinkCreateProducts $request
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createCheckoutLink(Components\CheckoutLinkCreateProductPrice|Components\CheckoutLinkCreateProduct|Components\CheckoutLinkCreateProducts $request): Components\CheckoutLink
    {
        $fake = self::recordIfFaking('createCheckoutLink', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->checkoutLinks->create(request: $request);

        if ($response->statusCode === 201 && $response->checkoutLink !== null) {
            return $response->checkoutLink;
        }

        throw new Errors\APIException('Failed to create checkout link', $response->statusCode ?? 500, '', null);
    }

    /**
     * Update a checkout link.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateCheckoutLink(string $checkoutLinkId, Components\CheckoutLinkUpdate $request): Components\CheckoutLink
    {
        $fake = self::recordIfFaking('updateCheckoutLink', [$checkoutLinkId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->checkoutLinks->update(checkoutLinkUpdate: $request, id: $checkoutLinkId);

        if ($response->statusCode === 200 && $response->checkoutLink !== null) {
            return $response->checkoutLink;
        }

        throw new Errors\APIException('Failed to update checkout link', $response->statusCode ?? 500, '', null);
    }

    /**
     * Delete a checkout link.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function deleteCheckoutLink(string $checkoutLinkId): void
    {
        $fake = self::recordIfFaking('deleteCheckoutLink', [$checkoutLinkId]);
        if ($fake[0]) {
            return;
        }

        $sdk = self::sdk();

        $response = $sdk->checkoutLinks->delete(id: $checkoutLinkId);

        if ($response->statusCode !== 200 && $response->statusCode !== 204) {
            throw new Errors\APIException('Failed to delete checkout link', $response->statusCode, '', null);
        }
    }

    /**
     * List checkout links.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listCheckoutLinks(?Operations\CheckoutLinksListRequest $request = null): Operations\CheckoutLinksListResponse
    {
        $fake = self::recordIfFaking('listCheckoutLinks', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        if ($request === null) {
            $request = new Operations\CheckoutLinksListRequest();
        }

        $generator = $sdk->checkoutLinks->list(request: $request);

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list checkout links', 500, '', null);
    }

    /**
     * Get a specific checkout link by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getCheckoutLink(string $checkoutLinkId): Components\CheckoutLink
    {
        $fake = self::recordIfFaking('getCheckoutLink', [$checkoutLinkId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->checkoutLinks->get(id: $checkoutLinkId);

        if ($response->statusCode === 200 && $response->checkoutLink !== null) {
            return $response->checkoutLink;
        }

        throw new Errors\APIException('Failed to get checkout link', $response->statusCode ?? 500, '', null);
    }

    // ──────────────────────────────────────────────────────────────
    //  Seats (admin)
    // ──────────────────────────────────────────────────────────────

    /**
     * List the seats on a subscription or order.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listSeats(?string $subscriptionId = null, ?string $orderId = null): Components\SeatsList
    {
        $fake = self::recordIfFaking('listSeats', [$subscriptionId, $orderId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customerSeats->listSeats(subscriptionId: $subscriptionId, orderId: $orderId);

        if ($response->statusCode === 200 && $response->seatsList !== null) {
            return $response->seatsList;
        }

        throw new Errors\APIException('Failed to list seats', $response->statusCode ?? 500, '', null);
    }

    /**
     * Assign a seat to a member by email, customer id, or external id.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function assignSeat(Components\SeatAssign $request): Components\CustomerSeat
    {
        $fake = self::recordIfFaking('assignSeat', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customerSeats->assignSeat(request: $request);

        if ($response->statusCode === 200 && $response->customerSeat !== null) {
            return $response->customerSeat;
        }

        throw new Errors\APIException('Failed to assign seat', $response->statusCode ?? 500, '', null);
    }

    /**
     * Revoke a seat from a member.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function revokeSeat(string $seatId): Components\CustomerSeat
    {
        $fake = self::recordIfFaking('revokeSeat', [$seatId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customerSeats->revokeSeat(seatId: $seatId);

        if ($response->statusCode === 200 && $response->customerSeat !== null) {
            return $response->customerSeat;
        }

        throw new Errors\APIException('Failed to revoke seat', $response->statusCode ?? 500, '', null);
    }

    /**
     * Resend the invitation email for a pending seat.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function resendSeatInvitation(string $seatId): Components\CustomerSeat
    {
        $fake = self::recordIfFaking('resendSeatInvitation', [$seatId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customerSeats->resendInvitation(seatId: $seatId);

        if ($response->statusCode === 200 && $response->customerSeat !== null) {
            return $response->customerSeat;
        }

        throw new Errors\APIException('Failed to resend seat invitation', $response->statusCode ?? 500, '', null);
    }

    // ──────────────────────────────────────────────────────────────
    //  Metrics, Organizations & Files
    // ──────────────────────────────────────────────────────────────

    /**
     * Fetch Polar metrics (analytics) for a given period.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getMetrics(Operations\MetricsGetRequest $request): Components\MetricsResponse
    {
        $fake = self::recordIfFaking('getMetrics', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->metrics->get(request: $request);

        if ($response->statusCode === 200 && $response->metricsResponse !== null) {
            return $response->metricsResponse;
        }

        throw new Errors\APIException('Failed to get metrics', $response->statusCode ?? 500, '', null);
    }

    /**
     * List organizations the authenticated access token has access to.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listOrganizations(?string $slug = null, ?int $page = null, ?int $limit = null): Operations\OrganizationsListResponse
    {
        $fake = self::recordIfFaking('listOrganizations', [$slug, $page, $limit]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $generator = $sdk->organizations->list(
            slug: $slug,
            page: $page,
            limit: $limit,
        );

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list organizations', 500, '', null);
    }

    /**
     * Get a single organization by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getOrganization(string $organizationId): Components\Organization
    {
        $fake = self::recordIfFaking('getOrganization', [$organizationId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->organizations->get(id: $organizationId);

        if ($response->statusCode === 200 && $response->organization !== null) {
            return $response->organization;
        }

        throw new Errors\APIException('Failed to get organization', $response->statusCode ?? 500, '', null);
    }

    /**
     * List files (admin-scoped).
     *
     * @param  string|array<string>|null  $organizationId
     * @param  string|array<string>|null  $ids
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function listFiles(string|array|null $organizationId = null, string|array|null $ids = null, ?int $page = null, ?int $limit = null): Operations\FilesListResponse
    {
        $fake = self::recordIfFaking('listFiles', [$organizationId, $ids, $page, $limit]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $generator = $sdk->files->list(
            organizationId: $organizationId,
            ids: $ids,
            page: $page,
            limit: $limit,
        );

        foreach ($generator as $response) {
            if ($response->statusCode === 200) {
                return $response;
            }
        }

        throw new Errors\APIException('Failed to list files', 500, '', null);
    }

    /**
     * Update a license key (admin-scoped).
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateLicenseKey(string $licenseKeyId, Components\LicenseKeyUpdate $request): Components\LicenseKeyRead
    {
        $fake = self::recordIfFaking('updateLicenseKey', [$licenseKeyId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->licenseKeys->update(licenseKeyUpdate: $request, id: $licenseKeyId);

        if ($response->statusCode === 200 && $response->licenseKeyRead !== null) {
            return $response->licenseKeyRead;
        }

        throw new Errors\APIException('Failed to update license key', $response->statusCode ?? 500, '', null);
    }

    /**
     * Replace the SDK with a fake for testing.
     */
    public static function fake(): PolarFake
    {
        self::$fakeInstance = PolarFake::install();

        return self::$fakeInstance;
    }

    /**
     * Get the active fake instance, if any.
     */
    public static function getFake(): ?PolarFake
    {
        return self::$fakeInstance;
    }

    /**
     * Reset the cached SDK instance (useful for testing).
     */
    public static function resetSdk(): void
    {
        self::$sdkInstance = null;
        self::$fakeInstance = null;
    }

    /**
     * Set the SDK instance (useful for testing).
     */
    public static function setSdk(?Polar $sdk): void
    {
        self::$sdkInstance = $sdk;
    }

    /**
     * Get or create a cached Polar SDK instance.
     *
     * @throws PolarApiError
     */
    public static function sdk(): Polar
    {
        if (self::$sdkInstance !== null) {
            return self::$sdkInstance;
        }

        if (empty($apiKey = config('polar.access_token'))) {
            throw new PolarApiError('Polar API key not set.');
        }

        self::$sdkInstance = Polar::builder()
            ->setSecurity($apiKey)
            ->setServer(config('polar.server', 'sandbox'))
            ->build();

        return self::$sdkInstance;
    }

    /**
     * Set the customer model class name.
     */
    public static function useCustomerModel(string $customerModel): void
    {
        static::$customerModel = $customerModel;
    }

    /**
     * Set the subscription model class name.
     */
    public static function useSubscriptionModel(string $subscriptionModel): void
    {
        static::$subscriptionModel = $subscriptionModel;
    }

    /**
     * Set the order model class name.
     */
    public static function useOrderModel(string $orderModel): void
    {
        static::$orderModel = $orderModel;
    }
}
