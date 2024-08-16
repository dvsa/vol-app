<?php

/**
 * ById
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\QueryBuilder;

/**
 * ById
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ById implements QueryPartialInterface
{
    /**
     * Adds a where id = XX clause
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        [$id] = $arguments;

        [$alias] = $qb->getRootAliases();

        $qb->andWhere($qb->expr()->eq($alias . '.id', ':byId'))
            ->setParameter('byId', $id);
    }
}
