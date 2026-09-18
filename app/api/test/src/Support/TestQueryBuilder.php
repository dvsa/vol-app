<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Support;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery as m;
use Mockery\MockInterface;

/**
 * A real Doctrine QueryBuilder that cannot reach a database.
 *
 * Every builder method is Doctrine's own, so getDQL() is the query the repository actually
 * asked for and an invalid one throws where production would. Only getQuery() is replaced:
 * it hands back a mock Query, which is where a test declares the rows the repository should
 * receive. Repositories build and execute in a single call, so that seam has to be inside
 * the builder rather than around it.
 */
final class TestQueryBuilder extends QueryBuilder
{
    private Query|MockInterface|null $stubbedQuery = null;

    #[\Override]
    public function getQuery(): Query
    {
        return $this->stubbedQuery ??= m::mock(Query::class);
    }

    /**
     * The mock Query this builder returns, for setting result expectations on. Stable across
     * calls, so it can be primed before or after the code under test reaches getQuery().
     */
    public function stubbedQuery(): Query|MockInterface
    {
        return $this->getQuery();
    }

    /**
     * Shorthand for the overwhelmingly common case: this query returns these rows.
     */
    public function willReturn(mixed $result, string $method = 'getResult'): static
    {
        $this->stubbedQuery()->shouldReceive($method)->andReturn($result);

        return $this;
    }
}
