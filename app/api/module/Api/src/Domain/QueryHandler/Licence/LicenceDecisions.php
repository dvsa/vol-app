<?php

/**
 * LicenceDecisions.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;

/**
 * LicenceDecisions
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class LicenceDecisions extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['BusRegSearchView', 'IrhpPermit'];

    public function handleQuery(QueryInterface $query)
    {
        $licence = $this->getRepo()->fetchUsingId($query);
        /* @var $licence \Dvsa\Olcs\Api\Entity\Licence\Licence */

        $activeBusRoutes = $this->getRepo('BusRegSearchView')->fetchActiveByLicence($licence);

        $irhpPermitQuery = GetListByLicence::create(
            [
                'licence' => $query->getId(),
                'validOnly' => true
            ]
        );
        $irhpPermitCount = $this->getRepo('IrhpPermit')->fetchCount($irhpPermitQuery);

        $decisionCriteria['activeComLics'] = !$licence->getActiveCommunityLicences()->isEmpty();
        $decisionCriteria['activeBusRoutes'] = count($activeBusRoutes) > 0;
        $decisionCriteria['activeVariations'] = !$licence->getActiveVariations()->isEmpty();
        $decisionCriteria['activePermits'] = $irhpPermitCount > 0;
        $decisionCriteria['ongoingPermitApplications'] = !$licence->getOngoingIrhpApplications()->isEmpty();
        $decisionCriteria['validCorPermitApplications'] = $licence->hasValidCertificateOfRoadworthinessApplications();

        $suitableForDecisions = true;
        if (in_array(true, $decisionCriteria)) {
            $suitableForDecisions = $decisionCriteria;
        }

        return $this->result(
            $licence,
            [],
            ['suitableForDecisions' => $suitableForDecisions]
        );
    }
}
