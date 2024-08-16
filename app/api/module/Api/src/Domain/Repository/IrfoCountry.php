<?php

/**
 * IrfoCountry
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry as Entity;
use Dvsa\Olcs\Api\Domain\Exception;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IrfoCountry
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class IrfoCountry extends AbstractRepository
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
}
