<?php

/**
 * With User
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * With User
 */
final readonly class WithUser implements QueryPartialInterface
{
    public function __construct(private With $with)
    {
    }

    /**
     * Joins on all refdata relationships
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        $alias = ((isset($arguments[0]) && isset($arguments[1])) ? $arguments[1] : $qb->getRootAliases()[0]);

        $this->with->modifyQuery($qb, [$alias . '.user', 'u']);
        $this->with->modifyQuery($qb, ['u.contactDetails', 'cd']);
        $this->with->modifyQuery($qb, ['cd.person', 'p']);
    }
}
