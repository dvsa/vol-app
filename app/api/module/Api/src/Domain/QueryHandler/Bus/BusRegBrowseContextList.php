<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\BusRegBrowseView;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * BusRegBrowseContextList
 */
class BusRegBrowseContextList extends AbstractQueryHandler
{
    protected $repoServiceName = 'BusRegBrowseView';

    /**
     * Returns a distinct list of column entries identified by query->getContext().
     *
     * @param QueryInterface $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var BusRegBrowseView $repo */
        $repo = $this->getRepo();

        $results = $repo->fetchDistinctList($query->getContext());

        return [
            'result' => $results,
            'count' => count($results)
        ];
    }
}
