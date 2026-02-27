<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\MaxStockPermits;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermits as MaxStockPermitsQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class MaxStockPermitsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new MaxStockPermits();

        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $licenceId = 8;
        $totAuthVehicles = 12;

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getTotAuthVehicles')
            ->withNoArgs()
            ->andReturn($totAuthVehicles);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($licence);

        $livePermitCounts = [
            [
                'irhpPermitStockId' => 5,
                'irhpPermitCount' => 14
            ],
            [
                'irhpPermitStockId' => 6,
                'irhpPermitCount' => 7,
            ],
            [
                'irhpPermitStockId' => 7,
                'irhpPermitCount' => 0,
            ]
        ];

        $this->repoMap['IrhpPermit']->shouldReceive('getLivePermitCountsGroupedByStock')
            ->with($licenceId)
            ->andReturn($livePermitCounts);

        $irhpPermitStock1 = m::mock(IrhpPermitStock::class);
        $irhpPermitStock1->shouldReceive('getId')
            ->andReturn(5);

        $irhpPermitStock2 = m::mock(IrhpPermitStock::class);
        $irhpPermitStock2->shouldReceive('getId')
            ->andReturn(6);

        $irhpPermitStock3 = m::mock(IrhpPermitStock::class);
        $irhpPermitStock3->shouldReceive('getId')
            ->andReturn(7);

        $irhpPermitStock4 = m::mock(IrhpPermitStock::class);
        $irhpPermitStock4->shouldReceive('getId')
            ->andReturn(8);

        $irhpPermitStocks = [$irhpPermitStock1, $irhpPermitStock2, $irhpPermitStock3, $irhpPermitStock4];

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchAll')
            ->andReturn($irhpPermitStocks);

        $expectedResult = [
            'result' => [
                5 => 0,
                6 => 5,
                7 => 12,
                8 => 12,
            ]
        ];

        $result = $this->sut->handleQuery(MaxStockPermitsQry::create(['licence' => $licenceId]));
        $this->assertEquals($expectedResult, $result);
    }
}
