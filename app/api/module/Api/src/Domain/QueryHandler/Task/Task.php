<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Task;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Task extends AbstractQueryHandler
{
    protected $repoServiceName = 'Task';

    protected $extraRepos = ['EventHistory'];

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();
        $repo->disableSoftDeleteable();

        $taskHistory = $this->getRepo('EventHistory')->fetchByTask($query->getId());

        return $this->result(
            $repo->fetchUsingId($query),
            [
                'category',
                'subCategory',
                'assignedToTeam',
                'assignedToUser',
                'assignedByUser' => [
                    'contactDetails' => [
                        'person'
                    ]
                ],
                'lastModifiedBy' => [
                    'contactDetails' => [
                        'person'
                    ]
                ]
            ],
            [
                'taskHistory' => $taskHistory
            ]
        );
    }
}
