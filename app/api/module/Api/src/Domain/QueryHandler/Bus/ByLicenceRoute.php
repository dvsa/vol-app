<?php

/**
 * Bus Registration List (by licenceId and routeNo)
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Bus Registration List (by licenceId and routeNo) Paginated.
 */
class ByLicenceRoute extends AbstractQueryHandler
{
    protected $repoServiceName = 'Bus';

    public function handleQuery(QueryInterface $query)
    {
        return [
            'results' => $this->resultList(
                $this->getRepo()->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' => $this->getRepo()->fetchCount($query)
        ];
    }
}
