<?php

namespace Tests\Feature;

use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components;

afterEach(function () {
    LaravelPolar::resetSdk();
});

it('can install a fake and record calls', function () {
    $fake = LaravelPolar::fake();

    LaravelPolar::deleteCustomer('cust_123');

    $fake->assertCalled('deleteCustomer');
    $fake->assertNotCalled('createCustomer');
});

it('can assert a method was called with specific arguments', function () {
    $fake = LaravelPolar::fake();

    LaravelPolar::deleteBenefit('benefit_abc');

    $fake->assertCalledWith('deleteBenefit', fn ($id) => $id === 'benefit_abc');
});

it('can assert call count', function () {
    $fake = LaravelPolar::fake();

    LaravelPolar::deleteDiscount('disc_1');
    LaravelPolar::deleteDiscount('disc_2');

    $fake->assertCalledTimes('deleteDiscount', 2);
});

it('can assert nothing called', function () {
    $fake = LaravelPolar::fake();

    $fake->assertNothingCalled();
});

it('returns stub values', function () {
    $fake = LaravelPolar::fake();

    $stubProduct = \Mockery::mock(Components\Product::class);
    $fake->stub('getProduct', $stubProduct);

    $result = LaravelPolar::getProduct('prod_123');

    expect($result)->toBe($stubProduct);
    $fake->assertCalled('getProduct');
});

it('returns null for void methods when faked', function () {
    $fake = LaravelPolar::fake();

    // void methods should not throw when faked
    LaravelPolar::deleteBenefit('benefit_123');
    LaravelPolar::deleteCustomer('cust_123');
    LaravelPolar::deleteDiscount('disc_123');
    LaravelPolar::ingestEvents(\Mockery::mock(Components\EventsIngest::class));
    LaravelPolar::generateOrderInvoice('order_123');
    LaravelPolar::deactivateLicenseKey(\Mockery::mock(Components\LicenseKeyDeactivate::class));

    $fake->assertCalledTimes('deleteBenefit', 1);
    $fake->assertCalledTimes('deleteCustomer', 1);
    $fake->assertCalledTimes('deleteDiscount', 1);
    $fake->assertCalledTimes('ingestEvents', 1);
    $fake->assertCalledTimes('generateOrderInvoice', 1);
    $fake->assertCalledTimes('deactivateLicenseKey', 1);
});

it('resets fake on resetSdk', function () {
    LaravelPolar::fake();

    expect(LaravelPolar::getFake())->not->toBeNull();

    LaravelPolar::resetSdk();

    expect(LaravelPolar::getFake())->toBeNull();
});
