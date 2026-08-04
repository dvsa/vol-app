<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;

/**
 * Builds the applicant context sent with an analysis request.
 *
 * vehicles_requested covers this application plus other active licences plus other
 * applications under consideration - the same set getRequiredFinance() sums over, so the two
 * figures cannot contradict each other.
 */
class ApplicantProfileBuilder
{
    public function __construct(private readonly FinancialStandingHelperService $financialStandingHelper)
    {
    }

    /**
     * @return array{
     *     organisation_name: string,
     *     trading_name: string|null,
     *     business_type: string,
     *     licence_type: string,
     *     vehicles_requested: int,
     *     required_finance: float
     * }
     */
    public function build(Application $application): array
    {
        $licence = $application->getLicence();
        $organisation = $licence?->getOrganisation();

        return [
            'organisation_name' => (string)$organisation?->getName(),
            'trading_name' => $this->resolveTradingName($licence),
            'business_type' => $this->describeRefData($organisation?->getType()),
            'licence_type' => $this->describeRefData($application->getLicenceType()),
            'vehicles_requested' => $this->resolveVehiclesRequested($application),
            'required_finance' => (float)$this->financialStandingHelper->getRequiredFinance($application),
        ];
    }

    /** Total authorised vehicles across everything the financial-standing calculation covers. */
    private function resolveVehiclesRequested(Application $application): int
    {
        $total = (int)$application->getTotAuthVehicles();

        foreach ($application->getOtherActiveLicencesForOrganisation() as $otherLicence) {
            $total += (int)$otherLicence->getTotAuthVehicles();
        }

        foreach ($this->financialStandingHelper->getOtherNewApplications($application) as $otherApplication) {
            $total += (int)$otherApplication->getTotAuthVehicles();
        }

        return $total;
    }

    private function resolveTradingName(?object $licence): ?string
    {
        if ($licence === null) {
            return null;
        }

        $names = [];

        foreach ($licence->getTradingNames() as $tradingName) {
            $name = trim((string)$tradingName->getName());

            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $names === [] ? null : implode(', ', array_unique($names));
    }

    /** Description over id: the profile feeds a prompt, and "Limited company" carries meaning. */
    private function describeRefData(?RefData $refData): string
    {
        if ($refData === null) {
            return '';
        }

        $description = (string)$refData->getDescription();

        return $description !== '' ? $description : (string)$refData->getId();
    }
}
