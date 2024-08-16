<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Opposition;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Opposition as OppositionRepo;

/**
 * Opposition QueryHandler
 */
final class OppositionList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Opposition';

    /**
     * Handle Query
     *
     * @param \Dvsa\Olcs\Transfer\Query\Opposition\OppositionList $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var OppositionRepo $repo */
        $repo = $this->getRepo();

        $result = $this->resultList(
            $repo->fetchList($query, Query::HYDRATE_OBJECT),
            [
                'case' => [
                    'application',
                ],
                'grounds',
                'opposer' => [
                    'contactDetails' => [
                        'person',
                    ],
                ],
            ]
        );

        return [
            'result' => $result,
            'count' => count($result),
        ];
    }
}
