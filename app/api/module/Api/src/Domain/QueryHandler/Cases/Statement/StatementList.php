<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Statement;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Statement as StatementRepo;

/**
 * StatementList QueryHandler
 */
final class StatementList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Statement';

    public function handleQuery(QueryInterface $query)
    {
        /** @var StatementRepo $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'requestorsContactDetails' => [
                        'person'
                    ]
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
