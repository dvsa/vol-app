<?php

/**
 * With
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * With
 */
final class With implements QueryPartialInterface
{
    private $i = 0;

    /** joins added by this class, per query builder, keyed by property path */
    private ?\WeakMap $joins = null;

    /**
     * Adds a left join on XX clause
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    #[\Override]
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        $property = $arguments[0];
        $alias = $arguments[1] ?? null;

        if (!str_contains((string) $property, '.')) {
            $property = $qb->getRootAliases()[0] . '.' . $property;
        }

        $this->joins ??= new \WeakMap();
        $joins = $this->joins[$qb] ?? [];
        $existing = $joins[$property] ?? null;

        // don't join the same path twice. Nothing can refer to an auto alias by name, so one
        // can be renamed in place when a later call asks for its own alias
        if ($existing !== null) {
            if ($alias === null || $alias === $existing['alias']) {
                return;
            }

            if ($existing['auto'] && $this->rename($qb, $property, $existing['alias'], $alias)) {
                $joins[$property] = ['alias' => $alias, 'auto' => false];
                $this->joins[$qb] = $joins;
                return;
            }
        }

        $auto = $alias === null;
        $alias ??= 'w' . $this->i++;

        $qb->leftJoin($property, $alias);
        $qb->addSelect($alias);

        $joins[$property] = ['alias' => $alias, 'auto' => $auto];
        $this->joins[$qb] = $joins;
    }

    private function rename(QueryBuilder $qb, string $property, string $from, string $to): bool
    {
        $joins = $qb->getDQLPart('join');
        $selects = [];
        $found = false;

        foreach ($joins as $root => $rootJoins) {
            foreach ($rootJoins as $key => $join) {
                if ($join->getJoin() === $property && $join->getAlias() === $from) {
                    $joins[$root][$key] = new Join(Join::LEFT_JOIN, $property, $to);
                    $found = true;
                }
            }
        }

        foreach ($qb->getDQLPart('select') as $select) {
            $selects = array_merge($selects, $select->getParts());
        }

        $key = array_search($from, $selects, true);

        if (!$found || $key === false) {
            return false;
        }

        $selects[$key] = $to;
        $qb->add('join', $joins);
        $qb->select($selects);

        return true;
    }
}
