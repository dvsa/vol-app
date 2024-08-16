<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit as Entity;

/**
 * TaskAlphaSplit
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaskAlphaSplit extends AbstractRepository
{
    protected $entity = Entity::class;

    protected function applyListFilters(\Doctrine\ORM\QueryBuilder $qb, \Dvsa\Olcs\Transfer\Query\QueryInterface $query)
    {
        if (method_exists($query, 'getTaskAllocationRule')) {
            $qb->andWhere($qb->expr()->eq('m.taskAllocationRule', ':taskAllocationRule'))
                ->setParameter('taskAllocationRule', $query->getTaskAllocationRule());
        }
    }
}
