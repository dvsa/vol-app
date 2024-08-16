<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup as Entity;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationPathGroupList;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Application Path Group
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class ApplicationPathGroup extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Apply List Filters
     *
     * @param QueryBuilder   $qb    Doctrine Query Builder
     * @param QueryInterface $query Http Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query instanceof ApplicationPathGroupList) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.isVisibleInInternal', ':isVisibleInInternal'))
                ->setParameter('isVisibleInInternal', true);
        }
    }
}
