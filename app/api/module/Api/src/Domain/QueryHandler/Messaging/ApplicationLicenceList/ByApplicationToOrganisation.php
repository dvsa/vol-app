<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\ApplicationLicenceList;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByApplicationToOrganisation as GetApplicationListByApplicationToOrganisationQuery;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByOrganisation as GetApplicationListByOrganisationQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class ByApplicationToOrganisation extends AbstractQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];

    protected $extraRepos = [Repository\Application::class];

    /**
     * @param GetApplicationListByApplicationToOrganisationQuery $query
     * @throws NotFoundException
     */
    public function handleQuery(QueryInterface $query)
    {
        $applicationRepository = $this->getApplicationRepository();

        $application = $applicationRepository->fetchWithLicence($query->getApplication());

        $organisation = $application->getLicence()->getOrganisation();

        $organisationQuery = [
            'organisation' => $organisation->getId()
        ];

        return $this->getQueryHandler()->handleQuery(GetApplicationListByOrganisationQuery::create($organisationQuery));
    }

    private function getApplicationRepository(): Repository\Application
    {
        return $this->getRepo(Repository\Application::class);
    }
}
