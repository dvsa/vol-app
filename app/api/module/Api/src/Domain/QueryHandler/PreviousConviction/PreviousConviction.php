<?php

/**
 * Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\PreviousConviction;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PreviousConviction extends AbstractQueryHandler
{
    protected $repoServiceName = 'PreviousConviction';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result($this->getRepo()->fetchUsingId($query));
    }
}
