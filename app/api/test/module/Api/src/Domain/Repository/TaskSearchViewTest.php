<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\TaskSearchView as TaskSearchViewRepo;
use Dvsa\Olcs\Transfer\Query\Task\TaskList;
use Dvsa\Olcs\Utils\Constants\FilterOptions;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\TaskSearchView::class)]
class TaskSearchViewTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(TaskSearchViewRepo::class, true);
    }

    public function testFetchList(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [
            'assignedToUser' => 11,
            'assignedToTeam' => 22,
            'category' => 1,
            'taskSubCategory' => 2,
            'date' => 'tdt_today',
            'status' => 'tst_closed',
            'urgent' => true,
            'messaging' => true,
            'licence' => 111,
            'transportManager' => 222,
            'case' => 333,
            'application' => 444,
            'busReg' => 555,
            'organisation' => 666,
            'showTasks' => 'OTHER',
        ];

        $query = TaskList::create($data);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery')
            ->once();

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));

        $expected = '{QUERY} AND m.assignedToUser = 11'
            . ' AND m.assignedToTeam = 22'
            . ' AND m.category = 1'
            . ' AND m.taskSubCategory = 2'
            . ' AND m.actionDate <= [[' . date('Y-m-d') . ']]'
            . ' AND m.isClosed = 1'
            . ' AND m.urgent = 1'
            . ' AND m.messaging = 1'
            . ' AND ('
            . 'm.licenceId = :licence'
            . ' OR m.transportManagerId = :tm'
                . ' OR m.caseId = :case'
                . ' OR m.applicationId = :application'
                . ' OR m.busRegId = :busReg'
                . ' OR m.irfoOrganisationId = :organisation'
            . ')';

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchListWithFlagShowTasks(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [
            'case' => 333,
            'application' => 444,
            'busReg' => 555,
            'organisation' => 666,
            'showTasks' => FilterOptions::SHOW_SELF_ONLY,
        ];

        $query = TaskList::create($data);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery')
            ->once();

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));

        $expected = '{QUERY}' .
            ' AND m.applicationId = [[444]]' .
            ' AND m.caseId = [[333]]' .
            ' AND m.busRegId = [[555]]' .
            ' AND (' .
                'm.irfoOrganisationId = :organisation' .
            ')';

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchListAlt(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [
            'assignedToUser' => 11,
            'assignedToTeam' => 22,
            'category' => 1,
            'taskSubCategory' => 2,
            'date' => 'tdt_today',
            'status' => 'tst_all',
            'urgent' => false,
            'messaging' => false,
            'licence' => 111,
            'application' => 444,
        ];

        $query = TaskList::create($data);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery')
            ->once();

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));

        $expected = '{QUERY} AND m.assignedToUser = 11'
            . ' AND m.assignedToTeam = 22'
            . ' AND m.category = 1'
            . ' AND m.taskSubCategory = 2'
            . ' AND m.actionDate <= [[' . date('Y-m-d') . ']]'
            . ' AND ('
            . 'm.licenceId = :licence'
            . ' OR m.applicationId = :application'
            . ')';

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchNoFilters(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [];

        $query = TaskList::create($data);

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
}
