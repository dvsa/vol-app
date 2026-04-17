<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Mockery as m;
use DateTime;

/**
 * IRHP Permit Type test
 */
class IrhpPermitTypeTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpPermitType::class);
    }

    public function testFetchAvailableTypes(): void
    {
        $now = new DateTime('2018-10-25 13:21:10');

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchAvailableTypes($now));

        $expectedQuery = 'BLAH '
            . 'SELECT ipt, rd '
            . 'INNER JOIN ipt.name rd '
            . 'INNER JOIN ipt.irhpPermitStocks ips '
            . 'INNER JOIN ips.irhpPermitWindows ipw '
            . 'AND ipw.startDate <= [[2018-10-25T13:21:10+00:00]] '
            . 'AND ipw.endDate > [[2018-10-25T13:21:10+00:00]] '
            . 'AND ips.hiddenSs != 1 '
            . 'ORDER BY rd.displayOrder ASC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchReadyToPrint(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchReadyToPrint());

        $expectedQuery = 'BLAH '
            . 'SELECT ipt, rd DISTINCT '
            . 'INNER JOIN ipt.name rd '
            . 'INNER JOIN ipt.irhpPermitStocks ips '
            . 'INNER JOIN ips.irhpPermitRanges ipr '
            . 'INNER JOIN ipr.irhpPermits ip '
            . 'AND ip.status IN [[['
                . '"' . IrhpPermitEntity::STATUS_PENDING . '",'
                . '"' . IrhpPermitEntity::STATUS_AWAITING_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_ERROR . '"'
            . ']]] '
            . 'ORDER BY rd.description ASC';

        $this->assertEquals($expectedQuery, $this->query);
    }
}
