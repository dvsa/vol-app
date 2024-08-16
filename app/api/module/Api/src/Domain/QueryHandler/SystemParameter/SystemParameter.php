<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\SystemParameter;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SysParamRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameter as SysParamQry;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

class SystemParameter extends AbstractQueryHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'SystemParameter';

    public function handleQuery(QueryInterface $query): Result
    {
        assert($query instanceof SysParamQry);
        $id = $query->getId();

        if ($this->cacheService->hasCustomItem(CacheEncryption::SYS_PARAM_IDENTIFIER, $id)) {
            return $this->cacheService->getCustomItem(CacheEncryption::SYS_PARAM_IDENTIFIER, $id);
        }

        $repo = $this->getRepo();
        assert($repo instanceof SysParamRepo);

        $systemParameter = $repo->fetchById($id);
        $result = $this->result($systemParameter);

        $this->cacheService->setCustomItem(CacheEncryption::SYS_PARAM_IDENTIFIER, $result, $id);
        return $result;
    }
}
