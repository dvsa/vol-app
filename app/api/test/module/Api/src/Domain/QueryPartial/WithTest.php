<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\With;

/**
 * WithTest
 */
class WithTest extends QueryPartialTestCase
{
    /**
     * @var With
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new With();

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
            [
                'SELECT a, w0 FROM foo a LEFT JOIN ENTITY.PROPERTY w0',
                ['ENTITY.PROPERTY']
            ],
            [
                'SELECT a, ALIAS FROM foo a LEFT JOIN ENTITY.PROPERTY ALIAS',
                ['ENTITY.PROPERTY', 'ALIAS']
            ],
            [
                'SELECT a, ALIAS FROM foo a LEFT JOIN a.PROPERTY ALIAS',
                ['PROPERTY', 'ALIAS']
            ],
        ];
    }
}
