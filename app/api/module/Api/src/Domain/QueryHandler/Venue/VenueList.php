<?php

/**
 * Venue
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Venue;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Venue as VenueRepo;

/**
 * Venue
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class VenueList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Venue';

    public function handleQuery(QueryInterface $query)
    {
        /** @var VenueRepo $repo */
        $repo = $this->getRepo();
        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
