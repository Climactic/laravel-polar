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

        if ($response->statusCode !== 202) {
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
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function createCustomer(Components\CustomerCreate $request): Components\CustomerWithMembers
    {
        $fake = self::recordIfFaking('createCustomer', [$request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customers->create(request: $request);

        if ($response->statusCode === 201 && $response->customerWithMembers !== null) {
            return $response->customerWithMembers;
        }

        throw new Errors\APIException('Failed to create customer', $response->statusCode ?? 500, '', null);
    }

    /**
     * Get a customer by ID.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getCustomer(string $customerId): Components\CustomerWithMembers
    {
        $fake = self::recordIfFaking('getCustomer', [$customerId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customers->get(id: $customerId);

        if ($response->statusCode === 200 && $response->customerWithMembers !== null) {
            return $response->customerWithMembers;
        }

        throw new Errors\APIException('Failed to get customer', $response->statusCode ?? 500, '', null);
    }

    /**
     * Update a customer.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function updateCustomer(string $customerId, Components\CustomerUpdate $request): Components\CustomerWithMembers
    {
        $fake = self::recordIfFaking('updateCustomer', [$customerId, $request]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customers->update(id: $customerId, customerUpdate: $request);

        if ($response->statusCode === 200 && $response->customerWithMembers !== null) {
            return $response->customerWithMembers;
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
    public static function getCustomerByExternalId(string $externalId): Components\CustomerWithMembers
    {
        $fake = self::recordIfFaking('getCustomerByExternalId', [$externalId]);
        if ($fake[0]) {
            return $fake[1];
        }

        $sdk = self::sdk();

        $response = $sdk->customers->getExternal(externalId: $externalId);

        if ($response->statusCode === 200 && $response->customerWithMembers !== null) {
            return $response->customerWithMembers;
        }

        throw new Errors\APIException('Failed to get customer by external ID', $response->statusCode ?? 500, '', null);
    }

    /**
     * Get a customer's state.
     *
     * @throws Errors\APIException
     * @throws PolarApiError
     */
    public static function getCustomerState(string $customerId): Components\CustomerState
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
