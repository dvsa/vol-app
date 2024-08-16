<?php

/**
 * With Case
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * With Case
 */
final readonly class WithCase implements QueryPartialInterface
{
    public function __construct(private With $with)
    {
    }

    /**
     * Joins on Case relationship
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        $alias = ((isset($arguments[0]) && isset($arguments[1])) ? $arguments[1] : $qb->getRootAliases()[0]);

        $this->with->modifyQuery($qb, [$alias . '.case', 'c']);
    }
}
