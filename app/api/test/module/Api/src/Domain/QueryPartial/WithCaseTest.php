<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\WithCase;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Mockery as m;

/**
 * WithCaseTest
 */
final class WithCaseTest extends QueryPartialTestCase
{
    public function setUp(): void
    {
        // Cannot mock With as it is Final
        $with = new With();
        $this->sut = new WithCase($with);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testModifyQuery(mixed $expectedDql, mixed $arguments): void
    {
        $this->sut->modifyQuery($this->qb, $arguments);
        $this->assertSame(
            $expectedDql,
            $this->qb->getDQL()
        );
    }

    public static function dataProvider(): \Iterator
    {
        yield ['SELECT a, c FROM foo a LEFT JOIN a.case c', []];
        yield ['SELECT a, c FROM foo a LEFT JOIN a.case c', ['ENTITY']];
        yield ['SELECT a, c FROM foo a LEFT JOIN ALIAS.case c', ['ENTITY', 'ALIAS']];
    }
}
