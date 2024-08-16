<?php

/**
 * IRHP Permit Type
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;

/**
 * Irhp Permit Type
 */
class IrhpPermitType extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'ipt';

    /**
     * Returns list of types with currently open windows
     *
     * @param DateTime $now Now
     *
     * @return array
     */
    public function fetchAvailableTypes(DateTime $now)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->select($this->alias, 'rd')
            ->innerJoin($this->alias . '.name', 'rd')
            ->innerJoin($this->alias . '.irhpPermitStocks', 'ips')
            ->innerJoin('ips.irhpPermitWindows', 'ipw')
            ->where($qb->expr()->lte('ipw.startDate', ':now'))
            ->andWhere($qb->expr()->gt('ipw.endDate', ':now'))
            ->andWhere($qb->expr()->neq('ips.hiddenSs', 1))
            ->setParameter('now', $now)
            ->orderBy('rd.displayOrder', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns list of types ready to print
     *
     * @return array
     */
    public function fetchReadyToPrint()
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->select($this->alias, 'rd')
            ->distinct()
            ->innerJoin($this->alias . '.name', 'rd')
            ->innerJoin($this->alias . '.irhpPermitStocks', 'ips')
            ->innerJoin('ips.irhpPermitRanges', 'ipr')
            ->innerJoin('ipr.irhpPermits', 'ip')
            ->where($qb->expr()->in('ip.status', ':statuses'))
            ->setParameter('statuses', IrhpPermitEntity::$readyToPrintStatuses)
            ->orderBy('rd.description', 'ASC');

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }
}
