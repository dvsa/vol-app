<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\InternalApplicationsSummary as InternalApplicationsSummaryQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Internal applications summary
 */
final class InternalApplicationsSummary extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle query
     *
     * @param QueryInterface|InternalApplicationsSummaryQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->getRepo()->fetchInternalApplicationsSummary(
            $query->getLicence(),
            $query->getStatus()
        );
    }
}
