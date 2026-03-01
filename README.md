![](https://banners.beyondco.de/Laravel%20Polar.png?theme=light&packageManager=composer+require&packageName=climactic%2Flaravel-polar&pattern=pieFactory&style=style_1&description=Polar.sh+Laravel+Integration&md=1&showWatermark=1&fontSize=100px&images=https://github.com/polarsource/polar/raw/refs/heads/main/server/polar/backoffice/static/logo.light.svg "Laravel Polar")

# Laravel Polar

[![Latest Version on Packagist](https://img.shields.io/packagist/v/climactic/laravel-polar.svg?style=flat-square)](https://packagist.org/packages/climactic/laravel-polar)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/climactic/laravel-polar/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/climactic/laravel-polar/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/climactic/laravel-polar/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/climactic/laravel-polar/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/climactic/laravel-polar.svg?style=flat-square)](https://packagist.org/packages/climactic/laravel-polar)

Seamlessly integrate Polar.sh subscriptions and payments into your Laravel application. This package provides an elegant way to handle subscriptions, manage recurring payments, and interact with Polar's API. With built-in support for webhooks, subscription management, and a fluent API, you can focus on building your application while we handle the complexities of subscription billing.

## Installation

**Step 1:** You can install the package via composer:

```bash
composer require climactic/laravel-polar
```

**Step 2:** Run `:install`:

```bash
php artisan polar:install
```

This will publish the config, migrations and views, and ask to run the migrations.

Or publish and run the migrations individually:

```bash
php artisan vendor:publish --tag="polar-migrations"
```

```bash
php artisan vendor:publish --tag="polar-config"
```

```bash
php artisan vendor:publish --tag="polar-views"
```

```bash
php artisan migrate
```

This is the contents of the published config file:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Polar Access Token
    |--------------------------------------------------------------------------
    |
    | The Polar access token is used to authenticate with the Polar API.
    | You can find your access token in the Polar dashboard > Settings
    | under the "Developers" section.
    |
    */
    'access_token' => env('POLAR_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Polar Server
    |--------------------------------------------------------------------------
    |
    | The Polar server environment to use for API requests.
    | Available options: "production" or "sandbox"
    |
    | - production: https://api.polar.sh (Production environment)
    | - sandbox: https://sandbox-api.polar.sh (Sandbox environment)
    |
    */
    'server' => env('POLAR_SERVER', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Polar Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The Polar webhook secret is used to verify that the webhook requests
    | are coming from Polar. You can find your webhook secret in the Polar
    | dashboard > Settings > Webhooks on each registered webhook.
    |
    | We (the developers) recommend using a single webhook for all your
    | integrations. This way you can use the same secret for all your
    | integrations and you don't have to manage multiple webhooks.
    |
    */
    'webhook_secret' => env('POLAR_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Polar Url Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI where routes from Polar will be served
    | from. The URL built into Polar is used by default; however,
    | you can modify this path as you see fit for your application.
    |
    */
    'path' => env('POLAR_PATH', 'polar'),

    /*
    |--------------------------------------------------------------------------
    | Default Redirect URL
    |--------------------------------------------------------------------------
    |
    | This is the default redirect URL that will be used when a customer
    | is redirected back to your application after completing a purchase
    | from a checkout session in your Polar account.
    |
    */
    'redirect_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Currency Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale in which your money values are formatted in
    | for display. To utilize other locales besides the default "en" locale
    | verify you have to have the "intl" PHP extension installed on the system.
    |
    */
    'currency_locale' => env('POLAR_CURRENCY_LOCALE', 'en'),

    'organization_id' => env('POLAR_ORGANIZATION_ID'),

    'middleware_redirect_url' => null,

    'webhook_handlers' => [
        // 'subscription.created' => App\Webhooks\CustomHandler::class,
    ],
];
```

## Usage

### Access Token

Configure your access token. Create a new token in the Polar Dashboard > Settings > Developers and paste it in the `.env` file.

- https://sandbox.polar.sh/dashboard/<org_slug>/settings (Sandbox)
- https://polar.sh/dashboard/<org_slug>/settings (Production)

```bash
POLAR_ACCESS_TOKEN="<your_access_token>"
```

### Webhook Secret

Configure your webhook secret. Create a new webhook in the Polar Dashboard > Settings > Webhooks.

- https://sandbox.polar.sh/dashboard/<org_slug>/settings/webhooks (Sandbox)
- https://polar.sh/dashboard/<org_slug>/settings/webhooks (Production)

Configure the webhook for the following events that this package supports:

- `order.created`
- `order.updated`
- `order.paid`
- `order.refunded`
- `subscription.created`
- `subscription.updated`
- `subscription.active`
- `subscription.canceled`
- `subscription.uncanceled`
- `subscription.revoked`
- `subscription.past_due`
- `benefit_grant.created`
- `benefit_grant.updated`
- `benefit_grant.revoked`
- `benefit_grant.cycled`
- `refund.created`
- `refund.updated`
- `checkout.created`
- `checkout.updated`
- `checkout.expired`
- `customer.created`
- `customer.updated`
- `customer.deleted`
- `customer.state_changed`
- `customer_seat.assigned`
- `customer_seat.claimed`
- `customer_seat.revoked`
- `organization.updated`
- `product.created`
- `product.updated`
- `benefit.created`
- `benefit.updated`

```bash
POLAR_WEBHOOK_SECRET="<your_webhook_secret>"
```

### Billable Trait

Let’s make sure everything’s ready for your customers to checkout smoothly. 🛒

First, we’ll need to set up a model to handle billing—don’t worry, it’s super simple! In most cases, this will be your app’s User model. Just add the Billable trait to your model like this (you’ll import it from the package first, of course):

```php
use Climactic\LaravelPolar\Billable;

class User extends Authenticatable
{
    use Billable;
}
```

Now the user model will have access to the methods provided by the package. You can make any model billable by adding the trait to it, not just the User model.

### Polar Script

Polar includes a JavaScript script that you can use to initialize the [Polar Embedded Checkout](https://docs.polar.sh/features/checkout/embed). If you going to use this functionality, you can use the `@polarEmbedScript` directive to include the script in your views inside the `<head>` tag.

```blade
<head>
    ...

    @polarEmbedScript
</head>
```

### Blade Directives

The package provides Blade directives for conditionally rendering content based on subscription status.

#### `@subscribed`

Check if the authenticated user (or an explicit billable) has a valid subscription:

```blade
@subscribed
    You have an active subscription!
@else
    Please subscribe to access this content.
@endsubscribed
```

You can pass a subscription type and/or product ID:

```blade
@subscribed('pro')
    Welcome, Pro member!
@endsubscribed

@subscribed('default', 'product_id_123')
    You're on the right plan.
@endsubscribed
```

You can also pass an explicit billable model as the first argument:

```blade
@subscribed($team)
    Team is subscribed.
@endsubscribed

@subscribed($team, 'pro')
    Team has a Pro plan.
@endsubscribed
```

#### `@onTrial`

Check if the authenticated user's subscription is currently trialing:

```blade
@onTrial
    You're on a free trial!
@else
    Your trial has ended.
@endonTrial
```

Accepts a subscription type or an explicit billable, just like `@subscribed`:

```blade
@onTrial('pro')
    Your Pro trial is active.
@endonTrial

@onTrial($user)
    This user is trialing.
@endonTrial
```

### Route Middleware

The `polar.subscribed` middleware protects routes so only subscribed users can access them:

```php
Route::middleware('polar.subscribed')->group(function () {
    Route::get('/dashboard', DashboardController::class);
});
```

The middleware accepts two optional parameters — **subscription type** (the label you assign when creating [multiple subscriptions](#multiple-subscriptions), defaults to `'default'`) and **product ID** (a specific Polar product the subscription must be for):

```php
// Require a subscription of type "pro"
Route::middleware('polar.subscribed:pro')->group(function () {
    // ...
});

// Require a "default" subscription on a specific Polar product
Route::middleware('polar.subscribed:default,product_id_123')->group(function () {
    // ...
});
```

For API requests (when `Accept: application/json` is present), a 403 JSON response is returned. For web requests, you can configure a redirect URL in `config/polar.php`:

```php
'middleware_redirect_url' => '/billing',
```

If `middleware_redirect_url` is `null` (the default), a 403 response is returned for web requests as well.

### Webhooks

This package includes a webhook handler that will handle the webhooks from Polar.

#### Webhooks & CSRF Protection

Incoming webhooks should not be affected by [CSRF protection](https://laravel.com/docs/csrf). To prevent this, exclude `polar/*` in your application's `bootstrap/app.php` file:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'polar/*',
    ]);
})
```

### Commands

This package includes a list of commands that you can use to retrieve information about your Polar account.

| Command | Description |
|---------|-------------|
| `php artisan polar:products` | List all available products with their ids |
| `php artisan polar:products --id=123` | List a specific product by id |
| `php artisan polar:products --id=123 --id=321` | List a specific products by ids |

### Checkouts

#### Single Payments

To create a checkout to show only a single payment, pass a single items to the array of products when creating the checkout.

```php
use Illuminate\Http\Request;

Route::get('/subscribe', function (Request $request) {
    return $request->user()->checkout(['product_id_123']);
});
```

If you want to show multiple products that the user can choose from, you can pass an array of product ids to the `checkout` method.

```php
use Illuminate\Http\Request;

Route::get('/subscribe', function (Request $request) {
    return $request->user()->checkout(['product_id_123', 'product_id_456']);
});
```

This could be useful if you want to offer monthly, yearly, and lifetime plans for example.

> [!NOTE]
> If you are requesting the checkout a lot of times we recommend you to cache the URL returned by the `checkout` method.

#### Custom Price

You can override the price of a product using the `charge` method.

```php
use Illuminate\Http\Request;

Route::get('/subscribe', function (Request $request) {
    return $request->user()->charge(1000, ['product_id_123']);
});
```

#### Embedded Checkout

Instead of redirecting the user you can create the checkout link, pass it to the page and use our blade component:

```php
use Illuminate\Http\Request;

Route::get('/billing', function (Request $request) {
    $checkout = $request->user()->checkout(['product_id_123'])
        ->withEmbedOrigin(config('app.url'));

    return view('billing', ['checkout' => $checkout]);
});
```

Now we can use the button like this:

```blade
<x-polar-button :checkout="$checkout" />
```

The component accepts the normal props that a link element accepts. You can change the theme of the embedded checkout by using the following prop:

```blade
<x-polar-button :checkout="$checkout" data-polar-checkout-theme="dark" />
```

It defaults to light theme, so you only need to pass the prop if you want to change it.

##### Inertia

For projects usin Inertia you can render the button adding `data-polar-checkout` to the link in the following way:

`button.vue`
```vue
<template>
  <a href="<CHECKOUT_LINK>" data-polar-checkout>Buy now</a>
</template>
```

```jsx
// button.{jsx,tsx}

export function Button() {
  return (
    <a href="<CHECKOUT_LINK>" data-polar-checkout>Buy now</a>
  );
}
```

At the end is just a normal link but using an special attribute for the script to render the embedded checkout.

> [!NOTE]
> Remember that you can use the theme attribute too to change the color system in the checkout

### Prefill Customer Information

You can override the user data using the following methods in your models provided by the `Billable` trait.

```php
public function polarName(): ?string; // default: $model->name
public function polarEmail(): ?string; // default: $model->email
```

### Redirects After Purchase

You can redirect the user to a custom page after the purchase using the `withSuccessUrl` method:

```php
$request->user()->checkout('variant-id')
    ->withSuccessUrl(url('/success'));
```

You can also add the `checkout_id={CHECKOUT_ID}` query parameter to the URL to retrieve the checkout session id:

```php
$request->user()->checkout('variant-id')
    ->withSuccessUrl(url('/success?checkout_id={CHECKOUT_ID}'));
```

### Custom metadata and customer metadata

You can add custom metadata to the checkout session using the `withMetadata` method:

```php
$request->user()->checkout('variant-id')
    ->withMetadata(['key' => 'value']);
```

You can also add customer metadata to the checkout session using the `withCustomerMetadata` method:

```php
$request->user()->checkout('variant-id')
    ->withCustomerMetadata(['key' => 'value']);
```

These will then be available in the relevant webhooks for you.

#### Reserved Keywords

When working with custom data, this library has a few reserved terms.

- `billable_id`
- `billable_type`
- `subscription_type`

Using any of these will result in an exception being thrown.

### Customers

#### Customer Portal

Customers can update their personal information (e.g., name, email address) by accessing their [self-service customer portal](https://docs.polar.sh/features/customer-portal). To redirect customers to this portal, call the `redirectToCustomerPortal()` method on your billable model (e.g., the User model).

```php
use Illuminate\Http\Request;

Route::get('/customer-portal', function (Request $request) {
    return $request->user()->redirectToCustomerPortal();
});
```

Optionally, you can obtain the signed customer portal URL directly:

```php
$url = $user->customerPortalUrl();
```

### Orders

#### Retrieving Orders

You can retrieve orders by using the `orders` relationship on the billable model:

```blade
<table>
    @foreach ($user->orders as $order)
        <td>{{ $order->ordered_at->toFormattedDateString() }}</td>
        <td>{{ $order->polar_id }}</td>
        <td>{{ $order->amount }}</td>
        <td>{{ $order->tax_amount }}</td>
        <td>{{ $order->refunded_amount }}</td>
        <td>{{ $order->refunded_tax_amount }}</td>
        <td>{{ $order->currency }}</td>
        <!-- Add more columns as needed -->
    @endforeach
</table>
```

#### Check order status

You can check the status of an order by using the `status` attribute:

```php
$order->status;
```

Or you can use some of the helper methods offers by the `Order` model:

```php
$order->paid();
```

Aside from that, you can run two other checks: refunded, and partially refunded.  If the order is refunded, you can utilize the refunded_at timestamp:

```blade
@if ($order->refunded())
    Order {{ $order->polar_id }} was refunded on {{ $order->refunded_at->toFormattedDateString() }}
@endif
```

You may also see if an order was for a certain product:

```php
if ($order->hasProduct('product_id_123')) {
    // ...
}
```

Furthermore, you can check if a consumer has purchased a specific product:

```php
if ($user->hasPurchasedProduct('product_id_123')) {
    // ...
}
```

#### Invoices

You can retrieve and generate invoices for orders:

```php
// Get the invoice data for an order
$invoice = $order->invoice();

// Trigger invoice generation (async)
$order->generateInvoice();
```

Or use the facade directly:

```php
use Climactic\LaravelPolar\LaravelPolar;

$invoice = LaravelPolar::getOrderInvoice('order-id-123');
LaravelPolar::generateOrderInvoice('order-id-123');
```

#### Refunding Orders

You can refund an order (requires the amount in cents):

```php
use Polar\Models\Components\RefundReason;

// Refund a specific amount
$refund = $order->issueRefund(1000);

// Refund with a reason
$refund = $order->issueRefund(1000, RefundReason::CustomerRequest);
```

Or use the facade for more control:

```php
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components\RefundCreate;
use Polar\Models\Components\RefundReason;

$refund = LaravelPolar::createRefund(new RefundCreate(
    orderId: 'order-id-123',
    reason: RefundReason::CustomerRequest,
    amount: 1000,
));

// List all refunds
$refunds = LaravelPolar::listRefunds();
```

#### Listing Orders via API

You can list orders directly from the Polar API:

```php
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Operations\OrdersListRequest;

// List all orders
$orders = LaravelPolar::listOrders();

// List with filters
$orders = LaravelPolar::listOrders(new OrdersListRequest(
    productId: 'product_id_123',
));

// Get a specific order from the API
$order = LaravelPolar::getOrder('order-id-123');
```

### Subscriptions

#### Creating Subscriptions

Starting a subscription is simple. For this, we require our product's variant id. Copy the product id and start a new subscription checkout using your billable model:

```php
use Illuminate\Http\Request;

Route::get('/subscribe', function (Request $request) {
    return $request->user()->subscribe('product_id_123');
});
```

When a customer completes their checkout, the incoming `SubscriptionCreated` event webhook connects it to your billable model in the database. You may then get the subscription from your billable model:

```php
$subscription = $user->subscription();
```

#### Checking Subscription Status

Once a consumer has subscribed to your services, you can use a variety of methods to check on the status of their subscription. The most basic example is to check if a customer has a valid subscription.

```php
if ($user->subscribed()) {
    // ...
}
```

You can utilize this in a variety of locations in your app, such as middleware, rules, and so on, to provide services. To determine whether an individual subscription is valid, you can use the `valid` method:

```php
if ($user->subscription()->valid()) {
    // ...
}
```

This method, like the subscribed method, returns true if your membership is active, on trial, past due, or cancelled during its grace period.

You may also check if a subscription is for a certain product:

```php
if ($user->subscription()->hasProduct('product_id_123')) {
    // ...
}
```

If you wish to check if a subscription is on a specific product while being valid, you can use:

```php
if ($user->subscribedToProduct('product_id_123')) {
    // ...
}
```

Alternatively, if you use different [subscription types](#multiple-subscriptions), you can pass a type as an additional parameter:

```php
if ($user->subscribed('swimming')) {
    // ...
}

if ($user->subscribedToProduct('product_id_123', 'swimming')) {
    // ...
}
```

#### Cancelled Status

To see if a user has cancelled their subscription, you can use the cancelled method:

```php
if ($user->subscription()->cancelled()) {
    // ...
}
```

When they are in their grace period, you can utilize the `onGracePeriod` check.

```php
if ($user->subscription()->onGracePeriod()) {
    // ...
}
```

#### Past Due Status

If a recurring payment fails, the subscription will become past due.  This indicates that the subscription is still valid, but your customer's payments will be retried in two weeks.

```php
if ($user->subscription()->pastDue()) {
    // ...
}
```

#### Subscription Scopes

There are several subscription scopes available for querying subscriptions in specific states:

```php
// Get all active subscriptions...
$subscriptions = Subscription::query()->active()->get();

// Get all of the cancelled subscriptions for a specific user...
$subscriptions = $user->subscriptions()->cancelled()->get();
```

Here's all available scopes:

```php
Subscription::query()->incomplete();
Subscription::query()->incompleteExpired();
Subscription::query()->onTrial();
Subscription::query()->active();
Subscription::query()->pastDue();
Subscription::query()->unpaid();
Subscription::query()->cancelled();
```

#### Changing Plans

When a consumer is on a monthly plan, they may desire to upgrade to a better plan, alter their payments to an annual plan, or drop to a lower-cost plan. In these cases, you can allow them to swap plans by giving a different product id to the `swap` method:

```php
use App\Models\User;

$user = User::find(1);

$user->subscription()->swap('product_id_123');
```

This will change the customer's subscription plan, however billing will not occur until the next payment cycle. If you want to immediately invoice the customer, you can use the `swapAndInvoice` method instead.

```php
$user = User::find(1);

$user->subscription()->swapAndInvoice('product_id_123');
```

#### Multiple Subscriptions

In certain situations, you may wish to allow your consumer to subscribe to numerous subscription kinds.  For example, a gym may provide a swimming and weight lifting subscription.  You can let your customers subscribe to one or both.

To handle the various subscriptions, you can offer a type of subscription as the second argument when creating a new one:

```php
$user = User::find(1);

$checkout = $user->subscribe('product_id_123', 'swimming');
```

You can now always refer to this specific subscription type by passing the type argument when getting it:

```php
$user = User::find(1);

// Retrieve the swimming subscription type...
$subscription = $user->subscription('swimming');

// Swap plans for the gym subscription type...
$user->subscription('gym')->swap('product_id_123');

// Cancel the swimming subscription...
$user->subscription('swimming')->cancel();
```

#### Cancelling Subscriptions

To cancel a subscription, call the `cancel` method.

```php
$user = User::find(1);

$user->subscription()->cancel();
```

This will cause your subscription to be cancelled.  If you cancel your subscription in the middle of the cycle, it will enter a grace period, and the ends_at column will be updated.  The customer will continue to have access to the services offered for the duration of the period.  You may check the grace period by calling the `onGracePeriod` method:

```php
if ($user->subscription()->onGracePeriod()) {
    // ...
}
```

Polar does not offer immediate cancellation.  To resume a subscription while it is still in its grace period, use the resume method.

```php
$user->subscription()->resume();
```

When a cancelled subscription approaches the end of its grace period, it becomes expired and cannot be resumed.

#### Revoking Subscriptions

To immediately revoke a subscription (cancel without a grace period), use the `revoke` method:

```php
$user->subscription()->revoke();
```

This differs from `cancel()` which cancels at the end of the billing period. `revoke()` terminates the subscription immediately.

#### Subscription API Methods

You can interact with subscriptions directly via the Polar API:

```php
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Operations\SubscriptionsListRequest;

// List all subscriptions
$subscriptions = LaravelPolar::listSubscriptions();

// Get a specific subscription
$subscription = LaravelPolar::getSubscription('sub-id-123');

// Revoke a subscription via the API
$subscription = LaravelPolar::revokeSubscription('sub-id-123');
```

#### Subscription Trials

The package supports subscription trials through the Polar SDK's `Trialing` status. You can check trial state on subscriptions:

```php
// Check if a subscription is on trial
$subscription->onTrial();

// Check if a subscription's trial has expired
$subscription->hasExpiredTrial();

// Filter subscriptions by trial status
Subscription::query()->onTrial()->get();
```

You can also use "generic" trials on the customer model, independent of any subscription:

```php
// Check if the customer is on a generic trial
$customer->onGenericTrial();

// Check if the customer's generic trial has expired
$customer->hasExpiredGenericTrial();
```

Generic trials use the `trial_ends_at` column on the `polar_customers` table.

### Benefits

Benefits are automated features that are granted to customers when they purchase your products. You can manage benefits using both the `LaravelPolar` facade (for create/update/delete operations) and methods on your billable model (for listing and retrieving benefits).

#### Creating Benefits

Create benefits programmatically using the `LaravelPolar` facade:

```php
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components;

$benefit = LaravelPolar::createBenefit(
    new Components\BenefitCustomCreate(
        description: 'Premium Support',
        organizationId: 'your-org-id',
        properties: new Components\BenefitCustomCreateProperties(),
    )
);
```

#### Listing Benefits

List all benefits for an organization using your billable model:

```php
$benefits = $user->listBenefits('your-org-id');
```

#### Getting a Specific Benefit

Retrieve a specific benefit by ID using your billable model:

```php
$benefit = $user->getBenefit('benefit-id-123');
```

#### Listing Benefit Grants

Get all grants for a specific benefit using your billable model:

```php
$grants = $user->listBenefitGrants('benefit-id-123');
```

#### Updating Benefits

Update an existing benefit using the `LaravelPolar` facade:

```php
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components;

$benefit = LaravelPolar::updateBenefit(
    'benefit-id-123',
    new Components\BenefitCustomUpdate(
        description: 'Updated Premium Support',
        properties: new Components\BenefitCustomUpdateProperties(),
    )
);
```

#### Deleting Benefits

Delete a benefit using the `LaravelPolar` facade:

```php
LaravelPolar::deleteBenefit('benefit-id-123');
```

### Customers API

Manage Polar customers directly via the API:

```php
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components\CustomerCreate;
use Polar\Models\Components\CustomerUpdate;
use Polar\Models\Operations\CustomersListRequest;

// Create a customer
$customer = LaravelPolar::createCustomer(new CustomerCreate(
    email: 'user@example.com',
));

// Get a customer
$customer = LaravelPolar::getCustomer('customer-id-123');

// Get a customer by external ID
$customer = LaravelPolar::getCustomerByExternalId('your-external-id');

// Update a customer
$customer = LaravelPolar::updateCustomer('customer-id-123', new CustomerUpdate(
    name: 'Updated Name',
));

// List all customers
$customers = LaravelPolar::listCustomers();

// Delete a customer
LaravelPolar::deleteCustomer('customer-id-123');

// Get customer state (active subscriptions, orders, etc.)
$state = LaravelPolar::getCustomerState('customer-id-123');
```

### Products

Full CRUD operations for products via the facade:

```php
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components;

// Create a product
$product = LaravelPolar::createProduct(new Components\ProductCreateRecurring(
    name: 'Pro Plan',
    prices: [/* ... */],
));

// Get a product
$product = LaravelPolar::getProduct('product-id-123');

// Update a product
$product = LaravelPolar::updateProduct('product-id-123', new Components\ProductUpdate(
    name: 'Updated Pro Plan',
));

// Update product benefits
$product = LaravelPolar::updateProductBenefits('product-id-123', new Components\ProductBenefitsUpdate(
    benefits: ['benefit-id-1', 'benefit-id-2'],
));

// List products (already existed)
$products = LaravelPolar::listProducts();
```

### Discounts

Create and manage discount codes for your products:

```php
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components;

// Create a percentage discount
$discount = LaravelPolar::createDiscount(
    new Components\DiscountPercentageOnceForeverDurationCreate(
        name: '20% Off',
        basisPoints: 2000, // 20%
        organizationId: 'your-org-id',
    )
);

// List discounts
$discounts = LaravelPolar::listDiscounts();

// Get a discount
$discount = LaravelPolar::getDiscount('discount-id-123');

// Update a discount
$discount = LaravelPolar::updateDiscount('discount-id-123', new Components\DiscountUpdate(
    name: 'Updated Discount',
));

// Delete a discount
LaravelPolar::deleteDiscount('discount-id-123');
```

Apply a discount to a checkout using the existing `withDiscountId` method:

```php
$user->checkout(['product_id_123'])
    ->withDiscountId('discount-id-123');
```

### License Keys

Manage software license keys:

```php
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components;

// List all license keys
$keys = LaravelPolar::listLicenseKeys();

// Get a specific license key
$key = LaravelPolar::getLicenseKey('key-id-123');

// Validate a license key
$validated = LaravelPolar::validateLicenseKey(new Components\LicenseKeyValidate(
    key: 'LICENSE-KEY-VALUE',
    organizationId: 'your-org-id',
));

// Activate a license key
$activation = LaravelPolar::activateLicenseKey(new Components\LicenseKeyActivate(
    key: 'LICENSE-KEY-VALUE',
    organizationId: 'your-org-id',
    label: 'My Device',
));

// Deactivate a license key
LaravelPolar::deactivateLicenseKey(new Components\LicenseKeyDeactivate(
    key: 'LICENSE-KEY-VALUE',
    organizationId: 'your-org-id',
    activationId: 'activation-id',
));
```

#### License Keys on Billable

The `Billable` trait includes license key management methods so you can validate, activate, and deactivate keys directly from your user model.

The `organization_id` is resolved from `config('polar.organization_id')` by default, so you only need to set it once in your `.env`:

```bash
POLAR_ORGANIZATION_ID="your-org-id"
```

You can still pass an explicit organization ID to any method if needed:

```php
// List license keys (optionally filter by benefit ID)
$keys = $user->licenseKeys();
$keys = $user->licenseKeys(benefitId: 'benefit-id-123');

// Validate a license key
$validated = $user->validateLicenseKey('LICENSE-KEY-VALUE');

// Activate a license key on a device
$activation = $user->activateLicenseKey('LICENSE-KEY-VALUE', 'My Laptop');

// Deactivate a license key
$user->deactivateLicenseKey('LICENSE-KEY-VALUE', 'activation-id');

// Override the config org ID for a specific call
$validated = $user->validateLicenseKey('LICENSE-KEY-VALUE', organizationId: 'other-org-id');
```

### Usage-Based Billing

Track customer usage events for metered billing. This allows you to charge customers based on their actual usage of your service.

#### Tracking Usage Events

Track a single usage event for a customer:

```php
$user->ingestUsageEvent('api_request', [
    'endpoint' => '/api/v1/data',
    'method' => 'GET',
    'duration_ms' => 145,
]);
```

#### Batch Event Ingestion

For usage-based billing, you can track multiple events at once:

```php
$user->ingestUsageEvents([
    [
        'eventName' => 'api_request',
        'properties' => [
            'endpoint' => '/api/v1/data',
            'method' => 'GET',
        ],
    ],
    [
        'eventName' => 'storage_used',
        'properties' => [
            'bytes' => 1048576,
        ],
        'timestamp' => time(),
    ],
]);
```

#### Listing Customer Meters

List all meters for a customer:

```php
$meters = $user->listCustomerMeters();
```

#### Getting a Specific Customer Meter

Retrieve a specific customer meter by ID using the `LaravelPolar` facade:

```php
use Climactic\LaravelPolar\LaravelPolar;

$meter = LaravelPolar::getCustomerMeter('meter-id-123');
```

> [!NOTE]
> Usage events are sent to Polar for processing. They are not stored locally in your database. Use Polar's dashboard or API to view processed usage data.

### Handling Webhooks

Polar can send webhooks to your app, allowing you to react. By default, this package handles the majority of the work for you. If you have properly configured webhooks, it will listen for incoming events and update your database accordingly. We recommend activating all event kinds so you may easily upgrade in the future.

#### Webhook Events

The package dispatches the following webhook events:

**Order Events:**
- `Climactic\LaravelPolar\Events\OrderCreated`
- `Climactic\LaravelPolar\Events\OrderUpdated`
- `Climactic\LaravelPolar\Events\OrderPaid`
- `Climactic\LaravelPolar\Events\OrderRefunded`

**Subscription Events:**
- `Climactic\LaravelPolar\Events\SubscriptionCreated`
- `Climactic\LaravelPolar\Events\SubscriptionUpdated`
- `Climactic\LaravelPolar\Events\SubscriptionActive`
- `Climactic\LaravelPolar\Events\SubscriptionCanceled`
- `Climactic\LaravelPolar\Events\SubscriptionUncanceled`
- `Climactic\LaravelPolar\Events\SubscriptionRevoked`
- `Climactic\LaravelPolar\Events\SubscriptionPastDue`

**Benefit Grant Events:**
- `Climactic\LaravelPolar\Events\BenefitGrantCreated`
- `Climactic\LaravelPolar\Events\BenefitGrantUpdated`
- `Climactic\LaravelPolar\Events\BenefitGrantRevoked`
- `Climactic\LaravelPolar\Events\BenefitGrantCycled`

**Refund Events:**
- `Climactic\LaravelPolar\Events\RefundCreated`
- `Climactic\LaravelPolar\Events\RefundUpdated`

**Checkout Events:**
- `Climactic\LaravelPolar\Events\CheckoutCreated`
- `Climactic\LaravelPolar\Events\CheckoutUpdated`
- `Climactic\LaravelPolar\Events\CheckoutExpired`

**Customer Events:**
- `Climactic\LaravelPolar\Events\CustomerCreated`
- `Climactic\LaravelPolar\Events\CustomerUpdated`
- `Climactic\LaravelPolar\Events\CustomerDeleted`
- `Climactic\LaravelPolar\Events\CustomerStateChanged`

**Customer Seat Events:**
- `Climactic\LaravelPolar\Events\CustomerSeatAssigned`
- `Climactic\LaravelPolar\Events\CustomerSeatClaimed`
- `Climactic\LaravelPolar\Events\CustomerSeatRevoked`

**Organization Events:**
- `Climactic\LaravelPolar\Events\OrganizationUpdated`

**Product Events:**
- `Climactic\LaravelPolar\Events\ProductCreated`
- `Climactic\LaravelPolar\Events\ProductUpdated`

**Benefit Events:**
- `Climactic\LaravelPolar\Events\BenefitCreated`
- `Climactic\LaravelPolar\Events\BenefitUpdated`

Each of these events has a `$payload` property containing the webhook payload. Some events also expose convenience properties for direct access to related models:

**Events with Convenience Properties:**

| Event | Convenience Properties |
|-------|----------------------|
| `OrderCreated`, `OrderUpdated` | `$billable`, `$order` |
| `SubscriptionCreated`, `SubscriptionUpdated`, `SubscriptionActive`, `SubscriptionCanceled`, `SubscriptionRevoked` | `$billable`, `$subscription` |
| `BenefitGrantCreated`, `BenefitGrantUpdated`, `BenefitGrantRevoked` | `$billable` |

**Events with Only `$payload`:**

| Event | Access Pattern |
|-------|----------------|
| `CheckoutCreated`, `CheckoutUpdated`, `CheckoutExpired` | `$event->payload->checkout` |
| `CustomerCreated`, `CustomerUpdated`, `CustomerDeleted`, `CustomerStateChanged` | `$event->payload->customer` |
| `ProductCreated`, `ProductUpdated` | `$event->payload->product` |
| `BenefitCreated`, `BenefitUpdated` | `$event->payload->benefit` |

**Example Usage:**

```php
// Events with convenience properties
public function handle(OrderCreated $event): void
{
    $order = $event->order; // Direct access
    $billable = $event->billable; // Direct access
}

// Events with only payload
public function handle(CheckoutCreated $event): void
{
    $checkout = $event->payload->checkout; // Access via payload
}
```

If you wish to respond to these events, you must establish listeners for them. You can create separate listener classes for each event type, or use a single listener class with multiple methods.

#### Using Separate Listener Classes

Create individual listener classes for each event:

```php
<?php

namespace App\Listeners;

use Climactic\LaravelPolar\Events\CheckoutCreated;

class HandleCheckoutCreated
{
    public function handle(CheckoutCreated $event): void
    {
        $checkout = $event->payload->checkout;
        // Handle checkout creation...
    }
}
```

```php
<?php

namespace App\Listeners;

use Climactic\LaravelPolar\Events\SubscriptionUpdated;

class HandleSubscriptionUpdated
{
    public function handle(SubscriptionUpdated $event): void
    {
        $subscription = $event->subscription;
        // Handle subscription update...
    }
}
```

#### Using a Single Listener Class

Alternatively, you can use a single listener class with multiple methods. For this approach, you'll need to register the listener as an event subscriber:

```php
<?php

namespace App\Listeners;

use Climactic\LaravelPolar\Events\CheckoutCreated;
use Climactic\LaravelPolar\Events\SubscriptionUpdated;
use Climactic\LaravelPolar\Events\WebhookHandled;
use Illuminate\Events\Dispatcher;

class PolarEventListener
{
    /**
     * Handle received Polar webhooks.
     */
    public function handleWebhookHandled(WebhookHandled $event): void
    {
        if ($event->payload['type'] === 'subscription.updated') {
            // Handle the incoming event...
        }
    }

    /**
     * Handle checkout created events.
     */
    public function handleCheckoutCreated(CheckoutCreated $event): void
    {
        $checkout = $event->payload->checkout;
        // Handle checkout creation...
    }

    /**
     * Handle subscription updated events.
     */
    public function handleSubscriptionUpdated(SubscriptionUpdated $event): void
    {
        $subscription = $event->subscription;
        // Handle subscription update...
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            WebhookHandled::class,
            [self::class, 'handleWebhookHandled']
        );

        $events->listen(
            CheckoutCreated::class,
            [self::class, 'handleCheckoutCreated']
        );

        $events->listen(
            SubscriptionUpdated::class,
            [self::class, 'handleSubscriptionUpdated']
        );
    }
}
```

The [Polar documentation](https://docs.polar.sh/integrate/webhooks/events) includes an example payload.

#### Registering Listeners

**For separate listener classes**, register them in your `EventServiceProvider`:

```php
<?php

namespace App\Providers;

use App\Listeners\HandleCheckoutCreated;
use App\Listeners\HandleSubscriptionUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Climactic\LaravelPolar\Events\CheckoutCreated;
use Climactic\LaravelPolar\Events\SubscriptionUpdated;
use Climactic\LaravelPolar\Events\WebhookHandled;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        WebhookHandled::class => [
            // Add your listeners here
        ],
        CheckoutCreated::class => [
            HandleCheckoutCreated::class,
        ],
        SubscriptionUpdated::class => [
            HandleSubscriptionUpdated::class,
        ],
    ];
}
```

**For event subscribers**, register the subscriber in your `EventServiceProvider`:

```php
<?php

