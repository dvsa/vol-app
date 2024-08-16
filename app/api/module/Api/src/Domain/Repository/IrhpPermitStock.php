<?php

/**
 * IrhpPermitStock
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;

/**
 * IrhpPermitStock
 */
class IrhpPermitStock extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'ips';

    /**
     * Returns list of stocks ready to print
     *
     * @param int    $irhpPermitTypeId Irhp Permit Type Id
     * @param string $countryId        Country Id
     *
     * @return array
     */
    public function fetchReadyToPrint($irhpPermitTypeId, $countryId = null)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->select($this->alias)
            ->distinct()
            ->innerJoin($this->alias . '.irhpPermitRanges', 'ipr')
            ->innerJoin('ipr.irhpPermits', 'ip')
            ->where($qb->expr()->in('ip.status', ':statuses'))
            ->andWhere($this->alias . '.irhpPermitType = :irhpPermitTypeId')
            ->setParameter('statuses', IrhpPermitEntity::$readyToPrintStatuses)
            ->setParameter('irhpPermitTypeId', $irhpPermitTypeId);

        if (!empty($countryId)) {
            $qb
                ->andWhere($this->alias . '.country = :countryId')
                ->setParameter('countryId', $countryId);
        }

        $qb->orderBy($this->alias . '.validFrom', 'DESC');

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @return array
     */
    public function fetchByIrhpPermitType(int $irhpPermitType)
    {
        return $this->fetchByX('irhpPermitType', [$irhpPermitType]);
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        $qb = $this->createQueryBuilder();
        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch all bilateral stocks with open windows for a given country
     *
     * @param DateTime $now Now
     * @return array
     */
    public function fetchOpenBilateralStocksByCountry(string $country, DateTime $now)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->select($this->alias)
            ->innerJoin('ips.irhpPermitType', 'ipt')
            ->innerJoin('ips.irhpPermitWindows', 'ipw')
            ->innerJoin('ips.country', 'c')
            ->where($qb->expr()->eq($this->alias . '.country', ':country'))
            ->andWhere($qb->expr()->lte('ipw.startDate', ':now'))
            ->andWhere($qb->expr()->gt('ipw.endDate', ':now'))
            ->andWhere($qb->expr()->eq('ipt.id', ':type'))
            ->setParameter('country', $country)
            ->setParameter('type', IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL)
            ->setParameter('now', $now);

        if ($country == Country::ID_MOROCCO) {
            $qb
                ->innerJoin('ips.permitCategory', 'r')
                ->addOrderBy('r.displayOrder', 'ASC')
                ->addOrderBy('ips.validTo', 'ASC');
        }

        return $qb->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getResult(Query::HYDRATE_ARRAY);
    }
}
