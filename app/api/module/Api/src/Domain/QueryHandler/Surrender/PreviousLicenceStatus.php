<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Surrender;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class PreviousLicenceStatus extends AbstractQueryHandler
{
    protected $repoServiceName = 'EventHistory';

    public function handleQuery(QueryInterface $query)
    {
        return $this->getRepo()->fetchPreviousLicenceStatus($query->getId());
    }
}
