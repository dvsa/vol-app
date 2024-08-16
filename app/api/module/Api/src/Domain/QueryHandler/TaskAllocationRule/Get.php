<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TaskAllocationRule;

use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\TaskAllocationRule\Get as Qry;

class Get extends AbstractQueryHandler
{
    protected $extraRepos = [
        Repository\TaskAllocationRule::class,
    ];

    /**
     * @param Qry $query
     * @throws RuntimeException
     */
    public function handleQuery(QueryInterface $query): Result
    {
        $repo = $this->getRepo(Repository\TaskAllocationRule::class);

        return $this->result(
            $repo->fetchUsingId($query),
            [
                'category',
                'subCategory',
                'team',
                'user' => ['contactDetails' => ['person']],
                'trafficArea',
                'taskAlphaSplits',
            ]
        );
    }
}