namespace App\Providers;

use App\Listeners\PolarEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $subscribe = [
        PolarEventListener::class,
    ];
}
```

Laravel v11 and v12 will automatically discover listeners and subscribers if they follow Laravel's naming conventions.

#### Custom Webhook Handlers

For full control over how a specific webhook event is processed (including database updates), you can register custom handlers in `config/polar.php`. Custom handlers run *instead of* the built-in processing for that event type.

```php
// config/polar.php
'webhook_handlers' => [
    'subscription.created' => App\Webhooks\CustomSubscriptionHandler::class,
],
```

Your handler must implement `Climactic\LaravelPolar\Contracts\WebhookHandler`:

```php
<?php

namespace App\Webhooks;

use Climactic\LaravelPolar\Contracts\WebhookHandler;

class CustomSubscriptionHandler implements WebhookHandler
{
    public function handle(array $data, \DateTime $timestamp, string $type): ?string
    {
        // Your custom logic here...
        // $data contains the webhook payload

        // Return null to mark as handled, or a string reason to mark as skipped
        return null;
    }
}
```

Unregistered event types continue to use the built-in handler.

## Testing

### Test Fake

Use `LaravelPolar::fake()` to prevent real API calls in your test suite. The fake records all calls and lets you assert against them:

```php
use Climactic\LaravelPolar\LaravelPolar;

$fake = LaravelPolar::fake();

// Run code that calls LaravelPolar methods...
LaravelPolar::listProducts();

// Assert methods were called
$fake->assertCalled('listProducts');
$fake->assertNotCalled('createProduct');
$fake->assertCalledTimes('listProducts', 1);
$fake->assertNothingCalled(); // fails if any method was called
```

You can stub return values for methods that your code depends on:

```php
$fake = LaravelPolar::fake();
$fake->stub('listProducts', $myProductList);

$result = LaravelPolar::listProducts(); // returns $myProductList
```

Assert that a method was called with specific arguments using a callback:

```php
$fake->assertCalledWith('getProduct', function (string $id) {
    return $id === 'product-id-123';
});
```

Remember to tear down the fake after each test (or in `tearDown()`):

```php
$fake->tearDown();
```

### Running Tests

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [danestves](https://github.com/danestves) — original author of [danestves/laravel-polar](https://github.com/danestves/laravel-polar)
- [laravel/cashier (Stripe)](https://github.com/laravel/cashier-stripe)
- [laravel/cashier (Paddle)](https://github.com/laravel/cashier-paddle)
- [lemonsqueezy/laravel](https://github.com/lmsqueezy/laravel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
