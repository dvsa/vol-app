<?php

/**
 * Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficAreaEnforcementArea;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentres extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['LicenceOperatingCentre', 'TrafficArea', 'Document'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var \Dvsa\Olcs\Api\Entity\Licence\Licence $licence */
        $licence = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $licence,
            [
                'trafficArea',
                'enforcementArea'
            ],
            [
                'requiresVariation' => $this->isGranted(Permission::SELFSERVE_USER),
                'operatingCentres' => $this->getRepo('LicenceOperatingCentre')
                    ->fetchByLicenceIdForOperatingCentres($licence->getId(), $query),
                'isPsv' => $licence->isPsv(),
                'canHaveCommunityLicences' => $licence->canHaveCommunityLicences(),
                'canHaveSchedule41' => false,
                'possibleEnforcementAreas' => $this->getPossibleEnforcementAreas($licence),
                'possibleTrafficAreas' => $this->getPossibleTrafficAreas(),
                // Vars used for add form
                'canAddAnother' => true,
               'documents' =>
                    $this->resultList(
                        $this->getRepo('Document')->fetchUnlinkedOcDocumentsForEntity($licence)
                    ),
            ]
        );
    }

    private function getPossibleTrafficAreas()
    {
        return $this->getRepo('TrafficArea')->getValueOptions();
    }

    private function getPossibleEnforcementAreas(LicenceEntity $licence)
    {
        if ($licence->getTrafficArea() === null) {
            return [];
        }

        /** @var TrafficAreaEnforcementArea[] $tas */
        $tas = $licence->getTrafficArea()->getTrafficAreaEnforcementAreas();

        $options = [];
        foreach ($tas as $ta) {
            $options[$ta->getEnforcementArea()->getId()] = $ta->getEnforcementArea()->getName();
        }

        return $options;
    }
}
