<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveApplicationsSummary as SelfserveApplicationsSummaryQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Selfserve applications summary
 */
final class SelfserveApplicationsSummary extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle query
     *
     * @param QueryInterface|SelfserveApplicationsSummaryQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->getRepo()->fetchSelfserveApplicationsSummary(
            $query->getOrganisation()
        );
    }
}
