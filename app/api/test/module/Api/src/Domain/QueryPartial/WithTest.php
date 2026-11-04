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

    public function testDoesNotJoinSamePropertyTwice(): void
    {
        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'p']);
        $this->sut->modifyQuery($this->qb, ['PROPERTY']);

        $this->assertSame(
            'SELECT a, p FROM foo a LEFT JOIN a.PROPERTY p',
            $this->qb->getDQL()
        );
    }

    public function testExplicitAliasReplacesGeneratedAlias(): void
    {
        $this->sut->modifyQuery($this->qb, ['PROPERTY']);
        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'p']);

        $this->assertSame(
            'SELECT a, p FROM foo a LEFT JOIN a.PROPERTY p',
            $this->qb->getDQL()
        );
    }

    public function testExplicitAliasUpdatesDependentJoin(): void
    {
        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'old']);
        $this->sut->modifyQuery($this->qb, ['old.CHILD', 'child']);

        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'new']);

        $this->assertSame(
            'SELECT a, new, child FROM foo a'
            . ' LEFT JOIN a.PROPERTY new'
            . ' LEFT JOIN new.CHILD child',
            $this->qb->getDQL()
        );
    }

    public function testExplicitAliasUpdatesDependentJoinCondition(): void
    {
        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'old']);
        $this->qb->leftJoin('a.OTHER', 'oth', 'WITH', 'oth.x = old.y');

        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'new']);

        $this->assertSame(
            'SELECT a, new FROM foo a LEFT JOIN a.PROPERTY new LEFT JOIN a.OTHER oth WITH oth.x = new.y',
            $this->qb->getDQL()
        );
    }

    public function testExplicitAliasRejectedWhenTheOldAliasIsUsedElsewhere(): void
    {
        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'old']);
        $this->qb->where('old.id = :x');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot re-alias the join on "old" to "new": the where clause still refers');

        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'new']);
    }

    public function testDoesNotReuseAnInnerJoin(): void
    {
        $this->qb->innerJoin('a.PROPERTY', 'i');

        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'p']);

        $this->assertSame(
            'SELECT a, p FROM foo a INNER JOIN a.PROPERTY i LEFT JOIN a.PROPERTY p',
            $this->qb->getDQL()
        );
    }

    public function testDoesNotReuseAJoinWithACondition(): void
    {
        $this->qb->leftJoin('a.PROPERTY', 'f', 'WITH', 'f.status = :st');

        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'p']);

        $this->assertSame(
            'SELECT a, p FROM foo a LEFT JOIN a.PROPERTY f WITH f.status = :st LEFT JOIN a.PROPERTY p',
            $this->qb->getDQL()
        );
    }

    public function testSelectsAnExistingJoinThatWasNotSelected(): void
    {
        $this->qb->leftJoin('a.PROPERTY', 'p');

        $this->sut->modifyQuery($this->qb, ['PROPERTY']);

        $this->assertSame(
            'SELECT a, p FROM foo a LEFT JOIN a.PROPERTY p',
            $this->qb->getDQL()
        );
    }

    public function testRejectsAnAliasAlreadyUsedByAnotherJoin(): void
    {
        $this->sut->modifyQuery($this->qb, ['OTHER', 'dup']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Alias "dup" is already in use by the join on "a.OTHER"');

        $this->sut->modifyQuery($this->qb, ['PROPERTY', 'dup']);
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
}
