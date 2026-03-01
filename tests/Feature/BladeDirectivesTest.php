<?php

namespace Tests\Feature;

use Climactic\LaravelPolar\Customer;
use Climactic\LaravelPolar\Subscription;
use Climactic\LaravelPolar\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Relations\Relation;

beforeEach(function () {
    Relation::morphMap([
        'users' => User::class,
    ]);
});

it('renders @subscribed block when user is subscribed', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
    ]);
    Subscription::factory()->active()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'product_id' => 'product_123',
    ]);

    $this->actingAs($user);

    $view = $this->blade('@subscribed Active @else Not Active @endsubscribed');
    $view->assertSee('Active');
    $view->assertDontSee('Not Active');
});

it('renders else block when user is not subscribed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $view = $this->blade('@subscribed YES_ACTIVE @else NO_PLAN @endsubscribed');
    $view->assertSee('NO_PLAN');
    $view->assertDontSee('YES_ACTIVE');
});

it('renders @subscribed block with explicit type', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
    ]);
    Subscription::factory()->active()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'type' => 'pro',
        'product_id' => 'product_123',
    ]);

    $this->actingAs($user);

    $view = $this->blade("@subscribed('pro') Pro @else Not Pro @endsubscribed");
    $view->assertSee('Pro');
    $view->assertDontSee('Not Pro');
});

it('renders else block when subscribed to different type', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
    ]);
    Subscription::factory()->active()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'type' => 'default',
        'product_id' => 'product_123',
    ]);

    $this->actingAs($user);

    $view = $this->blade("@subscribed('pro') Pro @else Not Pro @endsubscribed");
    $view->assertSee('Not Pro');
});

it('renders @subscribed with explicit billable', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
    ]);
    Subscription::factory()->active()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'product_id' => 'product_123',
    ]);

    $view = $this->blade('@subscribed($user) Active @else Not Active @endsubscribed', ['user' => $user]);
    $view->assertSee('Active');
});

it('renders @onTrial block when subscription is trialing', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
    ]);
    Subscription::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'status' => \Polar\Models\Components\SubscriptionStatus::Trialing,
        'product_id' => 'product_123',
    ]);

    $this->actingAs($user);

    $view = $this->blade('@onTrial Trialing @else Not Trialing @endonTrial');
    $view->assertSee('Trialing');
    $view->assertDontSee('Not Trialing');
});

it('renders else block when subscription is not trialing', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
    ]);
    Subscription::factory()->active()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'product_id' => 'product_123',
    ]);

    $this->actingAs($user);

    $view = $this->blade('@onTrial Trialing @else Not Trialing @endonTrial');
    $view->assertSee('Not Trialing');
});

it('renders else block when no user is authenticated for @subscribed', function () {
    $view = $this->blade('@subscribed Active @else Not Active @endsubscribed');
    $view->assertSee('Not Active');
});

it('renders @onTrial block with explicit billable and type', function () {
    $user = User::factory()->create();
    Customer::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
    ]);
    Subscription::factory()->create([
        'billable_id' => $user->getKey(),
        'billable_type' => $user->getMorphClass(),
        'type' => 'pro',
        'status' => \Polar\Models\Components\SubscriptionStatus::Trialing,
        'product_id' => 'product_123',
    ]);

    $view = $this->blade('@onTrial($user, \'pro\') Trialing @else Not Trialing @endonTrial', ['user' => $user]);
    $view->assertSee('Trialing');
    $view->assertDontSee('Not Trialing');
});

it('renders else block when no user is authenticated for @onTrial', function () {
    $view = $this->blade('@onTrial Trialing @else Not Trialing @endonTrial');
    $view->assertSee('Not Trialing');
});
