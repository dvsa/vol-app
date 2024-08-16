<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\WithCase;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Mockery as m;

/**
 * WithCaseTest
 */
class WithCaseTest extends QueryPartialTestCase
{
    /** @var m\Mock */
    private $with;

    public function setUp(): void
    {
        // Cannot mock With as it is Final
        $this->with = new With();
        $this->sut = new WithCase($this->with);

        parent::setUp();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testModifyQuery($expectedDql, $arguments)
    {
        $this->sut->modifyQuery($this->qb, $arguments);
        $this->assertSame(
            $expectedDql,
            $this->qb->getDQL()
        );
    }

    public function dataProvider()
    {
        return [
            ['SELECT a, c FROM foo a LEFT JOIN a.case c', []],
            ['SELECT a, c FROM foo a LEFT JOIN a.case c', ['ENTITY']],
            ['SELECT a, c FROM foo a LEFT JOIN ALIAS.case c', ['ENTITY', 'ALIAS']],
        ];
    }
}
