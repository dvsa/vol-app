<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\GracePeriod;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Grace Periods
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class GracePeriods extends AbstractQueryHandler
{
    protected $repoServiceName = 'GracePeriod';

    /**
     * @param \Dvsa\Olcs\Transfer\Query\GracePeriod\GracePeriods $query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\GracePeriod $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList($repo->fetchList($query, Query::HYDRATE_OBJECT)),
            'count' => $repo->fetchCount($query),
        ];
    }
}
