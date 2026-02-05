<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\DocumentSearchView as DocumentSearchViewRepo;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Dvsa\Olcs\Api\Entity\View\DocumentSearchView as DocumentSearchViewEntity;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\DocumentSearchView::class)]
class DocumentSearchViewTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(DocumentSearchViewRepo::class, true);
    }

    public function testFetchListWithoutFilters(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [];

        $query = DocumentList::create($data);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery')
            ->once();

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));

        $expected = '{QUERY}';

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchList(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [
            'isExternal' => 'Y',
            'category' => 11,
            'documentSubCategory' => 22,
            'licence' => 111,
            'transportManager' => 222,
            'case' => 333,
            'irfoOrganisation' => 444,
            'showDocs' => 'OTHER',
            'format' => 'FOO',
            'onlyUnlinked' => 'Y'
        ];

        $query = DocumentList::create($data);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery')
            ->once();

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));

        $expected = '{QUERY} AND m.identifier = [[' . DocumentSearchViewEntity::IDENTIFIER_UNLINKED . ']]' .
            ' AND m.isExternal = [[1]]' .
            ' AND m.category = 11' .
            ' AND m.documentSubCategory IN 22' .
            ' AND m.extension = [[FOO]]' .
            ' AND (m.licenceId = :licence OR m.tmId = :tm OR m.caseId = :case' .
            ' OR m.irfoOrganisationId = :irfoOrganisation)';

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchListWithShowSelfOnly(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [
            'case' => 'unit_CaseId',
            'irfoOrganisation' => 444,
            'showDocs' => FilterOptions::SHOW_SELF_ONLY,
            'application' => 'unit_AppId',
            'busReg' => 'unit_BusReg',
            'irhpApplication' => 'unit_IrhpApplication',
        ];

        $query = DocumentList::create($data);

        $this->sut
            ->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery')->once();

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));

        $expected = '{QUERY}' .
            ' AND m.applicationId = [[unit_AppId]]' .
            ' AND m.caseId = [[unit_CaseId]]' .
            ' AND m.busRegId = [[unit_BusReg]]' .
            ' AND m.irhpApplicationId = [[unit_IrhpApplication]]' .
            ' AND (' .
                'm.irfoOrganisationId = :irfoOrganisation' .
            ')';

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchListWithExcludeIrhp(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [
            'licence' => 'unit_LicenceId',
            'showDocs' => FilterOptions::EXCLUDE_IRHP,
        ];

        $query = DocumentList::create($data);

        $this->sut
            ->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery')->once();

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));

        $expected = '{QUERY}' .
            ' AND m.irhpApplicationId IS NULL' .
            ' AND (m.licenceId = :licence)';

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchDistinctListExtensions(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $mockQb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_ARRAY)->once()->andReturn(
            [
                ['extension' => 'DOC'],
                ['extension' => 'RTF'],
                ['extension' => 'DOCX'],
            ]
        );

        $this->mockCreateQueryBuilder($mockQb);

        $data = [
            'licence' => 111,
        ];
        $query = DocumentList::create($data);

        $this->sut->shouldReceive('buildDefaultListQuery')->once();

        $this->assertEquals(['DOC', 'RTF', 'DOCX'], $this->sut->fetchDistinctListExtensions($query));

        $expected = '{QUERY} SELECT DISTINCT m.extension AND (m.licenceId = :licence)';
        $this->assertEquals($expected, $this->query);
    }
}
