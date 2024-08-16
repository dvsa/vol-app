<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveIssuedPermitsSummary as SelfserveIssuedPermitsSummaryQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Selfserve issued permits summary
 */
final class SelfserveIssuedPermitsSummary extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle query
     *
     * @param QueryInterface|SelfserveIssuedPermitsSummaryQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->getRepo()->fetchSelfserveIssuedPermitsSummary(
            $query->getOrganisation()
        );
    }
}
