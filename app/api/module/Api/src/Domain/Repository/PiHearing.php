<?php

/**
 * PiHearing
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Pi\PiHearing as Entity;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\ReportList as ReportListQry;

/**
 * PiHearing
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PiHearing extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetches previous hearing
     *
     * @param int $pi
     * @param \DateTime $hearingDate
     * @return Entity
     */
    public function fetchPreviousHearing($pi, $hearingDate)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->lt($this->alias . '.hearingDate', ':hearingDate'))
            ->andWhere($qb->expr()->eq($this->alias . '.pi', ':pi'))
            ->andWhere($qb->expr()->eq($this->alias . '.isAdjourned', ':isAdjourned'))
            ->setParameter('hearingDate', $hearingDate)
            ->setParameter('pi', $pi)
            ->setParameter('isAdjourned', 1)
            ->orderBy($this->alias . '.hearingDate', 'DESC')
            ->setMaxResults(1);

        $this->getQueryBuilder()->modifyQuery($qb);

        $result = $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);

        return (!empty($result[0])) ? $result[0] : null;
    }

    /**
     * Applies list filters
     *
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query instanceof ReportListQry) {
            // apply different filters for report
            $this->applyListFiltersForReport($qb, $query);
        } else {
            $qb->andWhere($qb->expr()->eq($this->alias . '.pi', ':byPi'))
                ->setParameter('byPi', $query->getPi());
        }
    }

    /**
     * Applies list filters for report
     *
     * @return void
     */
    private function applyListFiltersForReport(QueryBuilder $qb, QueryInterface $query)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('pi', 'p')
            ->with('p.case', 'c')
            ->with('c.licence', 'l')
            ->with('l.organisation', 'o')
            ->with('l.status', 'lst')
            ->with('c.transportManager', 'tm')
            ->with('tm.tmStatus', 'tmst')
            ->with('tm.homeCd', 'tmhmcd')
            ->with('tmhmcd.person', 'tmhmcdp')
            ->with('venue', 'v')
            ->with('v.address', 'va');

        // filter by start date
        $startDate = new \DateTime($query->getStartDate());
        $startDate->setTime(0, 0, 0);
        $qb->andWhere($qb->expr()->gte($this->alias . '.hearingDate', ':hearingDateFrom'))
            ->setParameter('hearingDateFrom', $startDate);

        // filter by end date
        $endDate = new \DateTime($query->getEndDate());
        $endDate->setTime(23, 59, 59);
        $qb->andWhere($qb->expr()->lte($this->alias . '.hearingDate', ':hearingDateTo'))
            ->setParameter('hearingDateTo', $endDate);

        // filter by traffic area
        $trafficAreas = $query->getTrafficAreas();

        if (!empty($trafficAreas)) {
            if (($key = array_search('other', $trafficAreas)) !== false) {
                // the list contains 'other'
                // remove 'other' from the list
                unset($trafficAreas[$key]);

                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->isNull($this->alias . '.venue'),
                        !empty($trafficAreas) ? $qb->expr()->in('v.trafficArea', $trafficAreas) : null
                    )
                );
            } else {
                $qb->andWhere($qb->expr()->in('v.trafficArea', $trafficAreas));
            }
        }
    }
}
