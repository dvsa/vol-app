<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\TaskAlphaSplit as TaskAlphaSplitRepo;
use Dvsa\Olcs\Api\Entity\Task\TaskAlphaSplit as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\TaskAlphaSplit\GetList;
use Mockery as m;

final class TaskAlphaSplitTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(TaskAlphaSplitRepo::class, true);
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        // A bare QueryInterface has no getTaskAllocationRule(), so the filter is skipped.
        $this->sut->applyListFilters($qb, m::mock(QueryInterface::class));

        $this->assertSame('SELECT m FROM ' . Entity::class . ' m', $qb->getDQL());
    }

    public function testApplyListFiltersWithTaskAllocationRule(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, GetList::create(['taskAllocationRule' => 723]));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.taskAllocationRule = :taskAllocationRule',
            $qb->getDQL(),
        );
        $this->assertSame(723, $qb->getParameter('taskAllocationRule')->getValue());
    }
}
