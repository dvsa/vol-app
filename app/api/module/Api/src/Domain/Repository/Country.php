<?php

/**
 * Country
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>, Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use DateTime;

/**
 * Country
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>, Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class Country extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Applies filters to list queries. Note we always ignore newly uploaded files until they've been fully submitted
     *
     * @param QueryBuilder   $qb    doctrine query builder
     * @param QueryInterface $query the query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getIsEcmtState') && !empty($query->getIsEcmtState())) {
            $qb->andWhere($qb->expr()->in($this->alias . '.isEcmtState', ':isEcmtState'))
              ->setParameter('isEcmtState', $query->getIsEcmtState());
            $qb->addOrderBy($this->alias . '.countryDesc', 'ASC');
        }

        if (method_exists($query, 'getIsEeaState') && !empty($query->getIsEeaState())) {
            $qb->andWhere($qb->expr()->in($this->alias . '.isEeaState', ':isEeaState'))
                ->setParameter('isEeaState', $query->getIsEeaState());
            $qb->addOrderBy($this->alias . '.countryDesc', 'ASC');
        }

        if (method_exists($query, 'hasEcmtConstraints') && $query->hasEcmtConstraints()) {
            $this->getQueryBuilder()->with('constraints', 'c');
            $qb->andWhere($qb->expr()->isNotNull('c.id'));
            $qb->addOrderBy($this->alias . '.countryDesc', 'ASC');
        }
    }

    /**
     * Retrieves a list of all country ids and their associated descriptions
     *
     * @return array
     */
    public function fetchIdsAndDescriptions()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('c.id as countryId, c.countryDesc as description')
            ->from(Entity::class, 'c')
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Returns list of countries with currently open windows
     *
     * @param int      $type Type
     * @param DateTime $now  Now
     *
     * @return array
     */
    public function fetchAvailableCountriesForIrhpApplication($type, DateTime $now)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->select($this->alias)
            ->distinct()
            ->innerJoin($this->alias . '.irhpPermitStocks', 'ips')
            ->innerJoin('ips.irhpPermitType', 'ipt')
            ->innerJoin('ips.irhpPermitWindows', 'ipw')
            ->where($qb->expr()->eq('ipt.id', ':type'))
            ->andWhere($qb->expr()->lte('ipw.startDate', ':now'))
            ->andWhere($qb->expr()->gt('ipw.endDate', ':now'))
            ->setParameter('now', $now)
            ->setParameter('type', $type)
            ->orderBy($this->alias . '.countryDesc', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns list of countries with ready to print permits
     *
     * @param int $irhpPermitTypeId Irhp Permit Type Id
     *
     * @return array
     */
    public function fetchReadyToPrint($irhpPermitTypeId)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->select($this->alias)
            ->distinct()
            ->innerJoin($this->alias . '.irhpPermitStocks', 'ips')
            ->innerJoin('ips.irhpPermitRanges', 'ipr')
            ->innerJoin('ipr.irhpPermits', 'ip')
            ->where($qb->expr()->in('ip.status', ':statuses'))
            ->andWhere('ips.irhpPermitType = :irhpPermitTypeId')
            ->setParameter('statuses', IrhpPermitEntity::$readyToPrintStatuses)
            ->setParameter('irhpPermitTypeId', $irhpPermitTypeId)
            ->orderBy($this->alias . '.countryDesc', 'ASC');

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }
}
