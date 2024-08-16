<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\LegacyOffence as LegacyOffenceRepo;

/**
 * Legacy Offence QueryHandler
 */
final class LegacyOffenceList extends AbstractQueryHandler
{
    protected $repoServiceName = 'LegacyOffence';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var LegacyOffenceRepo $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList($repo->fetchList($query, Query::HYDRATE_OBJECT)),
            'count' => $repo->fetchCount($query)
        ];
    }
}
