<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\Document;
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
     *     organisation_name: string|null,
     *     licence_number: string|null,
     *     nature_of_business: string|null,
     *     business_type: string,
     *     people: list<string>,
     *     application_number: mixed,
     *     trading_name: string,
     *     required_funds: int,
     *     licence_type: string,
     *     application_date: mixed,
     *     vehicles_requested: int
     * }
     */
    public function build(Application $application): array
    {
        $licence = $application->getLicence();
        $organisation = $licence?->getOrganisation();

        return [
            'organisation_name' => $organisation?->getName(),
            'licence_number' => $licence?->getLicNo(),
            'nature_of_business' => $organisation?->getNatureOfBusiness(),
            'business_type' => $this->describeRefData($organisation?->getType()),
            'people' => $this->buildPeople($organisation),
            'application_number' => $application->getId(),
            'trading_name' => $this->resolveTradingName($licence) ?? 'None',
            'required_funds' => $this->financialStandingHelper->getRequiredFinance($application),
            'licence_type' => $this->describeRefData($application->getLicenceType()),
            'application_date' => $application->getApplicationDate(),
            'vehicles_requested' => $this->resolveVehiclesRequested($application),
        ];
    }

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

    /**
     * @return list<string>
     */
    private function buildPeople(?object $organisation): array
    {
        if ($organisation === null) {
            return [];
        }

        $people = [];

        foreach ($organisation->getOrganisationPersons() as $orgPerson) {
            $person = $orgPerson->getPerson();

            if ($person === null) {
                continue;
            }

            $parts = array_filter([
                $this->describeRefData($person->getTitle()),
                (string)$person->getForename(),
                (string)$person->getFamilyName(),
            ]);

            $fullName = trim(implode(' ', $parts));

            if ($fullName !== '') {
                $people[] = $fullName;
            }
        }

        return $people;
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
