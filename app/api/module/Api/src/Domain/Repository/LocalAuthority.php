<?php

/**
 * Local Authority
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as Entity;

/**
 * Local Authority
 */
class LocalAuthority extends AbstractRepository
{
    protected $entity = Entity::class;

    public function fetchByTxcName($txcNames)
    {
        /* @var QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();
        $this->getQueryBuilder()->modifyQuery($qb);
        $qb->andWhere($qb->expr()->in($this->alias . '.txcName', $txcNames));

        return $qb->getQuery()->execute();
    }

    /**
     * Fetches a list of local authorities matching the naptan codes
     *
     * @param $naptanCodes
     * @return mixed
     */
    public function fetchByNaptan($naptanCodes)
    {
        /* @var QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();
        $this->getQueryBuilder()->modifyQuery($qb);
        $qb->andWhere($qb->expr()->in($this->alias . '.naptanCode', $naptanCodes));

        return $qb->getQuery()->execute();
    }
}
