<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a list of permit range types ready to print
 */
class ReadyToPrintRangeType extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitRange';

    /**
     * Handle query
     *
     * @param QueryInterface $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        return ['results' => $this->getRepo()->fetchReadyToPrint($query->getIrhpPermitStock())];
    }
}
