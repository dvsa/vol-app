<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\PermitsAvailable;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityChecker;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\PermitsAvailable as PermitsAvailableQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class PermitsAvailableTest extends QueryHandlerTestCase
{
    private $irhpApplication;

    private $query;

    public function setUp(): void
    {
        $this->sut = new PermitsAvailable();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsAvailabilityStockAvailabilityChecker' => m::mock(StockAvailabilityChecker::class),
        ];

        $this->irhpApplication = m::mock(IrhpApplication::class);

        $this->query = PermitsAvailableQry::create(['id' => 47]);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($this->query)
            ->andReturn($this->irhpApplication);

        parent::setUp();
    }

    public function testReturnTrueWhenWindowOpenAndHasAvailability(): void
    {
        $irhpPermitStockId = 47;

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('isActive')
            ->withNoArgs()
            ->andReturn(true);
        $irhpPermitWindow->shouldReceive('getIrhpPermitStock->getId')
            ->andReturn($irhpPermitStockId);

        $this->irhpApplication->shouldReceive('getIrhpPermitType->isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(true);
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication->getIrhpPermitWindow')
            ->andReturn($irhpPermitWindow);

        $this->mockedSmServices['PermitsAvailabilityStockAvailabilityChecker']->shouldReceive('hasAvailability')
            ->with($irhpPermitStockId)
            ->andReturn(true);

        $this->assertEquals(
            ['permitsAvailable' => true],
            $this->sut->handleQuery($this->query)
        );
    }

    public function testReturnFalseWhenWindowOpenButNoAvailability(): void
    {
        $irhpPermitStockId = 47;

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('isActive')
            ->withNoArgs()
            ->andReturn(true);
        $irhpPermitWindow->shouldReceive('getIrhpPermitStock->getId')
            ->andReturn($irhpPermitStockId);

        $this->irhpApplication->shouldReceive('getIrhpPermitType->isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(true);
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication->getIrhpPermitWindow')
            ->andReturn($irhpPermitWindow);

        $this->mockedSmServices['PermitsAvailabilityStockAvailabilityChecker']->shouldReceive('hasAvailability')
            ->with($irhpPermitStockId)
            ->andReturn(false);

        $this->assertEquals(
            ['permitsAvailable' => false],
            $this->sut->handleQuery($this->query)
        );
    }

    public function testReturnFalseWhenWindowClosed(): void
    {
        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);
        $irhpPermitWindow->shouldReceive('isActive')
            ->withNoArgs()
            ->andReturn(false);

        $this->irhpApplication->shouldReceive('getIrhpPermitType->isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(true);
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication->getIrhpPermitWindow')
            ->andReturn($irhpPermitWindow);

        $this->assertEquals(
            ['permitsAvailable' => false],
            $this->sut->handleQuery($this->query)
        );
    }

    public function testReturnTrueWhenNotShortTerm(): void
    {
        $this->irhpApplication->shouldReceive('getIrhpPermitType->isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(false);

        $this->assertEquals(
            ['permitsAvailable' => true],
            $this->sut->handleQuery($this->query)
        );
    }
}
