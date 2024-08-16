<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * SlaTargetDate
 */
final class SlaTargetDate extends AbstractQueryHandler
{
    protected $repoServiceName = 'SlaTargetDate';

    public function handleQuery(QueryInterface $query)
    {
        $slaTargetDate = $this->getRepo()->fetchUsingEntityIdAndType($query->getEntityType(), $query->getEntityId());

        // return the SLA with the associated entity
        return $this->result(
            $slaTargetDate,
            [
                $query->getEntityType()
            ]
        );
    }
}
