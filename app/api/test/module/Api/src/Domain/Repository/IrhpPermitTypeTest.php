<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as Repo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as Entity;

final class IrhpPermitTypeTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchAvailableTypes(): void
    {
        $now = new DateTime('2018-10-25 13:21:10');

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchAvailableTypes($now));

        $this->assertSame(
            'SELECT ipt, rd FROM ' . Entity::class . ' ipt'
            . ' INNER JOIN ipt.name rd INNER JOIN ipt.irhpPermitStocks ips'
            . ' INNER JOIN ips.irhpPermitWindows ipw'
            . ' WHERE ipw.startDate <= :now AND ipw.endDate > :now AND ips.hiddenSs <> 1'
            . ' ORDER BY rd.displayOrder ASC',
            $qb->getDQL(),
        );
        $this->assertSame($now, $qb->getParameter('now')->getValue());
    }

    public function testFetchReadyToPrint(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchReadyToPrint());

        $this->assertSame(
            'SELECT DISTINCT ipt, rd FROM ' . Entity::class . ' ipt'
            . ' INNER JOIN ipt.name rd INNER JOIN ipt.irhpPermitStocks ips'
            . ' INNER JOIN ips.irhpPermitRanges ipr INNER JOIN ipr.irhpPermits ip'
            . ' WHERE ip.status IN(:statuses)'
            . ' ORDER BY rd.description ASC',
            $qb->getDQL(),
        );
        $this->assertSame(IrhpPermitEntity::$readyToPrintStatuses, $qb->getParameter('statuses')->getValue());
    }
}
