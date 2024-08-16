<?php

/**
 * Single NonPi
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\NonPi;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\NonPi as NonPiRepository;

/**
 * Single NonPi
 */
class Single extends AbstractQueryHandler
{
    protected $repoServiceName = 'NonPi';

    public function handleQuery(QueryInterface $query)
    {
        /** @var NonPiRepository $repo */
        $repo = $this->getRepo();

        return $this->result($repo->fetchUsingCaseId($query), ['case', 'venue', 'presidingTc']);
    }
}
