<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\WithBusReg;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Mockery as m;

/**
 * WithBusRegTest
 */
class WithBusRegTest extends QueryPartialTestCase
{
    /** @var m\Mock */
    private $with;

    public function setUp(): void
    {
        // Cannot mock With as it is Final
        $this->with = new With();
        $this->sut = new WithBusReg($this->with);

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

    public static function dataProvider(): array
    {
        return [
            ['SELECT a, br FROM foo a LEFT JOIN a.busReg br', []],
            ['SELECT a, br FROM foo a LEFT JOIN a.busReg br', ['ENTITY']],
            ['SELECT a, br FROM foo a LEFT JOIN ALIAS.busReg br', ['ENTITY', 'ALIAS']],
        ];
    }
}
