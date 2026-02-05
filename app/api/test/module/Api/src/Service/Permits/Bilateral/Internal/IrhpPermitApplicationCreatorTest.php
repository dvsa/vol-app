<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\IrhpPermitApplicationCreator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\IrhpPermitApplicationFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IrhpPermitApplicationCreatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitApplicationCreatorTest extends MockeryTestCase
{
    public function testHandle(): void
    {
        $stockId = 47;

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getOpenWindow')
            ->withNoArgs()
            ->andReturn($irhpPermitWindow);

        $irhpPermitStockRepo = m::mock(IrhpPermitStockRepository::class);
        $irhpPermitStockRepo->shouldReceive('fetchById')
            ->with($stockId)
            ->andReturn($irhpPermitStock);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getId')
            ->withNoArgs()
            ->andReturn($stockId);

        $irhpPermitApplicationRepo = m::mock(IrhpPermitApplicationRepository::class);
        $irhpPermitApplicationRepo->shouldReceive('save')
            ->with($irhpPermitApplication)
            ->once();

        $irhpApplication = m::mock(IrhpApplication::class);

        $irhpPermitApplicationFactory = m::mock(IrhpPermitApplicationFactory::class);
        $irhpPermitApplicationFactory->shouldReceive('create')
            ->with($irhpApplication, $irhpPermitWindow)
            ->andReturn($irhpPermitApplication);

        $irhpPermitApplicationCreator = new IrhpPermitApplicationCreator(
            $irhpPermitStockRepo,
            $irhpPermitApplicationRepo,
            $irhpPermitApplicationFactory
        );

        $this->assertSame(
            $irhpPermitApplication,
            $irhpPermitApplicationCreator->create($irhpApplication, $stockId)
        );
    }
}
