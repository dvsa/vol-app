<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPermitStock as Repo;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class IrfoPermitStockTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The result is indexed by serial number, so a caller can address a single permit in the range
     * without scanning.
     */
    public function testFetchUsingSerialNoStartEnd(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['result']);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getIrfoCountry')->andReturn(99);
        $query->shouldReceive('getValidForYear')->andReturn(2015);
        $query->shouldReceive('getSerialNoStart')->andReturn(1);
        $query->shouldReceive('getSerialNoEnd')->andReturn(2);

        $this->assertSame(['result'], $this->sut->fetchUsingSerialNoStartEnd($query));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m INDEX BY m.serialNo'
            . ' WHERE m.irfoCountry = :byIrfoCountry AND m.validForYear = :byValidForYear'
            . ' AND m.serialNo >= :bySerialNoStart AND m.serialNo <= :bySerialNoEnd',
            $qb->getDQL(),
        );
        $this->assertSame(99, $qb->getParameter('byIrfoCountry')->getValue());
        $this->assertSame(2015, $qb->getParameter('byValidForYear')->getValue());
        $this->assertSame(1, $qb->getParameter('bySerialNoStart')->getValue());
        $this->assertSame(2, $qb->getParameter('bySerialNoEnd')->getValue());
    }
}
