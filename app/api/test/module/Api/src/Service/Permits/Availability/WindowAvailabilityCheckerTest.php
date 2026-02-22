<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Availability;

use DateTime;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityChecker;
use Dvsa\Olcs\Api\Service\Permits\Availability\WindowAvailabilityChecker;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * WindowAvailabilityCheckerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class WindowAvailabilityCheckerTest extends MockeryTestCase
{
    public $windowAvailabilityChecker;
    private $now;

    private $irhpPermitStock1Id;

    private $irhpPermitStock2Id;

    private $irhpPermitStock3Id;

    private $stockAvailabilityChecker;

    public function setUp(): void
    {
        $this->now = m::mock(DateTime::class);

        $this->irhpPermitStock1Id = 20;
        $this->irhpPermitStock2Id = 40;
        $this->irhpPermitStock3Id = 60;

        $irhpPermitWindow1 = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow1->shouldReceive('getIrhpPermitStock->getId')
            ->andReturn($this->irhpPermitStock1Id);

        $irhpPermitWindow2 = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow2->shouldReceive('getIrhpPermitStock->getId')
            ->andReturn($this->irhpPermitStock2Id);

        $irhpPermitWindow3 = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow3->shouldReceive('getIrhpPermitStock->getId')
            ->andReturn($this->irhpPermitStock3Id);

        $this->stockAvailabilityChecker = m::mock(StockAvailabilityChecker::class);

        $irhpPermitWindows = [
            $irhpPermitWindow1,
            $irhpPermitWindow2,
            $irhpPermitWindow3
        ];

        $irhpPermitWindowRepo = m::mock(IrhpPermitWindowRepository::class);
        $irhpPermitWindowRepo->shouldReceive('fetchOpenWindowsByType')
            ->with(IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM, $this->now)
            ->andReturn($irhpPermitWindows);

        $this->windowAvailabilityChecker = new WindowAvailabilityChecker(
            $irhpPermitWindowRepo,
            $this->stockAvailabilityChecker
        );
    }

    public function testHasAvailabilityTrue(): void
    {
        $this->stockAvailabilityChecker->shouldReceive('hasAvailability')
            ->with($this->irhpPermitStock1Id)
            ->andReturn(false);
        $this->stockAvailabilityChecker->shouldReceive('hasAvailability')
            ->with($this->irhpPermitStock2Id)
            ->andReturn(true);

        $this->assertTrue($this->windowAvailabilityChecker->hasAvailability($this->now));
    }

    public function testAvailabilityFalse(): void
    {
        $this->stockAvailabilityChecker->shouldReceive('hasAvailability')
            ->with($this->irhpPermitStock1Id)
            ->andReturn(false);
        $this->stockAvailabilityChecker->shouldReceive('hasAvailability')
            ->with($this->irhpPermitStock2Id)
            ->andReturn(false);
        $this->stockAvailabilityChecker->shouldReceive('hasAvailability')
            ->with($this->irhpPermitStock3Id)
            ->andReturn(false);

        $this->assertFalse($this->windowAvailabilityChecker->hasAvailability($this->now));
    }
}
