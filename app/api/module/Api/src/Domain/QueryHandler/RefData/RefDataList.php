<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\RefData;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get a list of RefData
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class RefDataList extends AbstractQueryHandler
{
    protected $repoServiceName = 'RefData';

    public function handleQuery(QueryInterface $query)
    {
        /* @var $repo \Dvsa\Olcs\Api\Domain\Repository\RefData */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList($repo->fetchList($query, Query::HYDRATE_OBJECT)),
            'count' => $repo->fetchCount($query)
        ];
    }
}
