<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a list of permit countries ready to print
 */
class ReadyToPrintCountry extends AbstractQueryHandler
{
    protected $repoServiceName = 'Country';

    /**
     * Handle query
     *
     * @param QueryInterface $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        return ['results' => $this->getRepo()->fetchReadyToPrint($query->getIrhpPermitType())];
    }
}
