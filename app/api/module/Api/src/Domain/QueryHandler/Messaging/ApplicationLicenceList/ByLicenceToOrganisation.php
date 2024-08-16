<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\ApplicationLicenceList;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByLicenceToOrganisation as GetApplicationListByLicenceToOrganisationQuery;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByOrganisation as GetApplicationListByOrganisationQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class ByLicenceToOrganisation extends AbstractQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];

    protected $extraRepos = [Repository\Licence::class];

    /**
     * @param GetApplicationListByLicenceToOrganisationQuery $query
     * @throws NotFoundException
     */
    public function handleQuery(QueryInterface $query)
    {
        $licenceRepository = $this->getLicenceRepository();

        $licence = $licenceRepository->fetchById($query->getLicence());

        $organisation = $licence->getOrganisation();

        $organisationQuery = [
            'organisation' => $organisation->getId()
        ];

        return $this->getQueryHandler()->handleQuery(GetApplicationListByOrganisationQuery::create($organisationQuery));
    }

    private function getLicenceRepository(): Repository\Licence
    {
        return $this->getRepo(Repository\Licence::class);
    }
}
