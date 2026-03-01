<?php

namespace Climactic\LaravelPolar\Concerns;

use Climactic\LaravelPolar\LaravelPolar;
use Polar\Models\Components;
use Polar\Models\Operations;

/**
 * @deprecated Use LaravelPolar::listBenefits(), LaravelPolar::getBenefit(), and LaravelPolar::listBenefitGrants() directly instead.
 *             These methods do not use billable context and will be removed in a future version.
 */
trait ManagesBenefits
{
    /**
     * List all benefits for an organization.
     *
     * @deprecated Use LaravelPolar::listBenefits() directly instead.
     *
     * @throws \Polar\Models\Errors\APIException
     * @throws \Exception
     */
    public function listBenefits(string $organizationId): Operations\BenefitsListResponse
    {
        $request = new Operations\BenefitsListRequest(
            organizationId: $organizationId,
        );

        return LaravelPolar::listBenefits($request);
    }

    /**
     * Get a specific benefit by ID.
     *
     * @deprecated Use LaravelPolar::getBenefit() directly instead.
     *
     * @throws \Polar\Models\Errors\APIException
     * @throws \Exception
     */
    public function getBenefit(string $benefitId): Components\BenefitCustom|Components\BenefitDiscord|Components\BenefitGitHubRepository|Components\BenefitDownloadables|Components\BenefitLicenseKeys|Components\BenefitMeterCredit
    {
        return LaravelPolar::getBenefit($benefitId);
    }

    /**
     * List all grants for a specific benefit.
     *
     * @deprecated Use LaravelPolar::listBenefitGrants() directly instead.
     *
     * @throws \Polar\Models\Errors\APIException
     * @throws \Exception
     */
    public function listBenefitGrants(string $benefitId): Operations\BenefitsGrantsResponse
    {
        $request = new Operations\BenefitsGrantsRequest(
            id: $benefitId,
        );

        return LaravelPolar::listBenefitGrants($request);
    }
}
