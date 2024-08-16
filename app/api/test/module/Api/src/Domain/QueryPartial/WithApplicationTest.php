<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\WithApplication;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Mockery as m;

/**
 * WithApplicationTest
 */
class WithApplicationTest extends QueryPartialTestCase
{
    /** @var m\Mock */
    private $with;

    public function setUp(): void
    {
        // Cannot mock With as it is Final
        $this->with = new With();
        $this->sut = new WithApplication($this->with);

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
            ['SELECT a, app FROM foo a LEFT JOIN a.application app', []],
            ['SELECT a, app FROM foo a LEFT JOIN a.application app', ['ENTITY']],
            ['SELECT a, app FROM foo a LEFT JOIN ALIAS.application app', ['ENTITY', 'ALIAS']],
        ];
    }
}
