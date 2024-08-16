<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\View\BusRegBrowseView as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * BusRegBrowseView
 */
class BusRegBrowseView extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch a distinct list of record column
     *
     * @param string $field Field
     *
     * @return array
     */
    public function fetchDistinctList($field)
    {
        $qb = $this->createQueryBuilder();

        $qb->distinct()
            ->select([$this->alias . '.' . $field]);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Fetch for export
     *
     * @param array  $columns      Columns to export
     * @param string $date         Date
     * @param array  $trafficAreas Traffic areas
     * @param string $status       Status
     *
     * @return array
     */
    public function fetchForExport($columns, $date, $trafficAreas, $status = null)
    {
        // prefix columns with the table alias
        array_walk(
            $columns,
            function (&$item) {
                $item = $this->alias . '.' . $item;
            }
        );

        $qb = $this->createQueryBuilder();

        $qb->select($columns);

        $qb->andWhere($qb->expr()->eq($this->alias . '.acceptedDate', ':byAcceptedDate'))
            ->setParameter('byAcceptedDate', (new \DateTime($date))->format('Y-m-d'));

        $qb->andWhere($qb->expr()->in($this->alias . '.trafficAreaId', ':byTrafficAreas'))
            ->setParameter('byTrafficAreas', $trafficAreas);

        if (!empty($status)) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.status', ':byStatus'))
                ->setParameter('byStatus', $status);
        }

        return $qb->getQuery()->iterate();
    }

    /**
     * Applies filters to list queries
     *
     * @param QueryBuilder   $qb    doctrine query builder
     * @param QueryInterface $query the query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.acceptedDate', ':byAcceptedDate'))
            ->setParameter('byAcceptedDate', (new \DateTime($query->getAcceptedDate()))->format('Y-m-d'));

        $qb->andWhere($qb->expr()->in($this->alias . '.trafficAreaId', ':byTrafficAreas'))
            ->setParameter('byTrafficAreas', $query->getTrafficAreas());

        if (!empty($query->getStatus())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.status', ':byStatus'))
                ->setParameter('byStatus', $query->getStatus());
        }
    }
}
