<?php

/**
 * ByIds
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial\Filter;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryPartial\QueryPartialInterface;

/**
 * ByIds
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ByIds implements QueryPartialInterface
{
    /**
     * Adds a where ids IN [XX,YY] clause only if ids are specified
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        [$ids] = $arguments;

        if ($ids !== null) {
            [$alias] = $qb->getRootAliases();

            $qb->andWhere($qb->expr()->in($alias . '.id', ':byIds'))
                ->setParameter('byIds', $ids);
        }
    }
}
