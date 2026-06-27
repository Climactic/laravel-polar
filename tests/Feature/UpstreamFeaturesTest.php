<?php

namespace Tests\Feature;

use Climactic\LaravelPolar\Checkout;
use Climactic\LaravelPolar\Exceptions\InvalidCustomer;
use Climactic\LaravelPolar\LaravelPolar;
use Climactic\LaravelPolar\Order;
use Climactic\LaravelPolar\Subscription;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Polar\Models\Components;
use Polar\Models\Components\SubscriptionStatus;
use Climactic\LaravelPolar\Tests\Fixtures\User;
use Polar\Models\Operations;

afterEach(function () {
    LaravelPolar::resetSdk();
    Mockery::close();
});

// ──────────────────────────────────────────────────────────────
//  Facade: Seats, Checkout Links, Custom Fields, Metrics, Orgs, Files
// ──────────────────────────────────────────────────────────────

it('exposes seat admin methods on the facade', function () {
    $fake = LaravelPolar::fake();
    $fake->stub('listSeats', Mockery::mock(Components\SeatsList::class));
    $fake->stub('assignSeat', Mockery::mock(Components\CustomerSeat::class));
    $fake->stub('revokeSeat', Mockery::mock(Components\CustomerSeat::class));
    $fake->stub('resendSeatInvitation', Mockery::mock(Components\CustomerSeat::class));

    LaravelPolar::listSeats(subscriptionId: 'sub_1');
    LaravelPolar::assignSeat(new Components\SeatAssign(subscriptionId: 'sub_1', email: 'a@b.com'));
    LaravelPolar::revokeSeat('seat_1');
    LaravelPolar::resendSeatInvitation('seat_1');

    $fake->assertCalledWith('listSeats', fn($subscriptionId, $orderId) => $subscriptionId === 'sub_1');
    $fake->assertCalled('assignSeat');
    $fake->assertCalledWith('revokeSeat', fn($id) => $id === 'seat_1');
    $fake->assertCalledWith('resendSeatInvitation', fn($id) => $id === 'seat_1');
});

it('exposes checkout link CRUD on the facade', function () {
    $fake = LaravelPolar::fake();
    $fake->stub('createCheckoutLink', Mockery::mock(Components\CheckoutLink::class));
    $fake->stub('getCheckoutLink', Mockery::mock(Components\CheckoutLink::class));
    $fake->stub('updateCheckoutLink', Mockery::mock(Components\CheckoutLink::class));
    $fake->stub('listCheckoutLinks', Mockery::mock(Operations\CheckoutLinksListResponse::class));

    LaravelPolar::createCheckoutLink(new Components\CheckoutLinkCreateProducts(products: ['prod_1']));
    LaravelPolar::getCheckoutLink('cl_1');
    LaravelPolar::updateCheckoutLink('cl_1', new Components\CheckoutLinkUpdate());
    LaravelPolar::listCheckoutLinks();
    LaravelPolar::deleteCheckoutLink('cl_1');

    $fake->assertCalled('createCheckoutLink');
    $fake->assertCalledWith('getCheckoutLink', fn($id) => $id === 'cl_1');
    $fake->assertCalled('updateCheckoutLink');
    $fake->assertCalled('listCheckoutLinks');
    $fake->assertCalledWith('deleteCheckoutLink', fn($id) => $id === 'cl_1');
});

it('exposes custom field CRUD on the facade', function () {
    $fake = LaravelPolar::fake();
    $fake->stub('createCustomField', Mockery::mock(Components\CustomFieldText::class));
    $fake->stub('getCustomField', Mockery::mock(Components\CustomFieldText::class));
    $fake->stub('updateCustomField', Mockery::mock(Components\CustomFieldText::class));
    $fake->stub('listCustomFields', Mockery::mock(Operations\CustomFieldsListResponse::class));

    LaravelPolar::createCustomField(new Components\CustomFieldCreateText(slug: 'ref', name: 'Ref', properties: new Components\CustomFieldTextProperties()));
    LaravelPolar::getCustomField('cf_1');
    LaravelPolar::updateCustomField('cf_1', new Components\CustomFieldUpdateText());
    LaravelPolar::listCustomFields();
    LaravelPolar::deleteCustomField('cf_1');

    $fake->assertCalled('createCustomField');
    $fake->assertCalledWith('getCustomField', fn($id) => $id === 'cf_1');
    $fake->assertCalled('updateCustomField');
    $fake->assertCalled('listCustomFields');
    $fake->assertCalledWith('deleteCustomField', fn($id) => $id === 'cf_1');
});

