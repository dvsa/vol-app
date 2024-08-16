<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\QueryBuilder;

final readonly class WithIrhpApplication implements QueryPartialInterface
{
    public function __construct(private With $with)
    {
    }

    /**
     * Modify query to bring back an associated irhp application
     *
     * @param QueryBuilder $qb
     * @param array        $arguments
     *
     * @return void
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = []): void
    {
        $alias = ((isset($arguments[0]) && isset($arguments[1])) ? $arguments[1] : $qb->getRootAliases()[0]);
        $this->with->modifyQuery($qb, [$alias . '.irhpApplication', 'ia']);
    }
}
