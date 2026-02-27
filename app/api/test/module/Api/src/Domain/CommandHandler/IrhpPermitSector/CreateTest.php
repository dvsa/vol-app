<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitSector;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitSector\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitSectorQuota as PermitSectorQuotaRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\Sectors as SectorsRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\Sectors as SectorsEntity;
use Dvsa\Olcs\Transfer\Query\Permits\Sectors;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitSector\Create as CreateCmd;
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
        $this->mockRepo('IrhpPermitSectorQuota', PermitSectorQuotaRepo::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);
        $this->mockRepo('Sectors', SectorsRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $stockId = 999;
        $stockEntity = m::mock(IrhpPermitStock::class);

        $sector1 = m::mock(SectorsEntity::class);
        $sector2 = m::mock(SectorsEntity::class);
        $sectorResults = [$sector1, $sector2];

        $command = CreateCmd::create(['id' => $stockId]);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($stockEntity);

        $this->repoMap['Sectors']
            ->shouldReceive('fetchList')
            ->once()
            ->with(m::type(Sectors::class), Query::HYDRATE_OBJECT)
            ->andReturn($sectorResults);

        $this->repoMap['IrhpPermitSectorQuota']
            ->shouldReceive('save')
            ->times(count($sectorResults))
            ->with(m::type(IrhpPermitSectorQuota::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'Irhp Permit Stock' => $stockId
            ],
            'messages' => [
                'Irhp sector quotas created for stock ' . $stockId
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
