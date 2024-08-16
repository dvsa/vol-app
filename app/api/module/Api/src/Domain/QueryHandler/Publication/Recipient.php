<?php

/**
 * Recipient
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Publication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Recipient
 */
class Recipient extends AbstractQueryHandler
{
    protected $repoServiceName = 'Recipient';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            ['trafficAreas']
        );
    }
}
