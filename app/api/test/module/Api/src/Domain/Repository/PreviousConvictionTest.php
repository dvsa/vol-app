<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\PreviousConviction as Repo;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class PreviousConvictionTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByTransportManager(): void
    {
        $qb = $this->createRealQb()->willReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByTransportManager(123));

        $this->assertSame(
            'SELECT pc, w0 FROM ' . Entity::class . ' pc LEFT JOIN pc.title w0'
            . ' WHERE pc.transportManager = :tmId',
            $qb->getDQL(),
        );
        $this->assertSame(123, $qb->getParameter('tmId')->getValue());
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getTransportManager')->with()->andReturn(33);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT pc FROM ' . Entity::class . ' pc WHERE pc.transportManager = :tmId',
            $qb->getDQL(),
        );
        $this->assertSame(33, $qb->getParameter('tmId')->getValue());
    }
}
