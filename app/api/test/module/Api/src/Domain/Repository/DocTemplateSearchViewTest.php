<?php

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\DocTemplateSearchView as DocTemplateSearchViewRepo;
use Dvsa\Olcs\Transfer\Query\DocTemplate\FullList as FullDocTemplateList;

/**
 * @covers \Dvsa\Olcs\Api\Domain\Repository\DocTemplateSearchView
 */
class DocTemplateSearchViewTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(DocTemplateSearchViewRepo::class, true);
    }

    /**
     * @dataProvider fetchListDataProvider
     * @param $data
     * @param $expected
     */
    public function testFetchList($data, $expected)
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

    public function fetchListDataProvider()
    {
        return [
            [[], '{QUERY}'],
            [['category' => 11], '{QUERY} AND m.category = 11']
        ];
    }
}
