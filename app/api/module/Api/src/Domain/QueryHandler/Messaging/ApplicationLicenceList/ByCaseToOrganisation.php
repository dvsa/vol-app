<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\ApplicationLicenceList;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByCaseToOrganisation as GetApplicationListByCaseToOrganisationQuery;
use Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList\ByOrganisation as GetApplicationListByOrganisationQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class ByCaseToOrganisation extends AbstractQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];

    protected $extraRepos = [Repository\Cases::class];

    /** @param GetApplicationListByCaseToOrganisationQuery|QueryInterface $query */
    public function handleQuery(QueryInterface $query)
    {
        $casesRepo = $this->getRepo(Repository\Cases::class);
        /** @var Cases $case */
        $case = $casesRepo->fetchById($query->getCase());
        $organisationQuery = [
            'organisation' => $case->getRelatedOrganisation()->getId(),
        ];

        return $this->getQueryHandler()->handleQuery(
            GetApplicationListByOrganisationQuery::create($organisationQuery),
        );
    }
}
