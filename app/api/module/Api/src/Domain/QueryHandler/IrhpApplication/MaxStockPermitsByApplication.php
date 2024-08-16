<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermits as MaxStockPermitsQuery;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermitsByApplication as MaxStockPermitsByApplicationQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get maximum stock permits by application
 */
class MaxStockPermitsByApplication extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle query
     *
     * @param QueryInterface|MaxStockPermitsByApplicationQuery $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $irhpApplication = $this->getRepo()->fetchById($query->getId());
        $licenceId = $irhpApplication->getLicence()->getId();

        return $this->getQueryHandler()->handleQuery(
            MaxStockPermitsQuery::create(['licence' => $licenceId])
        );
    }
}
