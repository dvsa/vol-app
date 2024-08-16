<?php

/**
 * Prohibition
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Prohibition;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Prohibition as ProhibitionRepository;
use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\Prohibition as Query;

/**
 * Prohibition
 */
class Prohibition extends AbstractQueryHandler
{
    protected $repoServiceName = 'Prohibition';

    public function handleQuery(QueryInterface $query)
    {
        /* @var Query $query */

        /** @var ProhibitionRepository $repo */
        $repo = $this->getRepo();

        return $this->result($repo->fetchUsingId($query));
    }
}
