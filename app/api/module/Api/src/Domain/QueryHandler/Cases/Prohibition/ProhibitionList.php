<?php

/**
 * ProhibitionList
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Prohibition;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Prohibition as ProhibitionRepository;
use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\ProhibitionList as Query;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * ProhibitionList
 */
class ProhibitionList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Prohibition';

    public function handleQuery(QueryInterface $query)
    {
        /* @var Query $query */

        /** @var ProhibitionRepository $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList($repo->fetchList($query, DoctrineQuery::HYDRATE_OBJECT)),
            'count' => $repo->fetchCount($query)
        ];
    }
}
