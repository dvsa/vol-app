<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\TaskAlphaSplit as TaskAlphaSplitRepo;

/**
 * Task Alpha SplitTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaskAlphaSplitTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(TaskAlphaSplitRepo::class);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(TaskAlphaSplitRepo::class, true);

        $qb = $this->createMockQb('[QUERY]');
        $query = m::mock(QueryInterface::class);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame('[QUERY]', $this->query);
    }

    public function testApplyListFiltersWithTaskAllocationRule(): void
    {
        $this->setUpSut(TaskAlphaSplitRepo::class, true);

        $qb = $this->createMockQb('[QUERY]');
        $query = \Dvsa\Olcs\Transfer\Query\TaskAlphaSplit\GetList::create(['taskAllocationRule' => 723]);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame('[QUERY] AND m.taskAllocationRule = [[723]]', $this->query);
    }
}
