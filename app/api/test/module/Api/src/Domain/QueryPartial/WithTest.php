<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Dvsa\Olcs\Api\Domain\QueryPartial\With;

/**
 * WithTest
 */
final class WithTest extends QueryPartialTestCase
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
            'SELECT a, w0 FROM foo a LEFT JOIN ENTITY.PROPERTY w0',
            ['ENTITY.PROPERTY']
        ];
        yield [
            'SELECT a, ALIAS FROM foo a LEFT JOIN ENTITY.PROPERTY ALIAS',
            ['ENTITY.PROPERTY', 'ALIAS']
        ];
        yield [
            'SELECT a, ALIAS FROM foo a LEFT JOIN a.PROPERTY ALIAS',
            ['PROPERTY', 'ALIAS']
        ];
    }

    public function testRepeatedJoinWithoutAliasIsSkipped(): void
    {
        $this->sut->modifyQuery($this->qb, ['a.status']);
        $this->sut->modifyQuery($this->qb, ['status']);

        $this->assertSame('SELECT a, w0 FROM foo a LEFT JOIN a.status w0', $this->qb->getDQL());
    }

    public function testExplicitAliasRenamesEarlierAutoAliasJoin(): void
    {
        $this->sut->modifyQuery($this->qb, ['a.status']);
        $this->sut->modifyQuery($this->qb, ['a.type']);
        $this->sut->modifyQuery($this->qb, ['a.status', 's']);
        $this->sut->modifyQuery($this->qb, ['s.parent']);

        $this->assertSame(
            'SELECT a, s, w1, w2 FROM foo a LEFT JOIN a.status s LEFT JOIN a.type w1 LEFT JOIN s.parent w2',
            $this->qb->getDQL()
        );
    }

    public function testSecondExplicitAliasStillAddsItsOwnJoin(): void
    {
        $this->sut->modifyQuery($this->qb, ['a.status', 's1']);
        $this->sut->modifyQuery($this->qb, ['a.status', 's1']);
        $this->sut->modifyQuery($this->qb, ['a.status', 's2']);

        $this->assertSame(
            'SELECT a, s1, s2 FROM foo a LEFT JOIN a.status s1 LEFT JOIN a.status s2',
            $this->qb->getDQL()
        );
    }

    public function testJoinAddedDirectlyOnTheQueryBuilderIsNotReused(): void
    {
        $this->qb->innerJoin('a.status', 's');
        $this->sut->modifyQuery($this->qb, ['a.status']);

        $this->assertSame('SELECT a, w0 FROM foo a INNER JOIN a.status s LEFT JOIN a.status w0', $this->qb->getDQL());
    }
}
