<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\TaskSearchView as TaskSearchViewRepo;
use Dvsa\Olcs\Api\Entity\View\TaskSearchView as Entity;
use Dvsa\Olcs\Transfer\Query\Task\TaskList;
use Dvsa\Olcs\Utils\Constants\FilterOptions;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\TaskSearchView::class)]
final class TaskSearchViewTest extends RepositoryTestCase
{
    private const string FROM = 'SELECT m FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(TaskSearchViewRepo::class, true);
    }

    /**
     * The entity-id filters are collected into a single orX, so a task matching any one of the
     * linked records is returned. Everything before that is ANDed.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('fetchListProvider')]
    public function testFetchList(array $data, string $expectedWhere): void
    {
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')
            ->with($qb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar']);
        $this->sut->shouldReceive('buildDefaultListQuery');

        $this->assertSame(['foo' => 'bar'], $this->sut->fetchList(TaskList::create($data)));

        $this->assertSame(self::FROM . $expectedWhere, $qb->getDQL());
    }

    public static function fetchListProvider(): \Iterator
    {
        $today = date('Y-m-d');

        yield 'no filters' => [[], ''];

        yield 'every filter, showing related tasks too' => [
            [
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
            ],
            // The scalar filters are inlined rather than bound; only the id alternation binds.
            ' WHERE m.assignedToUser = 11 AND m.assignedToTeam = 22 AND m.category = 1'
            . ' AND m.taskSubCategory = 2 AND m.actionDate <= :actionDate AND m.isClosed = 1'
            . ' AND m.urgent = 1 AND m.messaging = 1'
            . ' AND (m.licenceId = :licence OR m.transportManagerId = :tm OR m.caseId = :case'
            . ' OR m.applicationId = :application OR m.busRegId = :busReg'
            . ' OR m.irfoOrganisationId = :organisation)',
        ];

        // SHOW_SELF_ONLY moves case/application/busReg out of the OR group and ANDs them.
        yield 'self only' => [
            [
                'case' => 333,
                'application' => 444,
                'busReg' => 555,
                'organisation' => 666,
                'showTasks' => FilterOptions::SHOW_SELF_ONLY,
            ],
            // A single-element orX renders without brackets.
            ' WHERE m.applicationId = :APP_ID AND m.caseId = :CASE_ID AND m.busRegId = :BUS_REG_ID'
            . ' AND m.irfoOrganisationId = :organisation',
        ];

        // 'tst_all' and falsy urgent/messaging drop their clauses entirely.
        yield 'all statuses, not urgent' => [
            [
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
            ],
            ' WHERE m.assignedToUser = 11 AND m.assignedToTeam = 22 AND m.category = 1'
            . ' AND m.taskSubCategory = 2 AND m.actionDate <= :actionDate'
            . ' AND (m.licenceId = :licence OR m.applicationId = :application)',
        ];
    }

    public function testFetchListBindsTheActionDateAsToday(): void
    {
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')->andReturn([]);
        $this->sut->shouldReceive('buildDefaultListQuery');

        $this->sut->fetchList(TaskList::create(['date' => 'tdt_today']));

        $this->assertSame(date('Y-m-d'), $qb->getParameter('actionDate')->getValue());
    }
}
