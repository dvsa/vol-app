<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\Order;

/**
 * OrderTest
 */
final class OrderTest extends QueryPartialTestCase
{
    /**
     * @var Order
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Order();

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
        yield [
            'SELECT a FROM foo a ORDER BY a.PROP DESC',
            ['PROP', 'DESC']
        ];
        yield [
            'SELECT a FROM foo a ORDER BY ENTITY.PROP ASC',
            ['ENTITY.PROP', 'ASC']
        ];
        yield [
            'SELECT a FROM foo a ORDER BY PROP ASC',
            ['PROP', 'ASC', ['XXXX', 'PROP']]
        ];
        yield [
            'SELECT a FROM foo a ORDER BY ENTITY.PROP ASC',
            ['ENTITY.PROP', 'ASC', ['XXXX', 'ENTITY.PROP']]
        ];
    }
}
