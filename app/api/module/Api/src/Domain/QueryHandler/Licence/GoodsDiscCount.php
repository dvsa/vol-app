<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Goods Disc Count
 *
 */
class GoodsDiscCount extends AbstractQueryHandler
{
    protected $repoServiceName = 'GoodsDisc';

    /**
     * Handler
     *
     * @param \Dvsa\Olcs\Transfer\Query\Licence\GoodsDiscCount $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Repository\GoodsDisc $goodsDiscRepo */
        $goodsDiscRepo = $this->getRepo();
        return $goodsDiscRepo->countForLicence($query->getId());
    }
}
