<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\WithApplication;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Mockery as m;

/**
 * WithApplicationTest
 */
final class WithApplicationTest extends QueryPartialTestCase
{
    public function setUp(): void
    {
        // Cannot mock With as it is Final
        $with = new With();
        $this->sut = new WithApplication($with);

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
        yield ['SELECT a, app FROM foo a LEFT JOIN a.application app', []];
        yield ['SELECT a, app FROM foo a LEFT JOIN a.application app', ['ENTITY']];
        yield ['SELECT a, app FROM foo a LEFT JOIN ALIAS.application app', ['ENTITY', 'ALIAS']];
    }
}
