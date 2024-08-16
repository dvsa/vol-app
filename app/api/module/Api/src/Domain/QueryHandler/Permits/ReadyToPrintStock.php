<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a list of permit stocks ready to print
 */
class ReadyToPrintStock extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitStock';

    /**
     * Handle query
     *
     * @param QueryInterface $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        return ['results' => $this->getRepo()->fetchReadyToPrint($query->getIrhpPermitType(), $query->getCountry())];
    }
}
