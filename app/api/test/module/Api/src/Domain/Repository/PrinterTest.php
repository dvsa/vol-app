<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Printer as Repo;
use Dvsa\Olcs\Api\Entity\PrintScan\Printer as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class PrinterTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchWithTeams(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->withNoArgs()->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchWithTeams(1));

        $this->assertSame(
            'SELECT m, w0' . self::FROM . ' LEFT JOIN m.teamPrinters w0 WHERE m.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('byId')->getValue());
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $this->assertNull($this->sut->applyListFilters($qb, m::mock(QueryInterface::class)));

        $this->assertSame(
            'SELECT m' . self::FROM . ' ORDER BY m.printerName ASC',
            $qb->getDQL(),
        );
    }
}
