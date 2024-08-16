<?php

/**
 * Business Details
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Business Details
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetails extends AbstractQueryHandler
{
    protected $repoServiceName = 'Organisation';

    public function handleQuery(QueryInterface $query)
    {
        return $this->getRepo()->fetchBusinessDetailsUsingId($query);
    }
}
