<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\BusRegBrowseView;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * BusRegBrowseList
 */
class BusRegBrowseList extends AbstractQueryHandler
{
    protected $repoServiceName = 'BusRegBrowseView';

    /**
     * Browse
     *
     * @param QueryInterface $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var BusRegBrowseView $repo */
        $repo = $this->getRepo();

        return [
            'results' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
