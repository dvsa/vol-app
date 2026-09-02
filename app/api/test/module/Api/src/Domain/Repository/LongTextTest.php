<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\LongText as LongTextRepo;
use Dvsa\Olcs\Api\Entity\System\LongText as LongTextEntity;
use Mockery as m;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LongTextRepo::class)]
final class LongTextTest extends RepositoryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(LongTextRepo::class);
    }

    public function testFetchByReferenceKeyReturnsTheRecordForTheRequestedLocale(): void
    {
        $entity = m::mock(LongTextEntity::class);

        $this->expectLookup(['cy_NI'], $entity);

        self::assertSame(
            $entity,
            $this->sut->fetchByReferenceKey('application-declaration-gv79-gb', 'cy_NI'),
        );
    }

    public function testItFallsBackFromNiToGbAndThenToEnglish(): void
    {
        $entity = m::mock(LongTextEntity::class);

        $this->expectLookup(['cy_NI', 'cy_GB', 'en_GB'], $entity);

        self::assertSame(
            $entity,
            $this->sut->fetchByReferenceKey('application-declaration-gv79-gb', 'cy_NI'),
        );
    }

    /**
     * @param list<string> $expectedLocales locales tried in order; the last one matches
     */
    private function expectLookup(array $expectedLocales, ?object $found): void
    {
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->andReturn('CONDITION');
        $qb->shouldReceive('andWhere')->andReturnSelf();

        $seen = [];
        $qb->shouldReceive('setParameter')->andReturnUsing(
            function (string $name, $value) use ($qb, &$seen) {
                if ($name === 'locale') {
                    $seen[] = $value;
                }

                return $qb;
            },
        );

        $results = array_fill(0, count($expectedLocales) - 1, null);
        $results[] = $found;

        $qb->shouldReceive('getQuery->getOneOrNullResult')
            ->times(count($expectedLocales))
            ->andReturn(...$results);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->andReturn($qb);

        $this->expectedLocales = $expectedLocales;
        $this->seenLocales = &$seen;
    }

    private array $expectedLocales = [];

    private array $seenLocales = [];

    protected function tearDown(): void
    {
        if ($this->expectedLocales !== []) {
            self::assertSame($this->expectedLocales, $this->seenLocales, 'locale fallback order');
        }

        parent::tearDown();
    }

    public function testFetchByReferenceKeyThrowsWhenTheContentIsMissing(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->andReturn('CONDITION');
        $qb->shouldReceive('andWhere')->andReturnSelf();
        $qb->shouldReceive('setParameter')->andReturnSelf();
        $qb->shouldReceive('getQuery->getOneOrNullResult')->andReturn(null);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->andReturn($qb);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchByReferenceKey('does-not-exist', 'en_GB');
    }
}
