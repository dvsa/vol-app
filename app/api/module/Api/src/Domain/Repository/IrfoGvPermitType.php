<?php

/**
 * IrfoGvPermitType repo
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermitType as Entity;
use Dvsa\Olcs\Api\Domain\Exception;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IrfoGvPermitType repo
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IrfoGvPermitType extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->orderBy($this->alias . '.description', 'ASC');
    }

    public function fetchActiveRecords()
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->where($doctrineQb->expr()->orX(
            $doctrineQb->expr()->isNull($this->alias . '.displayUntil'),
            $doctrineQb->expr()->gte($this->alias . '.displayUntil', ':today')
        ))
            ->setParameter('today', new \DateTime(), \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);

        $doctrineQb->orderBy($this->alias . '.description', 'ASC');

        return $doctrineQb->getQuery()->getResult();
    }
}
