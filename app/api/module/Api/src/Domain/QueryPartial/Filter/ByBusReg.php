<?php

/**
 * Filter By Bus Reg
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial\Filter;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryPartial\QueryPartialInterface;

/**
 * Filter By Bus Reg
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ByBusReg implements QueryPartialInterface
{
    /**
     * Adds a where busReg = XX clause only if id is specified
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        [$busRegId] = $arguments;

        if ($busRegId !== null) {
            [$alias] = $qb->getRootAliases();

            $qb->andWhere($qb->expr()->eq($alias . '.busReg', ':busRegId'))
                ->setParameter('busRegId', $busRegId);
        }
    }
}