it('exposes metrics, organization and file methods on the facade', function () {
    $fake = LaravelPolar::fake();
    $fake->stub('getMetrics', Mockery::mock(Components\MetricsResponse::class));
    $fake->stub('getOrganization', Mockery::mock(Components\Organization::class));
    $fake->stub('listOrganizations', Mockery::mock(Operations\OrganizationsListResponse::class));
    $fake->stub('listFiles', Mockery::mock(Operations\FilesListResponse::class));

    LaravelPolar::getMetrics(new Operations\MetricsGetRequest(
        startDate: \Brick\DateTime\LocalDate::of(2024, 1, 1),
        endDate: \Brick\DateTime\LocalDate::of(2024, 1, 31),
        interval: Components\TimeInterval::Day,
    ));
    LaravelPolar::getOrganization('org_1');
    LaravelPolar::listOrganizations();
    LaravelPolar::listFiles();

    $fake->assertCalled('getMetrics');
    $fake->assertCalledWith('getOrganization', fn($id) => $id === 'org_1');
    $fake->assertCalled('listOrganizations');
    $fake->assertCalled('listFiles');
});

it('exposes updateLicenseKey on the facade', function () {
    $fake = LaravelPolar::fake();
    $fake->stub('updateLicenseKey', Mockery::mock(Components\LicenseKeyRead::class));

    LaravelPolar::updateLicenseKey('lk_1', new Components\LicenseKeyUpdate());

    $fake->assertCalledWith('updateLicenseKey', fn($id, $req) => $id === 'lk_1');
});

// ──────────────────────────────────────────────────────────────
//  Subscription model helpers
// ──────────────────────────────────────────────────────────────

function subscriptionSyncStub(): Components\Subscription
{
    $stub = Mockery::mock(Components\Subscription::class);
    $stub->status = SubscriptionStatus::Active;
    $stub->productId = 'prod_1';
    $stub->currentPeriodEnd = new \DateTime('2030-01-01');
    $stub->trialEnd = null;
    $stub->endedAt = null;

    return $stub;
}

it('applies and removes a discount on a subscription', function () {
    $subscription = Subscription::factory()->create(['polar_id' => 'sub_1', 'product_id' => 'prod_1']);

    $fake = LaravelPolar::fake();
    $fake->stub('updateSubscription', subscriptionSyncStub());

    $subscription->applyDiscount('disc_1');
    $subscription->removeDiscount();

    $fake->assertCalledWith('updateSubscription', fn($id, $req) => $id === 'sub_1' && $req instanceof Components\SubscriptionUpdateDiscount && $req->discountId === 'disc_1');
    $fake->assertCalledWith('updateSubscription', fn($id, $req) => $req instanceof Components\SubscriptionUpdateDiscount && $req->discountId === null);
    $fake->assertCalledTimes('updateSubscription', 2);
});

it('updates the trial end date on a subscription', function () {
    $subscription = Subscription::factory()->create(['polar_id' => 'sub_1', 'product_id' => 'prod_1']);

    $fake = LaravelPolar::fake();
    $fake->stub('updateSubscription', subscriptionSyncStub());

    $subscription->updateTrial(new \DateTime('2030-01-01'));

    $fake->assertCalledWith('updateSubscription', fn($id, $req) => $req instanceof Components\SubscriptionUpdateTrial);
});

it('returns the trial end date accessor', function () {
    $subscription = Subscription::factory()->create([
        'polar_id' => 'sub_1',
        'trial_ends_at' => now()->addDays(5),
    ]);

    expect($subscription->trialEndsAt())->not->toBeNull();
});

it('manages seats through the subscription', function () {
    $subscription = Subscription::factory()->create(['polar_id' => 'sub_1']);

    $fake = LaravelPolar::fake();
    $fake->stub('listSeats', Mockery::mock(Components\SeatsList::class));
    $fake->stub('assignSeat', Mockery::mock(Components\CustomerSeat::class));
    $fake->stub('revokeSeat', Mockery::mock(Components\CustomerSeat::class));
    $fake->stub('resendSeatInvitation', Mockery::mock(Components\CustomerSeat::class));

    $subscription->seats();
    $subscription->assignSeat(email: 'member@example.com');
    $subscription->revokeSeat('seat_1');
    $subscription->resendSeatInvitation('seat_1');

    $fake->assertCalledWith('listSeats', fn($subscriptionId) => $subscriptionId === 'sub_1');
    $fake->assertCalledWith('assignSeat', fn($req) => $req instanceof Components\SeatAssign && $req->subscriptionId === 'sub_1' && $req->email === 'member@example.com');
    $fake->assertCalledWith('revokeSeat', fn($id) => $id === 'seat_1');
    $fake->assertCalledWith('resendSeatInvitation', fn($id) => $id === 'seat_1');
});

