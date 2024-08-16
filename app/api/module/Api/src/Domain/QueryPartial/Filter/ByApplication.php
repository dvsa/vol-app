<?php

/**
 * Filter By Application
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial\Filter;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryPartial\QueryPartialInterface;

/**
 * Filter By Application
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ByApplication implements QueryPartialInterface
{
    /**
     * Adds a where application = XX clause only if id is specified
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        [$applicationId] = $arguments;

        if ($applicationId !== null) {
            [$alias] = $qb->getRootAliases();

            $qb->andWhere($qb->expr()->eq($alias . '.application', ':applicationId'))
                ->setParameter('applicationId', $applicationId);
        }
    }
}
