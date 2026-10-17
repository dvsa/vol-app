<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\LongText as LongTextRepo;
use Dvsa\Olcs\Transfer\Query\LongText\GetList;
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

        $this->expectLookup('application-declaration-gv79-gb', ['cy_NI'], $entity);

        self::assertSame(
            $entity,
            $this->sut->fetchByReferenceKey('application-declaration-gv79-gb', 'cy_NI'),
        );
    }

    public function testItFallsBackFromNiToGbAndThenToEnglish(): void
    {
        $entity = m::mock(LongTextEntity::class);

        $this->expectLookup('application-declaration-gv79-gb', ['cy_NI', 'cy_GB', 'en_GB'], $entity);

        self::assertSame(
            $entity,
            $this->sut->fetchByReferenceKey('application-declaration-gv79-gb', 'cy_NI'),
        );
    }

    /**
     * @param list<string> $expectedLocales locales tried in order; the last one matches
     */
    private function expectLookup(string $referenceKey, array $expectedLocales, ?LongTextEntity $found): void
    {
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr')->times(count($expectedLocales) * 2)->andReturn(new Expr());
        $qb->shouldReceive('andWhere')
            ->times(count($expectedLocales))
            ->with(m::on(
                static fn (mixed $condition): bool => $condition instanceof Comparison
                    && (string) $condition === 'm.referenceKey = :referenceKey'
            ))
            ->andReturnSelf();
        $qb->shouldReceive('andWhere')
            ->times(count($expectedLocales))
            ->with(m::on(
                static fn (mixed $condition): bool => $condition instanceof Comparison
                    && (string) $condition === 'm.locale = :locale'
            ))
            ->andReturnSelf();
        $qb->shouldReceive('setParameter')
            ->times(count($expectedLocales))
            ->with('referenceKey', $referenceKey)
            ->andReturnSelf();

        foreach ($expectedLocales as $locale) {
            $qb->shouldReceive('setParameter')
                ->once()
                ->with('locale', $locale)
                ->ordered()
                ->andReturnSelf();
        }

        $results = array_fill(0, count($expectedLocales) - 1, null);
        $results[] = $found;

        $qb->shouldReceive('getQuery->getOneOrNullResult')
            ->times(count($expectedLocales))
            ->andReturn(...$results);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->andReturn($qb);
    }

    public function testFetchByReferenceKeyThrowsWhenTheContentIsMissing(): void
    {
        $this->expectLookup('does-not-exist', ['en_GB'], null);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchByReferenceKey('does-not-exist', 'en_GB');
    }

    public function testTheListCanBeSearchedByUidAndPageName(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr')->once()->andReturn(new Expr());
        $qb->shouldReceive('andWhere')
            ->once()
            ->with(m::on(
                static fn (mixed $condition): bool => $condition instanceof Orx
                    && $condition->getParts() === [
                        'm.referenceKey LIKE :search',
                        'm.pageName LIKE :search',
                        'm.description LIKE :search',
                    ]
            ))
            ->andReturnSelf();
        $qb->shouldReceive('setParameter')->once()->with('search', '%gv79%')->andReturnSelf();

        $this->applyListFilters($qb, GetList::create(['search' => 'gv79']));
    }

    public function testTheListCanBeNarrowedToOneLocale(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('andWhere')->once()->with('m.locale = :locale')->andReturnSelf();
        $qb->shouldReceive('setParameter')->once()->with('locale', 'cy_GB')->andReturnSelf();

        $this->applyListFilters($qb, GetList::create(['locale' => 'cy_GB']));
    }

    private function applyListFilters(QueryBuilder $qb, GetList $query): void
    {
        $method = new \ReflectionMethod($this->sut, 'applyListFilters');
        $method->invoke($this->sut, $qb, $query);
    }
}
