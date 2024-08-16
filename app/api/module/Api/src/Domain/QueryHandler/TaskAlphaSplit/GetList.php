<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TaskAlphaSplit;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get List of TaskAlphaSplit
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TaskAlphaSplit';

    public function handleQuery(QueryInterface $query)
    {
        /* @var $query \Dvsa\Olcs\Transfer\Query\TaskAlphaSplit\GetList */

        return [
            'result' => $this->resultList(
                $this->getRepo()->fetchList($query, \Doctrine\ORM\Query::HYDRATE_OBJECT),
                [
                    'taskAllocationRule',
                    'user' => ['contactDetails' => ['person']],
                ]
            ),
            'count' => $this->getRepo()->fetchCount($query),
            'count-unfiltered' => $this->getRepo()->hasRows($query),
        ];
    }
}
