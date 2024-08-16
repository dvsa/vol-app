<?php

/**
 * DefectList
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Prohibition;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\ProhibitionDefect as DefectRepository;
use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\DefectList as Query;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * DefectList
 */
class DefectList extends AbstractQueryHandler
{
    protected $repoServiceName = 'ProhibitionDefect';

    public function handleQuery(QueryInterface $query)
    {
        /* @var Query $query */

        /** @var DefectRepository $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList($repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT), ['prohibition']),
            'count' => $repo->fetchCount($query)
        ];
    }
}
