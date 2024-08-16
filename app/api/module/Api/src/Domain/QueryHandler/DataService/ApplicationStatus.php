<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DataService;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class ApplicationStatus extends AbstractQueryHandler
{
    protected $repoServiceName = 'DataService';

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Transfer\Query\DataService\ApplicationStatus $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\DataService $repo */
        $repo = $this->getRepo();

        $results = $this->resultList($repo->fetchApplicationStatus($query), []);

        return [
            'result' => $results,
            'count' => count($results),
        ];
    }
}
