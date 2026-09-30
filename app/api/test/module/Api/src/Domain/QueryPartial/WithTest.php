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
