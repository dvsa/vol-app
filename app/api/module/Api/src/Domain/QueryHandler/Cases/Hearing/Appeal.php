<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Hearing;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Appeal
 */
final class Appeal extends AbstractQueryHandler
{
    protected $repoServiceName = 'Appeal';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result($this->getRepo()->fetchUsingCaseId($query), ['case']);
    }
}
