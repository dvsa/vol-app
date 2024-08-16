<?php

/**
 * Paginate
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\QueryBuilder;

/**
 * Paginate
 */
final class Order implements QueryPartialInterface
{
    /**
     * Adds a where id = XX clause
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        // if we do not pass compositeFields array it should be defined anyway
        if (count($arguments) == 2) {
            $arguments[] = [];
        }
        [$sort, $order, $compositeFields] = $arguments;

        [$alias] = $qb->getRootAliases();

        if (str_contains((string) $sort, '.') || in_array($sort, $compositeFields)) {
            $qb->addOrderBy($sort, $order);
        } else {
            $qb->addOrderBy($alias . '.' . $sort, $order);
        }
    }
}
