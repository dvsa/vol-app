<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\ConditionUndertaking;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * ConditionUndertaking
 */
final class ConditionUndertaking extends AbstractQueryHandler
{
    protected $repoServiceName = 'ConditionUndertaking';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingCaseId($query),
            [
                'operatingCentre'
            ]
        );
    }
}
