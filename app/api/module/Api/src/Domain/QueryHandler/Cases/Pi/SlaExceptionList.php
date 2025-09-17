<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Pi;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get List of SLA Exceptions
 */
final class SlaExceptionList extends AbstractQueryHandler
{
    protected $repoServiceName = 'SlaException';

    /**
     * Handle query
     *
     * @param QueryInterface $query Query
     * @return array
     */
    public function handleQuery(QueryInterface $query): array
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\SlaException $repo */
        $repo = $this->getRepo();

        $slaExceptions = $repo->fetchActive();

        $results = [];
        foreach ($slaExceptions as $slaException) {
            $effectiveFrom = $slaException->getEffectiveFrom();
            $effectiveTo = $slaException->getEffectiveTo();

            $results[] = [
                'id' => $slaException->getId(),
                'slaDescription' => $slaException->getSlaDescription(),
                'slaExceptionDescription' => $slaException->getSlaExceptionDescription(),
                'effectiveFrom' => $effectiveFrom instanceof \DateTime ?
                    $effectiveFrom->format('Y-m-d') : $effectiveFrom,
                'effectiveTo' => $effectiveTo instanceof \DateTime ?
                    $effectiveTo->format('Y-m-d') : $effectiveTo,
            ];
        }

        return [
            'count' => count($results),
            'results' => $results,
        ];
    }
}
