<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitJurisdiction;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitJurisdiction\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitJurisdictionQuota as PermitJurisdictionQuotaRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitJurisdictionQuota;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Query\TrafficArea\TrafficAreaList;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitJurisdiction\Create as CreateCmd;
use Mockery as m;

/**
 * Create IRHP Permit Jurisdiction Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('IrhpPermitJurisdictionQuota', PermitJurisdictionQuotaRepo::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);
        $this->mockRepo('TrafficArea', TrafficAreaRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $stockId = 999;
        $stockEntity = m::mock(IrhpPermitStock::class);
        $stockEntity->shouldReceive('getId')->once()->withNoArgs()->andReturn($stockId);

        $trafficArea1 = m::mock(TrafficArea::class);
        $trafficArea2 = m::mock(TrafficArea::class);
        $trafficAreaResults = [$trafficArea1, $trafficArea2];

        $command = CreateCmd::create(['id' => $stockId]);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($stockEntity);

        $this->repoMap['TrafficArea']
            ->shouldReceive('fetchDevolved')
            ->once()
            ->withNoArgs()
            ->andReturn($trafficAreaResults);

        $this->repoMap['IrhpPermitJurisdictionQuota']
            ->shouldReceive('save')
            ->times(count($trafficAreaResults))
            ->with(m::type(IrhpPermitJurisdictionQuota::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'Irhp Permit Stock' => $stockId
            ],
            'messages' => [
                'Irhp jurisdiction quotas created for stock ' . $stockId
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
