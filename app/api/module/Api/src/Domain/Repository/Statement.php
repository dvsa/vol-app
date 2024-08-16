<?php

/**
 * Statement
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Cases\Statement as Entity;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Statement
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Statement extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Apply List Filters
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     * @return QueryBuilder|void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());

        return $qb;
    }

    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('requestorsContactDetails', 'rc')
            ->with('rc.person');
    }
}
