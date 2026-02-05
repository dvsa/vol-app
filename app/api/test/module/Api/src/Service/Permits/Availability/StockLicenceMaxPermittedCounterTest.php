<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Availability;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockLicenceMaxPermittedCounter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * StockLicenceMaxPermittedCounterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StockLicenceMaxPermittedCounterTest extends MockeryTestCase
{
    private $irhpPermitType;

    private $irhpPermitStock;

    private $licence;

    private $irhpPermitRepo;

    private $stockLicenceMaxPermittedCounter;

    public function setUp(): void
    {
        $this->irhpPermitType = m::mock(IrhpPermitType::class);

        $this->irhpPermitStock = m::mock(IrhpPermitStock::class);
        $this->irhpPermitStock->shouldReceive('getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($this->irhpPermitType);

        $this->licence = m::mock(Licence::class);

        $this->irhpPermitRepo = m::mock(IrhpPermitRepository::class);

        $this->stockLicenceMaxPermittedCounter = new StockLicenceMaxPermittedCounter($this->irhpPermitRepo);
    }

    public function testGetCountEcmtAnnual(): void
    {
        $licenceId = 707;
        $totAuthVehicles = 15;
        $validityYear = 2023;
        $allocatedPermitCount = 11;
        $expectedMaxPermitted = 4;

        $this->irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturnTrue();
        $this->irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturnFalse();

        $this->irhpPermitStock->shouldReceive('getValidityYear')
            ->withNoArgs()
            ->andReturn($validityYear);

        $this->licence->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($licenceId);
        $this->licence->shouldReceive('getTotAuthVehicles')
            ->withNoArgs()
            ->andReturn($totAuthVehicles);

        $this->irhpPermitRepo->shouldReceive('getEcmtAnnualPermitCountByLicenceAndStockEndYear')
            ->with($licenceId, $validityYear)
            ->andReturn($allocatedPermitCount);

        $this->assertEquals(
            $expectedMaxPermitted,
            $this->stockLicenceMaxPermittedCounter->getCount($this->irhpPermitStock, $this->licence)
        );
    }

    public function testGetCountEcmtShortTerm(): void
    {
        $totAuthVehicles = 12;
        $totAuthVehiclesTimesTwo = 24;

        $this->irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturnFalse();
        $this->irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturnTrue();

        $this->licence->shouldReceive('getTotAuthVehicles')
            ->withNoArgs()
            ->andReturn($totAuthVehicles);

        $this->assertEquals(
            $totAuthVehiclesTimesTwo,
            $this->stockLicenceMaxPermittedCounter->getCount($this->irhpPermitStock, $this->licence)
        );
    }

    public function testGetCountInvalidType(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(StockLicenceMaxPermittedCounter::ERR_INVALID_TYPE);

        $this->irhpPermitType->shouldReceive('isEcmtAnnual')
            ->withNoArgs()
            ->andReturnFalse();
        $this->irhpPermitType->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturnFalse();

        $this->stockLicenceMaxPermittedCounter->getCount($this->irhpPermitStock, $this->licence);
    }
}
