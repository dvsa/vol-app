<?php

/**
 * Prohibition
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Prohibition;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\ProhibitionDefect as DefectRepository;
use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\Defect as Query;

/**
 * Prohibition
 */
class Defect extends AbstractQueryHandler
{
    protected $repoServiceName = 'ProhibitionDefect';

    public function handleQuery(QueryInterface $query)
    {
        /* @var Query $query */

        /** @var DefectRepository $repo */
        $repo = $this->getRepo();

        return $this->result($repo->fetchUsingId($query));
    }
}