// ──────────────────────────────────────────────────────────────
//  Order model helpers
// ──────────────────────────────────────────────────────────────

it('returns an empty refunds collection for an unsynced order', function () {
    $order = Order::factory()->make(['polar_id' => null]);

    expect($order->refunds())->toBeEmpty();
});

it('lists refunds for a synced order through the facade', function () {
    $order = Order::factory()->make(['polar_id' => 'order_1']);

    $response = Mockery::mock(Operations\RefundsListResponse::class);
    $response->listResourceRefund = Mockery::mock(Components\ListResourceRefund::class);
    $response->listResourceRefund->items = [];

    $fake = LaravelPolar::fake();
    $fake->stub('listRefunds', $response);

    $order->refunds();

    $fake->assertCalledWith('listRefunds', fn($req) => $req instanceof Operations\RefundsListRequest && $req->orderId === 'order_1');
});

it('refunds the remaining unrefunded amount by default', function () {
    $order = Order::factory()->make([
        'polar_id' => 'order_1',
        'amount' => 5000,
        'refunded_amount' => 1500,
    ]);

    $fake = LaravelPolar::fake();
    $fake->stub('createRefund', Mockery::mock(Components\Refund::class));

    $order->refund();

    $fake->assertCalledWith('createRefund', fn($req) => $req instanceof Components\RefundCreate
        && $req->orderId === 'order_1'
        && $req->amount === 3500
        && $req->reason === Components\RefundReason::CustomerRequest);
});

it('refunds an explicit amount with a reason, comment and metadata', function () {
    $order = Order::factory()->make(['polar_id' => 'order_1', 'amount' => 5000, 'refunded_amount' => 0]);

    $fake = LaravelPolar::fake();
    $fake->stub('createRefund', Mockery::mock(Components\Refund::class));

    $order->refund(1000, Components\RefundReason::ServiceDisruption, comment: 'Goodwill', metadata: ['ticket' => '123']);

    $fake->assertCalledWith('createRefund', fn($req) => $req->amount === 1000
        && $req->reason === Components\RefundReason::ServiceDisruption
        && $req->comment === 'Goodwill'
        && $req->metadata === ['ticket' => '123']);
});

it('throws when refunding an order without a polar_id', function () {
    $order = Order::factory()->make(['polar_id' => null]);

    expect(fn() => $order->refund())->toThrow(\RuntimeException::class);
});

it('throws when downloading an invoice without a receipt url', function () {
    $order = Order::factory()->make(['polar_id' => null]);

    expect(fn() => $order->downloadInvoice())->toThrow(\RuntimeException::class);
});

it('returns empty custom field data for an unsynced order', function () {
    $order = Order::factory()->make(['polar_id' => null]);

    expect($order->customFieldData())->toBe([]);
});

// ──────────────────────────────────────────────────────────────
//  Payment methods
// ──────────────────────────────────────────────────────────────

it('throws when listing payment methods without a customer', function () {
    $user = User::factory()->create();

    expect(fn() => $user->paymentMethods())->toThrow(InvalidCustomer::class);
});

it('throws when deleting a payment method without a customer', function () {
    $user = User::factory()->create();

    expect(fn() => $user->deletePaymentMethod('pm_1'))->toThrow(InvalidCustomer::class);
});

// ──────────────────────────────────────────────────────────────
//  ingestEvents accepts any 2xx status (not just 202)
// ──────────────────────────────────────────────────────────────

it('treats any 2xx response from ingestEvents as success', function () {
    $mocked = createMockedSdkWithEvents();
    $response = new Operations\EventsIngestResponse(
        contentType: 'application/json',
        statusCode: 200,
        rawResponse: Mockery::mock(\Psr\Http\Message\ResponseInterface::class),
    );
    $mocked['events']->shouldReceive('ingest')->once()->andReturn($response);
    setLaravelPolarSdk($mocked['sdk']);

    LaravelPolar::ingestEvents(Mockery::mock(Components\EventsIngest::class));
});

// ──────────────────────────────────────────────────────────────
//  Checkout Inertia-aware response
// ──────────────────────────────────────────────────────────────

it('returns a 303 redirect for non-inertia checkout responses', function () {
    $checkout = Mockery::mock(Checkout::class)->makePartial();
    $checkout->shouldReceive('url')->andReturn('https://polar.sh/checkout/abc');

    $request = Mockery::mock(\Illuminate\Http\Request::class);
    $request->shouldReceive('header')->with('X-Inertia')->andReturn(null);

    $response = $checkout->toResponse($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getStatusCode())->toBe(303);
    expect($response->getTargetUrl())->toBe('https://polar.sh/checkout/abc');
});
