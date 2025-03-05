<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Si\Applied;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Penalty
 */
final class Penalty extends AbstractQueryHandler
{
    protected $repoServiceName = 'SiPenalty';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result($this->getRepo()->fetchUsingId($query), ['siPenaltyType', 'erruPenaltyRequested']);
    }
}
