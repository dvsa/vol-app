<?php

namespace Dvsa\OlcsTest\Api\Service\Permits\Availability;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Availability\EmissionsCategoriesGrantabilityChecker;
use Dvsa\Olcs\Api\Service\Permits\Availability\EmissionsCategoryAvailabilityCounter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EmissionsCategoriesGrantabilityCheckerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsCategoriesGrantabilityCheckerTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestIsGrantable
     */
    public function testIsGrantable($requiredEuro5, $availableEuro5, $requiredEuro6, $availableEuro6, $isGrantable)
    {
        $irhpPermitStockId = 57;

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getId')
            ->andReturn($irhpPermitStockId);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->andReturn($requiredEuro5);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->andReturn($requiredEuro6);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($irhpPermitApplication);

        $emissionsCategoryAvailabilityCounter = m::mock(EmissionsCategoryAvailabilityCounter::class);
        $emissionsCategoryAvailabilityCounter->shouldReceive('getCount')
            ->with($irhpPermitStockId, RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($availableEuro5);
        $emissionsCategoryAvailabilityCounter->shouldReceive('getCount')
            ->with($irhpPermitStockId, RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($availableEuro6);

        $emissionsCategoriesGrantabilityChecker = new EmissionsCategoriesGrantabilityChecker(
            $emissionsCategoryAvailabilityCounter
        );

        $this->assertEquals(
            $isGrantable,
            $emissionsCategoriesGrantabilityChecker->isGrantable($irhpApplication)
        );
    }

    public function dpTestIsGrantable()
    {
        return [
            [5, 5, 5, 5, true],
            [6, 5, 5, 5, false],
            [5, 5, 6, 5, false],
            [6, 5, 6, 5, false],
            [5, 6, 5, 5, true],
            [5, 5, 5, 6, true],
            [5, 6, 5, 6, true],
            [5, 6, 6, 5, false],
        ];
    }
}
