<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitStock;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitStock\Delete as DeleteHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as PermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitJurisdictionQuota as IrhpPermitJurisdictionQuotaRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitSectorQuota as IrhpPermitSectorQuotaRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Delete as DeleteCmd;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as PermitStockEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitJurisdictionQuota as JurisdictionEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota as SectorEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Create IRHP Permit Stock Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteHandler();
        $this->mockRepo('IrhpPermitStock', PermitStockRepo::class);
        $this->mockRepo('IrhpPermitJurisdictionQuota', IrhpPermitJurisdictionQuotaRepo::class);
        $this->mockRepo('IrhpPermitSectorQuota', IrhpPermitSectorQuotaRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'id' => '1'
        ];

        $command = DeleteCmd::create($cmdData);

        $id = $cmdData['id'];

        $irhpPermitStock = m::mock(PermitStockEntity::class);

        $jurisdictionQuota1 = m::mock(JurisdictionEntity::class);
        $jurisdictionQuota2 = m::mock(JurisdictionEntity::class);
        $jurisdictionQuota3 = m::mock(JurisdictionEntity::class);

        $sectorQuota1 = m::mock(SectorEntity::class);
        $sectorQuota2 = m::mock(SectorEntity::class);
        $sectorQuota3 = m::mock(SectorEntity::class);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpPermitStock);

        $irhpPermitStock
            ->shouldReceive('canDelete')
            ->once()
            ->andReturn(true);

        $irhpPermitStock
            ->shouldReceive('getIrhpPermitJurisdictionQuotas')
            ->once()
            ->andReturn(
                new ArrayCollection(
                    [
                        $jurisdictionQuota1,
                        $jurisdictionQuota2,
                        $jurisdictionQuota3
                    ]
                )
            );

        $this->repoMap['IrhpPermitJurisdictionQuota']
            ->shouldReceive('delete')
            ->with($jurisdictionQuota1)
            ->once();

        $this->repoMap['IrhpPermitJurisdictionQuota']
            ->shouldReceive('delete')
            ->with($jurisdictionQuota2)
            ->once();

        $this->repoMap['IrhpPermitJurisdictionQuota']
            ->shouldReceive('delete')
            ->with($jurisdictionQuota3)
            ->once();

        $irhpPermitStock
            ->shouldReceive('getIrhpPermitSectorQuotas')
            ->once()
            ->andReturn(
                new ArrayCollection(
                    [
                        $sectorQuota1,
                        $sectorQuota2,
                        $sectorQuota3
                    ]
                )
            );

        $this->repoMap['IrhpPermitSectorQuota']
            ->shouldReceive('delete')
            ->with($sectorQuota1)
            ->once();

        $this->repoMap['IrhpPermitSectorQuota']
            ->shouldReceive('delete')
            ->with($sectorQuota2)
            ->once();

        $this->repoMap['IrhpPermitSectorQuota']
            ->shouldReceive('delete')
            ->with($sectorQuota3)
            ->once();

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('delete')
            ->once()
            ->with($irhpPermitStock);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['id' => 1],
            'messages' => ['Permit Stock Deleted']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Test for preventing a Permit Stock being deleted if it has existing dependencies - no values are asserted as
     * this tests to ensure that a validation exception is thrown.
     */
    public function testHandleCantDelete(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('irhp-permit-stock-cannot-delete-active-dependencies');

        $cmdData = [
            'id' => '1'
        ];

        $command = DeleteCmd::create($cmdData);

        $id = $cmdData['id'];

        $irhpPermitStock = m::mock(PermitStockEntity::class);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpPermitStock);

        $irhpPermitStock
            ->shouldReceive('canDelete')
            ->once()
            ->andReturn(false);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('delete')
            ->never();

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNotFoundException(): void
    {
        $cmdData = [
            'id' => '1'
        ];

        $command = DeleteCmd::create($cmdData);

        $id = $cmdData['id'];

        $irhpPermitStock = m::mock(PermitStockEntity::class);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($irhpPermitStock);

        $irhpPermitStock
            ->shouldReceive('canDelete')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $irhpPermitStock
            ->shouldReceive('getIrhpPermitJurisdictionQuotas')
            ->once()
            ->andReturn(new ArrayCollection([]));

        $irhpPermitStock
            ->shouldReceive('getIrhpPermitSectorQuotas')
            ->once()
            ->andReturn(new ArrayCollection([]));

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('delete')
            ->with($irhpPermitStock)
            ->once()
            ->andThrow(NotFoundException::class);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['Id 1 not found']
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
