<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TrafficArea;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\TrafficArea\TrafficAreaInternalList as Qry;

class TrafficAreaInternalList extends AbstractQueryHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'TrafficArea';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();
        assert($query instanceof Qry);
        assert($repo instanceof TrafficAreaRepo);

        $result = [
            'result' => $this->resultList(
                $repo->fetchByIds($query->getTrafficAreas())
            ),
        ];

        return $result;
    }
}
