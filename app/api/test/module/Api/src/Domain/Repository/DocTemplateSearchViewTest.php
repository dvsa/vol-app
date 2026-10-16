<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\DocTemplateSearchView as DocTemplateSearchViewRepo;
use Dvsa\Olcs\Api\Entity\View\DocTemplateSearchView as Entity;
use Dvsa\Olcs\Transfer\Query\DocTemplate\FullList as FullDocTemplateList;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\DocTemplateSearchView::class)]
final class DocTemplateSearchViewTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(DocTemplateSearchViewRepo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fetchListDataProvider')]
    public function testFetchList(mixed $data, string $expectedWhere): void
    {
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')
            ->with($qb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar']);
        $this->sut->shouldReceive('buildDefaultListQuery');

        $this->assertSame(['foo' => 'bar'], $this->sut->fetchList(FullDocTemplateList::create($data)));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m' . $expectedWhere,
            $qb->getDQL(),
        );
    }

    public static function fetchListDataProvider(): \Iterator
    {
        yield 'no category' => [[], ''];
        // The category is inlined rather than bound; that is what the repository does.
        yield 'with category' => [['category' => 11], ' WHERE m.category = 11'];
    }
}
