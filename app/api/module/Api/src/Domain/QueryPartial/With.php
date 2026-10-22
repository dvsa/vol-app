<?php

/**
 * With
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Select;
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
    #[\Override]
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        $property = $arguments[0];
        $explicitAlias = isset($arguments[1]);
        $alias = $arguments[1] ?? 'w' . $this->i++;

        if (!str_contains((string) $property, '.')) {
            $property = $qb->getRootAliases()[0] . '.' . $property;
        }

        foreach ($qb->getDQLPart('join') as $joins) {
            foreach ($joins as $join) {
                if ($join->getJoin() !== $property) {
                    continue;
                }

                $existingAlias = $join->getAlias();

                if ($existingAlias === $alias || !$explicitAlias) {
                    return;
                }

                $this->replaceJoinAlias($qb, $existingAlias, $alias);

                return;
            }
        }

        $qb->leftJoin($property, $alias);
        $qb->addSelect($alias);
    }

    /**
     * Reuse an existing relationship with the requested alias and update
     * dependent joins and selected aliases.
     */
    private function replaceJoinAlias(QueryBuilder $qb, string $existingAlias, string $alias): void
    {
        $allJoins = $qb->getDQLPart('join');

        foreach ($allJoins as $rootAlias => $joins) {
            foreach ($joins as $key => $join) {
                $joinProperty = $join->getJoin();
                $joinAlias = $join->getAlias();

                if ($joinAlias === $existingAlias) {
                    $joinAlias = $alias;
                }

                if (str_starts_with($joinProperty, $existingAlias . '.')) {
                    $joinProperty = $alias . substr($joinProperty, strlen($existingAlias));
                }

                $allJoins[$rootAlias][$key] = new Join(
                    $join->getJoinType(),
                    $joinProperty,
                    $joinAlias,
                    $join->getConditionType(),
                    $join->getCondition(),
                    $join->getIndexBy(),
                );
            }
        }

        $qb->resetDQLPart('join');
        $qb->add('join', $allJoins);

        $selectParts = [];

        foreach ($qb->getDQLPart('select') as $select) {
            foreach ($select->getParts() as $part) {
                $selectParts[] = $part === $existingAlias ? $alias : $part;
            }
        }

        $qb->resetDQLPart('select');
        $qb->add('select', new Select($selectParts));
    }
}
