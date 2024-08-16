<?php

/**
 * Filter By Licence
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial\Filter;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryPartial\QueryPartialInterface;

/**
 * Filter By Licence
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ByLicence implements QueryPartialInterface
{
    /**
     * Adds a where licence = XX clause only if id is specified
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        [$licenceId] = $arguments;

        if ($licenceId !== null) {
            [$alias] = $qb->getRootAliases();

            $qb->andWhere($qb->expr()->eq($alias . '.licence', ':licenceId'))
                ->setParameter('licenceId', $licenceId);
        }
    }
}
