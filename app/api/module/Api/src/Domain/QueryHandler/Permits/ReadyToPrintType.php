<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a list of permit types ready to print
 */
class ReadyToPrintType extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitType';

    /**
     * Handle query
     *
     * @param QueryInterface $query Query
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleQuery(QueryInterface $query)
    {
        return ['results' => $this->getRepo()->fetchReadyToPrint()];
    }
}
