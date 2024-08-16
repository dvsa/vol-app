<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as Entity;
use DateTime;

/**
 * IRHP Permit Window
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitWindow extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'ipw';

    /**
     * Returns an array of IrhpPermitWindow objects that are open as of the specified date and time
     *
     *
     * @return mixed
     */
    public function fetchOpenWindows(int $irhpPermitStock, DateTime $currentDateTime)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('ipw')
            ->from(Entity::class, 'ipw')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('?1', 'ipw.irhpPermitStock'),
                $qb->expr()->between('?2', 'ipw.startDate', 'ipw.endDate')
            ))
            ->setParameter(1, $irhpPermitStock)
            ->setParameter(2, $currentDateTime);

        $this->getQueryBuilder()
           ->modifyQuery($qb)
           ->withRefdata();

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Fetch Windows by Permit Stock ID
     *
     * @param $irhpPermitStockId
     * @return array
     */
    public function fetchByIrhpPermitStockId($irhpPermitStockId)
    {
        $doctrineQb = $this->createQueryBuilder();
        $doctrineQb
            ->andWhere($doctrineQb->expr()->eq($this->alias . '.irhpPermitStock', ':irhpPermitStock'))
            ->setParameter('irhpPermitStock', $irhpPermitStockId);
        return $doctrineQb->getQuery()->getResult();
    }


    /**
     * Fetch Overlapping Windows by Permit Stock ID, proposed startDate and proposed End Date
     *
     * @param $irhpPermitStock
     * @param $proposedStartDate
     * @param $proposedEndDate
     * @param null $irhpPermitWindow
     * @return array
     */
    public function findOverlappingWindowsByType($irhpPermitStock, $proposedStartDate, $proposedEndDate, $irhpPermitWindow = null)
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->orWhere($qb->expr()->between($this->alias . '.startDate', ':proposedStartDate', ':proposedEndDate'))
            ->orWhere($qb->expr()->between($this->alias . '.endDate', ':proposedStartDate', ':proposedEndDate'))
            ->orWhere($qb->expr()->between(':proposedStartDate', $this->alias . '.startDate', $this->alias . '.endDate'))
            ->andWhere($qb->expr()->eq($this->alias . '.irhpPermitStock', ':irhpPermitStock'))
            ->setParameter('irhpPermitStock', $irhpPermitStock)
            ->setParameter('proposedStartDate', new DateTime($proposedStartDate))
            ->setParameter('proposedEndDate', new DateTime($proposedEndDate));
        if ($irhpPermitWindow !== null) {
            $qb
                ->andWhere($qb->expr()->neq($this->alias . '.id', ':irhpPermitWindow'))
                ->setParameter('irhpPermitWindow', $irhpPermitWindow);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $hydrationMode
     * @return Entity
     * @throws NotFoundException
     */
    public function fetchLastOpenWindowByStockId(int $irhpPermitStockId, $hydrationMode = Query::HYDRATE_OBJECT)
    {
        $date = new DateTime();
        $query = $this->getEntityManager()->createQueryBuilder();

        $results = $query->select('ipw')
            ->from(Entity::class, 'ipw')
            ->where($query->expr()->andX(
                $query->expr()->between('?1', 'ipw.startDate', 'ipw.endDate'),
                $query->expr()->eq('ipw.irhpPermitStock', '?2')
            ))
            ->orderBy('ipw.id', 'DESC')
            ->setParameter(1, $date)
            ->setParameter(2, $irhpPermitStockId)
            ->getQuery()
            ->getResult($hydrationMode);

        if (empty($results)) {
            throw new NotFoundException('No window available.');
        }

        return $results[0];
    }

    /**
     * Returns the latest open IrhpPermitWindow for a given IrhpPermitType
     *
     * @param int $irhpPermitTypeId Irhp Permit Type Id
     * @param DateTime $now Current datetime
     * @param int $hydrationMode Hydration mode
     * @return Entity
     * @throws NotFoundException
     */
    public function fetchLastOpenWindowByIrhpPermitType(
        int $irhpPermitTypeId,
        DateTime $now,
        $hydrationMode = Query::HYDRATE_OBJECT,
        ?int $year = null
    ) {
        $qb = $this->createQueryBuilder();

        $qb
            ->select($this->alias)
            ->innerJoin($this->alias . '.irhpPermitStock', 'ips')
            ->innerJoin('ips.irhpPermitType', 'ipt')
            ->where($qb->expr()->eq('ipt.id', ':irhpPermitTypeId'))
            ->andWhere($qb->expr()->lte($this->alias . '.startDate', ':now'))
            ->andWhere($qb->expr()->gt($this->alias . '.endDate', ':now'))
            ->setParameter('irhpPermitTypeId', $irhpPermitTypeId)
            ->setParameter('now', $now)
            ->orderBy($this->alias . '.endDate', 'DESC')
            ->setMaxResults(1);

        if ($year) {
            $fromDate = new DateTime($year . '-01-01 00:00:00');
            $toDate = new DateTime($year . '-12-31 23:59:59');

            $qb->andWhere('ips.validTo BETWEEN :fromDate AND :toDate')
                ->setParameter('fromDate', $fromDate)
                ->setParameter('toDate', $toDate);
        }

        $results = $qb->getQuery()->getResult($hydrationMode);

        if (empty($results)) {
            throw new NotFoundException('No window available.');
        }

        return $results[0];
    }

    /**
     * Fetch all windows to be closed
     *
     * @param \DateTime $currentDateTime Current datetime
     * @param string    $since           Date since which the query should go to find an expired window
     *
     * @return array
     */
    public function fetchWindowsToBeClosed(\DateTime $currentDateTime, $since = '-1 day')
    {
        $clonedDateTime = clone $currentDateTime;

        $qb = $this->createQueryBuilder();

        $qb
            ->where($qb->expr()->gte($this->alias . '.endDate', ':periodStart'))
            ->andWhere($qb->expr()->lt($this->alias . '.endDate', ':periodEnd'))
            ->setParameter('periodStart', $clonedDateTime->modify($since)->setTime(0, 0, 0))
            ->setParameter('periodEnd', $currentDateTime);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch all open windows for all the countries provided
     *
     * @param int      $type      Type
     * @param array    $countries List of country ids to check for
     * @param DateTime $now       Now
     *
     * @return array
     */
    public function fetchOpenWindowsByCountry($type, array $countries, DateTime $now)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->select($this->alias)
            ->distinct()
            ->innerJoin($this->alias . '.irhpPermitStock', 'ips')
            ->innerJoin('ips.irhpPermitType', 'ipt')
            ->innerJoin('ips.country', 'c')
            ->where($qb->expr()->eq('ipt.id', ':type'))
            ->andWhere($qb->expr()->lte($this->alias . '.startDate', ':now'))
            ->andWhere($qb->expr()->gt($this->alias . '.endDate', ':now'))
            ->andWhere($qb->expr()->in('c.id', ':countries'))
            ->setParameter('type', $type)
            ->setParameter('now', $now)
            ->setParameter('countries', $countries);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch all open windows for the specified type
     *
     * @param int $type Type
     * @param DateTime $now Now
     * @param bool $internalUser
     *
     * @return array
     */
    public function fetchOpenWindowsByType($type, DateTime $now, $internalUser = false)
    {
        $qb = $this->createQueryBuilder();

        $qb->select($this->alias)
            ->innerJoin($this->alias . '.irhpPermitStock', 'ips')
            ->innerJoin('ips.irhpPermitType', 'ipt')
            ->where($qb->expr()->eq('ipt.id', ':type'))
            ->andWhere($qb->expr()->lte($this->alias . '.startDate', ':now'))
            ->andWhere($qb->expr()->gt($this->alias . '.endDate', ':now'))
            ->setParameter('type', $type)
            ->setParameter('now', $now);

        if (!$internalUser) {
            $qb->andWhere($qb->expr()->neq('ips.hiddenSs', 1));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch all open windows for the specified type and year
     *
     * @param int $type Type
     * @param DateTime $now Now
     * @param int $year
     * @param bool $internalUser
     *
     * @return array
     * @throws \Exception
     */
    public function fetchOpenWindowsByTypeYear($type, DateTime $now, $year, $internalUser = false)
    {
        $fromDate = new DateTime($year . '-01-01 00:00:00');
        $toDate = new DateTime($year . '-12-31 23:59:59');

        $qb = $this->createQueryBuilder();

        $qb->select($this->alias, 'ipr', 'ips')
            ->innerJoin($this->alias . '.irhpPermitStock', 'ips')
            ->innerJoin('ips.irhpPermitType', 'ipt')
            ->innerJoin('ips.irhpPermitRanges', 'ipr')
            ->where($qb->expr()->eq('ipt.id', ':type'))
            ->andWhere($qb->expr()->lte($this->alias . '.startDate', ':now'))
            ->andWhere($qb->expr()->gt($this->alias . '.endDate', ':now'))
            ->andWhere($qb->expr()->between('ips.validTo', ':fromDate', ':toDate'))
            ->setParameter('type', $type)
            ->setParameter('now', $now)
            ->setParameter('fromDate', $fromDate)
            ->setParameter('toDate', $toDate);

        if (!$internalUser) {
            $qb->andWhere($qb->expr()->neq('ips.hiddenSs', 1));
        }

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata();

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }
}
