<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention;

use DateTime;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\DataRetention;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\DataRetention\GetProcessedList as Query;

/**
 * Get a list of processed data retention records
 */
class GetProcessedList extends AbstractQueryHandler
{
    protected $repoServiceName = 'DataRetention';

    /**
     * Return list of data retention records that have been processed
     *
     * @param QueryInterface|Query $query DTO
     *
     * @return array
     * @throws RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Query $query */
        /** @var DataRetention $repo */
        $repo = $this->getRepo();

        $results = $repo->fetchAllProcessedForRule(
            $query->getDataRetentionRuleId(),
            new DateTime($query->getStartDate()),
            new DateTime($query->getEndDate())
        );

        return [
            'count' => count($results),
            'result' => $results
        ];
    }
}
