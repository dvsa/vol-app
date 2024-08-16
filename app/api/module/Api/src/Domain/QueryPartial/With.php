<?php

/**
 * With
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\QueryBuilder;

/**
 * With
 */
final class With implements QueryPartialInterface
{
    private $i = 0;

    /**
     * Adds a left join on XX clause
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        $property = $arguments[0];
        $alias = ($arguments[1] ?? 'w' . $this->i++);

        if (!str_contains((string) $property, '.')) {
            $property = $qb->getRootAliases()[0] . '.' . $property;
        }

        $qb->leftJoin($property, $alias);
        $qb->addSelect($alias);
    }
}
