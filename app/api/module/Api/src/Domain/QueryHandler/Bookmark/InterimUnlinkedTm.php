<?php

/**
 * InterimUnlinkedTm Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * InterimUnlinkedTm Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class InterimUnlinkedTm extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($query);

        $criteria = Criteria::create();
        $criteria->where(
            $criteria->expr()->in('action', ['A', 'U'])
        );

        $tms = $application->getTransportManagers()->matching($criteria);

        $tmBundle = [
            'transportManager' => [
                'homeCd' => [
                    'person'
                ]
            ]
        ];

        return $this->result(
            $application,
            [],
            [
                'transportManagers' => $this->resultList($tms, $tmBundle)
            ]
        )->serialize();
    }
}
