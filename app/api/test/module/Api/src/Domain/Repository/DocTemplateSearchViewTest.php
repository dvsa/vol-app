<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\DocTemplateSearchView as DocTemplateSearchViewRepo;
use Dvsa\Olcs\Transfer\Query\DocTemplate\FullList as FullDocTemplateList;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\DocTemplateSearchView::class)]
class DocTemplateSearchViewTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(DocTemplateSearchViewRepo::class, true);
    }

    /**
     * @param $data
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('fetchListDataProvider')]
    public function testFetchList(mixed $data, mixed $expected): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $query = FullDocTemplateList::create($data);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery');

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));
        $this->assertEquals($expected, $this->query);
    }

    public static function fetchListDataProvider(): array
    {
        return [
            [[], '{QUERY}'],
            [['category' => 11], '{QUERY} AND m.category = 11']
        ];
    }
}
