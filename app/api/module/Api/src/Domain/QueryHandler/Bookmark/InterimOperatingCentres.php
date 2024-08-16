<?php

/**
 * InterimOperatingCentres Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * InterimOperatingCentres Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class InterimOperatingCentres extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($query);

        $criteria = Criteria::create();
        $criteria->where(
            $criteria->expr()->eq('isInterim', 'Y')
        );

        $ocs = $application->getOperatingCentres()->matching($criteria);

        $ocBundle = [
            'operatingCentre' => [
                'address',
                'conditionUndertakings' => [
                    'conditionType',
                    'attachedTo',
                    'licence',
                    'application',
                    'licConditionVariation'
                ]
            ]
        ];

        return $this->result(
            $application,
            ['licence'],
            [
                'operatingCentres' => $this->resultList($ocs, $ocBundle),
            ]
        )->serialize();
    }
}
