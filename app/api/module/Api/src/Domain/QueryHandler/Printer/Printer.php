<?php

/**
 * Printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Printer;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Printer extends AbstractQueryHandler
{
    protected $repoServiceName = 'Printer';

    public function handleQuery(QueryInterface $query)
    {
        $printer = $this->getRepo()->fetchUsingId($query);
        return $this->result($printer);
    }
}
