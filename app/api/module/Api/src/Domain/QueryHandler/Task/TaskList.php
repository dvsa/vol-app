<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Task;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Task List
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaskList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TaskSearchView';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $data = $query->getArrayCopy();

        unset($data['assignedToTeam']);
        unset($data['assignedToUser']);
        unset($data['category']);
        unset($data['taskSubCategory']);
        unset($data['date']);
        unset($data['status']);
        unset($data['urgent']);
        unset($data['messaging']);
        unset($data['showTasks']);

        $unfilteredQuery = \Dvsa\Olcs\Transfer\Query\Task\TaskList::create($data);

        /** @var \Dvsa\Olcs\Api\Domain\Repository\TaskSearchView $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($query),
            'count-unfiltered' => $repo->hasRows($unfilteredQuery),
        ];
    }
}
