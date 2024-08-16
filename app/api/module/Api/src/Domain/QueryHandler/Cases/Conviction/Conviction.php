<?php

/**
 * Conviction
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Conviction;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Conviction as ConvictionRepository;

/**
 * Conviction
 */
class Conviction extends AbstractQueryHandler
{
    protected $repoServiceName = 'Conviction';

    public function handleQuery(QueryInterface $query)
    {
        /** @var ConvictionRepository $repo */
        $repo = $this->getRepo();

        return $this->result($repo->fetchUsingId($query), ['case']);
    }
}
