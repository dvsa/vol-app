<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\WithBusReg;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Mockery as m;

/**
 * WithBusRegTest
 */
final class WithBusRegTest extends QueryPartialTestCase
{
    public function setUp(): void
    {
        // Cannot mock With as it is Final
        $with = new With();
        $this->sut = new WithBusReg($with);

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
        yield ['SELECT a, br FROM foo a LEFT JOIN a.busReg br', []];
        yield ['SELECT a, br FROM foo a LEFT JOIN a.busReg br', ['ENTITY']];
        yield ['SELECT a, br FROM foo a LEFT JOIN ALIAS.busReg br', ['ENTITY', 'ALIAS']];
    }
}
