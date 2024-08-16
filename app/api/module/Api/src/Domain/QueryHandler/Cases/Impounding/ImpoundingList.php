<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Impounding;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Impounding as ImpoundingRepo;

/**
 * ImpoundingList
 */
final class ImpoundingList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Impounding';

    public function handleQuery(QueryInterface $query)
    {
        /** @var ImpoundingRepo $repo */
        $repo = $this->getRepo();
        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                   'presidingTc'
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
