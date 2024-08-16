<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * People
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class People extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ApplicationOrganisationPerson', 'OrganisationPerson', 'Person', 'Licence'];

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Transfer\Query\Application\People $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /* @var $application \Dvsa\Olcs\Api\Entity\Application\Application */
        $application = $this->getRepo()->fetchUsingId($query);
        $licence = $application->getLicence();

        /** @var Repository\OrganisationPerson $orgPersonRepo */
        $orgPersonRepo = $this->getRepo('OrganisationPerson');
        $orgPersons = $orgPersonRepo->fetchListForOrganisation(
            $licence->getOrganisation()->getId()
        );

        /** @var Repository\ApplicationOrganisationPerson $appOrgPersonRepo */
        $appOrgPersonRepo = $this->getRepo('ApplicationOrganisationPerson');
        $appOrgPersons = $appOrgPersonRepo->fetchListForApplication(
            $query->getId()
        );

        /** @var Repository\Licence $licRepo */
        $licRepo = $this->getRepo('Licence');
        $licences = $licRepo->fetchByOrganisationIdAndStatuses(
            $licence->getOrganisation()->getId(),
            [
                LicenceEntity::LICENCE_STATUS_VALID,
                LicenceEntity::LICENCE_STATUS_SURRENDERED,
                LicenceEntity::LICENCE_STATUS_CURTAILED,
            ]
        );

        return $this->result(
            $application,
            [
                'licence' => ['organisation' => ['type']]
            ],
            [
                'useDeltas' => $application->useDeltasInPeopleSection(),
                'hasInforceLicences' => $licence->getOrganisation()->hasInforceLicences(),
                'isExceptionalType' => $licence->getOrganisation()->isSoleTrader() ||
                    $licence->getOrganisation()->isPartnership(),
                'isSoleTrader' => $licence->getOrganisation()->isSoleTrader(),
                'people' => $this->resultList($orgPersons, ['person' => ['title']]),
                'application-people' => $this->resultList($appOrgPersons, ['person', 'originalPerson']),
                'hasMoreThanOneValidCurtailedOrSuspendedLicences' =>
                    is_array($licences) && count($licences) > 1,
            ]
        );
    }
}
