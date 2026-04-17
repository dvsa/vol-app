<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as Repo;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList;

/**
 * TranslationKeyTest
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class TranslationKeyTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $query = m::mock(GetList::class);
        $query->shouldReceive('getTranslationSearch')
            ->andReturn('searchText')
            ->times(2);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('orWhere')
            ->with('m.id LIKE :translationSearch')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('orWhere')
            ->with('m.description LIKE :translationSearch')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('orWhere')
            ->with('m.translationKey LIKE :translationSearch')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('leftJoin')
            ->with('m.translationKeyTexts', 'tkt')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('orWhere')
            ->with('tkt.translatedText LIKE :translationSearch')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('translationSearch', '%searchText%')
            ->once()
            ->andReturnSelf();

        $this->sut->applyListFilters($qb, $query);
    }

    public function testApplyListFiltersNullSearch(): void
    {
        $this->setUpSut(Repo::class, true);

        $query = m::mock(GetList::class);
        $query->shouldReceive('getTranslationSearch')
            ->andReturnNull()
            ->once();

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->sut->applyListFilters($qb, $query);
    }

    public function testApplyListFiltersNotGetList(): void
    {
        $this->setUpSut(Repo::class, true);

        $query = m::mock(QueryInterface::class);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $this->assertNull($this->sut->applyListFilters($qb, $query));
    }
}
