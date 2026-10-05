<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\TmQualification as TmQualificationRepo;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class TmQualificationTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(TmQualificationRepo::class, true);
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->expects('getTransportManager')->andReturn(1);

        $this->sut->applyListFilters($qb, $mockQuery);

        $this->assertSame(
            'SELECT tq FROM ' . Entity::class . ' tq WHERE tq.transportManager = :transportManager',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('transportManager')->getValue());
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $previous = $this->newRealQb();
        $previous->select('other')->from(Entity::class, 'other');
        $this->queryBuilder->modifyQuery($previous);

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT tq, cc, qt FROM ' . Entity::class . ' tq'
            . ' LEFT JOIN tq.countryCode cc LEFT JOIN tq.qualificationType qt'
            . ' ORDER BY qt.displayOrder ASC',
            $qb->getDQL(),
        );
        $this->assertSame('SELECT other FROM ' . Entity::class . ' other', $previous->getDQL());
    }
}
