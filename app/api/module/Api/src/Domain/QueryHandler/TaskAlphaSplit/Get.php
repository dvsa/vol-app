<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TaskAlphaSplit;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a single TaskAlphaSplit
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Get extends AbstractQueryHandler
{
    protected $repoServiceName = 'TaskAlphaSplit';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();
        return $this->result(
            $repo->fetchUsingId($query),
            [
                'taskAllocationRule',
                'user' => ['contactDetails' => ['person']],
            ]
        );
    }
}
