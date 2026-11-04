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

        $existingAlias = $this->findReusableJoinAlias($qb, $property);

        if ($existingAlias === null) {
            $this->assertAliasIsFree($qb, $alias);

            $qb->leftJoin($property, $alias);
            $qb->addSelect($alias);

            return;
        }

        if ($explicitAlias && $existingAlias !== $alias) {
            $this->assertAliasIsFree($qb, $alias);
            $this->replaceJoinAlias($qb, $existingAlias, $alias);

            $existingAlias = $alias;
        }

        if (!$this->isSelected($qb, $existingAlias)) {
            $qb->addSelect($existingAlias);
        }
    }

    /**
     * Find a join we can reuse instead of adding our own. Only an unconditional left join
     * means the same thing as the one we would have added, so an inner join or one with an
     * ON/WITH condition is left alone.
     */
    private function findReusableJoinAlias(QueryBuilder $qb, string $property): ?string
    {
        foreach ($qb->getDQLPart('join') as $joins) {
            foreach ($joins as $join) {
                if (
                    $join->getJoin() === $property
                    && $join->getJoinType() === Join::LEFT_JOIN
                    && $join->getCondition() === null
                ) {
                    return $join->getAlias();
                }
            }
        }

        return null;
    }

    private function assertAliasIsFree(QueryBuilder $qb, string $alias): void
    {
        foreach ($qb->getDQLPart('join') as $joins) {
            foreach ($joins as $join) {
                if ($join->getAlias() === $alias) {
                    throw new \RuntimeException(
                        sprintf('Alias "%s" is already in use by the join on "%s"', $alias, $join->getJoin())
                    );
                }
            }
        }
    }

    private function isSelected(QueryBuilder $qb, string $alias): bool
    {
        foreach ($qb->getDQLPart('select') as $select) {
            if (in_array($alias, $select->getParts(), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reuse an existing relationship with the requested alias and update
     * dependent joins and selected aliases.
     */
    private function replaceJoinAlias(QueryBuilder $qb, string $existingAlias, string $alias): void
    {
        $pattern = '/(?<![\w.:])' . preg_quote($existingAlias, '/') . '\b/';
        $allJoins = $qb->getDQLPart('join');

        foreach ($allJoins as $rootAlias => $joins) {
            foreach ($joins as $key => $join) {
                $condition = $join->getCondition();

                $allJoins[$rootAlias][$key] = new Join(
                    $join->getJoinType(),
                    preg_replace($pattern, $alias, $join->getJoin()),
                    $join->getAlias() === $existingAlias ? $alias : $join->getAlias(),
                    $join->getConditionType(),
                    $condition === null ? null : preg_replace($pattern, $alias, (string) $condition),
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

        $this->assertOldAliasIsGone($qb, $pattern, $existingAlias, $alias);
    }

    /**
     * Only joins and plainly selected aliases get rewritten. Rewriting arbitrary
     * expressions by hand is asking for trouble, so if anything else still points at the
     * old alias, fail here rather than hand the database a query it cannot parse.
     */
    private function assertOldAliasIsGone(
        QueryBuilder $qb,
        string $pattern,
        string $existingAlias,
        string $alias
    ): void {
        foreach (['select', 'where', 'groupBy', 'having', 'orderBy'] as $name) {
            $part = $qb->getDQLPart($name);

            foreach (is_array($part) ? $part : [$part] as $expr) {
                if ($expr !== null && preg_match($pattern, (string) $expr)) {
                    throw new \RuntimeException(sprintf(
                        'Cannot re-alias the join on "%s" to "%s": the %s clause still refers to "%s"',
                        $existingAlias,
                        $alias,
                        $name,
                        $existingAlias
                    ));
                }
            }
        }
    }
}
