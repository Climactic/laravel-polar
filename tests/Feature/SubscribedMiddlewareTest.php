<?php

namespace Tests\Feature;

use Climactic\LaravelPolar\Customer;
use Climactic\LaravelPolar\Http\Middleware\Subscribed;
use Climactic\LaravelPolar\Subscription;
use Climactic\LaravelPolar\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Relation::morphMap([
        'users' => User::class,
    ]);
});

it('allows subscribed users through', function () {
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

    Route::get('/test', fn () => 'OK')->middleware(['web', 'polar.subscribed']);

    $this->actingAs($user)->get('/test')->assertOk()->assertSee('OK');
});

it('returns 403 for unsubscribed users on web requests', function () {
    $user = User::factory()->create();

    Route::get('/test', fn () => 'OK')->middleware(['web', 'polar.subscribed']);

    $this->actingAs($user)->get('/test')->assertStatus(403);
});

it('returns 403 JSON for unsubscribed users on API requests', function () {
    $user = User::factory()->create();

    Route::get('/test', fn () => 'OK')->middleware('polar.subscribed');

    $this->actingAs($user)
        ->getJson('/test')
        ->assertStatus(403)
        ->assertJson(['message' => 'Subscription required.']);
});

it('redirects to configured URL when middleware_redirect_url is set', function () {
    $user = User::factory()->create();

    config(['polar.middleware_redirect_url' => '/billing']);

    Route::get('/test', fn () => 'OK')->middleware(['web', 'polar.subscribed']);

    $this->actingAs($user)->get('/test')->assertRedirect('/billing');
});

it('supports type parameter in middleware', function () {
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

    Route::get('/pro', fn () => 'OK')->middleware(['web', 'polar.subscribed:pro']);
    Route::get('/basic', fn () => 'OK')->middleware(['web', 'polar.subscribed:basic']);

    $this->actingAs($user)->get('/pro')->assertOk();
    $this->actingAs($user)->get('/basic')->assertStatus(403);
});

it('supports product_id parameter in middleware', function () {
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

    Route::get('/with-product', fn () => 'OK')->middleware(['web', 'polar.subscribed:default,product_123']);
    Route::get('/wrong-product', fn () => 'OK')->middleware(['web', 'polar.subscribed:default,product_999']);

    $this->actingAs($user)->get('/with-product')->assertOk();
    $this->actingAs($user)->get('/wrong-product')->assertStatus(403);
});

it('returns 403 for guests', function () {
    Route::get('/test', fn () => 'OK')->middleware('polar.subscribed');

    $this->getJson('/test')->assertStatus(403);
});
