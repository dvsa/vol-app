<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\WithIrhpApplication;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;

class WithIrhpApplicationTest extends QueryPartialTestCase
{
    public function setUp(): void
    {
        // Cannot mock With as it is Final
        $with = new With();
        $this->sut = new WithIrhpApplication($with);

        parent::setUp();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testModifyQuery($expectedDql, $arguments): void
    {
        $this->sut->modifyQuery($this->qb, $arguments);
        $this->assertSame(
            $expectedDql,
            $this->qb->getDQL()
        );
    }

    public function dataProvider(): array
    {
        return [
            ['SELECT a, ia FROM foo a LEFT JOIN a.irhpApplication ia', []],
            ['SELECT a, ia FROM foo a LEFT JOIN a.irhpApplication ia', ['ENTITY']],
            ['SELECT a, ia FROM foo a LEFT JOIN ALIAS.irhpApplication ia', ['ENTITY', 'ALIAS']],
        ];
    }
}
