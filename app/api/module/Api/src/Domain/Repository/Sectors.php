<?php

/**
 * Sectors
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Util\QueryHelper;
use Dvsa\Olcs\Api\Entity\Permits\Sectors as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Sectors
 */
class Sectors extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    #[\Override]
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $order = QueryHelper::toSortDirection($query->getOrder());
        $qb->addOrderBy($this->alias . '.' . $query->getSort(), $order);
    }

    /**
     * Fetch an array of sectors for use by the Q&A options component
     *
     * @return array
     */
    public function fetchQaOptions()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('s.id as value, s.nameKey as label, s.descriptionKey as hint')
            ->from(Entity::class, 's')
            ->orderBy('s.displayOrder', \SortDirection::Ascending)
            ->getQuery()
            ->getScalarResult();
    }
}
