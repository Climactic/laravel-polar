<?php

namespace Climactic\LaravelPolar\Concerns;

use Climactic\LaravelPolar\Exceptions\InvalidCustomer;
use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components;
use Polar\Models\Operations;

trait ManagesLicenseKeys
{
    /**
     * List license keys, optionally filtered by benefit ID.
     *
     * @throws InvalidCustomer
     */
    public function licenseKeys(?string $benefitId = null): Operations\LicenseKeysListResponse
    {
        $this->assertCustomerExists();

        $request = new Operations\LicenseKeysListRequest(
            benefitId: $benefitId,
        );

        return LaravelPolar::listLicenseKeys($request);
    }

    /**
     * Validate a license key.
     *
     * @throws InvalidCustomer
     */
    public function validateLicenseKey(string $key, ?string $organizationId = null): Components\ValidatedLicenseKey
    {
        $this->assertCustomerExists();

        return LaravelPolar::validateLicenseKey(
            new Components\LicenseKeyValidate(key: $key, organizationId: $this->resolveOrganizationId($organizationId)),
        );
    }

    /**
     * Activate a license key.
     *
     * @throws InvalidCustomer
     */
    public function activateLicenseKey(string $key, string $label, ?string $organizationId = null): Components\LicenseKeyActivationRead
    {
        $this->assertCustomerExists();

        return LaravelPolar::activateLicenseKey(
            new Components\LicenseKeyActivate(key: $key, organizationId: $this->resolveOrganizationId($organizationId), label: $label),
        );
    }

    /**
     * Deactivate a license key.
     *
     * @throws InvalidCustomer
     */
    public function deactivateLicenseKey(string $key, string $activationId, ?string $organizationId = null): void
    {
        $this->assertCustomerExists();

        LaravelPolar::deactivateLicenseKey(
            new Components\LicenseKeyDeactivate(key: $key, organizationId: $this->resolveOrganizationId($organizationId), activationId: $activationId),
        );
    }

    private function resolveOrganizationId(?string $organizationId): string
    {
        $resolved = $organizationId ?? config('polar.organization_id');

        if (! $resolved) {
            throw new \InvalidArgumentException('Organization ID must be provided or set via the polar.organization_id config.');
        }

        return $resolved;
    }

    /**
     * @throws InvalidCustomer
     */
    private function assertCustomerExists(): void
    {
        if ($this->customer === null || $this->customer->polar_id === null) {
            throw InvalidCustomer::notYetCreated($this);
        }
    }
}
