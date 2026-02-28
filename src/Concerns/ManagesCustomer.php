<?php

namespace Climactic\LaravelPolar\Concerns;

use Climactic\LaravelPolar\Customer;
use Climactic\LaravelPolar\Exceptions\InvalidCustomer;
use Climactic\LaravelPolar\Exceptions\PolarApiError;
use Climactic\LaravelPolar\LaravelPolar;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\RedirectResponse;
use Polar\Models\Components;
use Polar\Models\Errors;

trait ManagesCustomer
{
    /**
     * Create a customer record for the billable model.
     *
     * @param  array<string, string|int>  $attributes
     */
    public function createAsCustomer(array $attributes = []): Customer
    {
        return $this->customer()->create($attributes);
    }

    /**
     * Get the customer related to the billable model.
     *
     * @return MorphOne<Customer, covariant $this>
     */
    public function customer(): MorphOne
    {
        return $this->morphOne(LaravelPolar::$customerModel, 'billable');
    }

    /**
     * Get the billable's name to associate with Polar.
     */
    public function polarName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * Get the billable's email address to associate with Polar.
     */
    public function polarEmail(): ?string
    {
        return $this->email ?? null;
    }

    /**
     * Generate a redirect response to the billable's customer portal.
     */
    public function redirectToCustomerPortal(): RedirectResponse
    {
        return new RedirectResponse($this->customerPortalUrl());
    }

    /**
     * Get the customer portal url for this billable.
     *
     * @throws PolarApiError
     * @throws InvalidCustomer
     * @throws Errors\APIException
     * @throws Errors\HTTPValidationError
     */
    public function customerPortalUrl(): string
    {
        if ($this->customer === null || $this->customer->polar_id === null) {
            throw InvalidCustomer::notYetCreated($this);
        }

        $request = new Components\CustomerSessionCustomerIDCreate(
            customerId: $this->customer->polar_id,
        );

        $response = LaravelPolar::createCustomerSession($request);

        return $response->customerPortalUrl;
    }
}
