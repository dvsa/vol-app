<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TmCaseDecision;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * GetByCase
 */
final class GetByCase extends AbstractQueryHandler
{
    protected $repoServiceName = 'TmCaseDecision';

    /**
     * Handle query
     *
     * @param QueryInterface $query DTO
     *
     * @return array|\Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        $tmCaseDecision = $this->getRepo()->fetchLatestUsingCase($query);

        if ($tmCaseDecision === false) {
            return [];
        }

        return $this->result(
            $tmCaseDecision,
            ['rehabMeasures', 'unfitnessReasons']
        );
    }
}
