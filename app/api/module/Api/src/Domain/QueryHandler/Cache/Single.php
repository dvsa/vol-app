<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cache;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Query\Cache\Single as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Single extends AbstractQueryHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    public function handleQuery(QueryInterface $query)
    {
        assert($query instanceof Qry);

        $queryData = ['id' => $query->getUniqueId()];
        $queryDto = $this->cacheService->getQueryFromCustomIdentifier($query->getId());

        if (!(is_a($queryDto, QueryInterface::class, true))) {
            throw new \Exception('Query not found');
        }

        $childQuery = $queryDto::create($queryData);

        return $this->getQueryHandler()->handleQuery($childQuery);
    }
}
