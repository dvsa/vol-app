<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as Repo;
use Dvsa\Olcs\Api\Entity\System\TranslationKey as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList;
use Mockery as m;

final class TranslationKeyTest extends RepositoryTestCase
{
    private const string SELECT = 'SELECT m FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The search matches the key, its description or any of its translations, so it is an OR
     * across a join rather than a filter on the root.
     */
    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(GetList::class);
        $query->shouldReceive('getTranslationSearch')->andReturn('searchText');

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            self::SELECT . ' LEFT JOIN m.translationKeyTexts tkt'
            . ' WHERE m.id LIKE :translationSearch'
            . ' OR m.description LIKE :translationSearch'
            . ' OR m.translationKey LIKE :translationSearch'
            . ' OR tkt.translatedText LIKE :translationSearch',
            $qb->getDQL(),
        );
        $this->assertSame('%searchText%', $qb->getParameter('translationSearch')->getValue());
    }

    /** No search term, or a query that cannot carry one, leaves the list query untouched. */
    #[\PHPUnit\Framework\Attributes\DataProvider('noSearchProvider')]
    public function testApplyListFiltersWithoutASearch(string $queryClass, bool $stubSearch): void
    {
        $qb = $this->createRealQb();

        $query = m::mock($queryClass);

        if ($stubSearch) {
            $query->shouldReceive('getTranslationSearch')->andReturnNull();
        }

        $this->assertNull($this->sut->applyListFilters($qb, $query));

        $this->assertSame(self::SELECT, $qb->getDQL());
    }

    public static function noSearchProvider(): \Iterator
    {
        yield 'an empty search' => [GetList::class, true];
        yield 'another query type' => [QueryInterface::class, false];
    }
}
