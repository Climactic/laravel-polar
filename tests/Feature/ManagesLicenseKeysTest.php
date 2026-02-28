<?php

namespace Tests\Feature;

use Climactic\LaravelPolar\Customer;
use Climactic\LaravelPolar\Exceptions\InvalidCustomer;
use Climactic\LaravelPolar\LaravelPolar;
use Climactic\LaravelPolar\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Polar\Models\Components;
use Polar\Models\Operations;

beforeEach(function () {
    Relation::morphMap([
        'users' => User::class,
    ]);
});

afterEach(function () {
    LaravelPolar::resetSdk();
});

it('throws InvalidCustomer when no customer exists for licenseKeys', function () {
    $user = User::factory()->create();

    expect(fn () => $user->licenseKeys())
        ->toThrow(InvalidCustomer::class);
});

it('throws InvalidCustomer when customer has no polar_id for licenseKeys', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'polar_id' => null,
    ]);

    expect(fn () => $user->licenseKeys())
        ->toThrow(InvalidCustomer::class);
});

it('calls listLicenseKeys via the billable', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'polar_id' => 'cust_123',
    ]);

    $fake = LaravelPolar::fake();
    $stubResponse = \Mockery::mock(Operations\LicenseKeysListResponse::class);
    $fake->stub('listLicenseKeys', $stubResponse);

    $result = $user->licenseKeys();

    expect($result)->toBe($stubResponse);
    $fake->assertCalled('listLicenseKeys');
});

it('throws InvalidCustomer when no customer exists for validateLicenseKey', function () {
    $user = User::factory()->create();

    expect(fn () => $user->validateLicenseKey('LK-abc', 'org_123'))
        ->toThrow(InvalidCustomer::class);
});

it('calls validateLicenseKey via the billable with explicit org id', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'polar_id' => 'cust_123',
    ]);

    $fake = LaravelPolar::fake();
    $stubResponse = \Mockery::mock(Components\ValidatedLicenseKey::class);
    $fake->stub('validateLicenseKey', $stubResponse);

    $result = $user->validateLicenseKey('LK-abc', 'org_123');

    expect($result)->toBe($stubResponse);
    $fake->assertCalled('validateLicenseKey');
});

it('falls back to config organization_id when not provided', function () {
    config(['polar.organization_id' => 'org_from_config']);

    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'polar_id' => 'cust_123',
    ]);

    $fake = LaravelPolar::fake();
    $stubResponse = \Mockery::mock(Components\ValidatedLicenseKey::class);
    $fake->stub('validateLicenseKey', $stubResponse);

    $result = $user->validateLicenseKey('LK-abc');

    expect($result)->toBe($stubResponse);
    $fake->assertCalled('validateLicenseKey');
});

it('throws when no organization_id provided and config is empty', function () {
    config(['polar.organization_id' => null]);

    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'polar_id' => 'cust_123',
    ]);

    $fake = LaravelPolar::fake();

    expect(fn () => $user->validateLicenseKey('LK-abc'))
        ->toThrow(\InvalidArgumentException::class, 'Organization ID must be provided or set via the polar.organization_id config.');
});

it('calls activateLicenseKey via the billable', function () {
    config(['polar.organization_id' => 'org_from_config']);

    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'polar_id' => 'cust_123',
    ]);

    $fake = LaravelPolar::fake();
    $stubResponse = \Mockery::mock(Components\LicenseKeyActivationRead::class);
    $fake->stub('activateLicenseKey', $stubResponse);

    $result = $user->activateLicenseKey('LK-abc', 'my-machine');

    expect($result)->toBe($stubResponse);
    $fake->assertCalled('activateLicenseKey');
});

it('calls deactivateLicenseKey via the billable', function () {
    config(['polar.organization_id' => 'org_from_config']);

    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'polar_id' => 'cust_123',
    ]);

    $fake = LaravelPolar::fake();

    $user->deactivateLicenseKey('LK-abc', 'activation_123');

    $fake->assertCalled('deactivateLicenseKey');
});
