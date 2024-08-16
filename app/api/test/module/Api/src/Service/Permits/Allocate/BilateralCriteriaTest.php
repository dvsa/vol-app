<?php

namespace Dvsa\OlcsTest\Api\Service\Permits\Allocate;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Allocate\BilateralCriteria;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * BilateralCriteriaTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class BilateralCriteriaTest extends MockeryTestCase
{
    /**
     * @dataProvider dpMatches
     */
    public function testMatches(
        $rangeCabotage,
        $rangeJourneyId,
        $criteriaStandardOrCabotage,
        $criteriaJourneyId,
        $expected
    ) {
        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $irhpPermitRange->shouldReceive('getCabotage')
            ->withNoArgs()
            ->andReturn($rangeCabotage);
        $irhpPermitRange->shouldReceive('getJourney->getId')
            ->withNoArgs()
            ->andReturn($rangeJourneyId);

        $bilateralCriteria = new BilateralCriteria($criteriaStandardOrCabotage, $criteriaJourneyId);

        $this->assertEquals(
            $expected,
            $bilateralCriteria->matches($irhpPermitRange)
        );
    }

    public function dpMatches()
    {
        return [
            [
                false,
                RefData::JOURNEY_SINGLE,
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
                RefData::JOURNEY_SINGLE,
                true,
            ],
            [
                false,
                RefData::JOURNEY_SINGLE,
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
                RefData::JOURNEY_MULTIPLE,
                false,
            ],
            [
                false,
                RefData::JOURNEY_SINGLE,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
                RefData::JOURNEY_SINGLE,
                false,
            ],
            [
                false,
                RefData::JOURNEY_SINGLE,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
                RefData::JOURNEY_MULTIPLE,
                false,
            ],
            [
                false,
                RefData::JOURNEY_MULTIPLE,
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
                RefData::JOURNEY_SINGLE,
                false,
            ],
            [
                false,
                RefData::JOURNEY_MULTIPLE,
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
                RefData::JOURNEY_MULTIPLE,
                true,
            ],
            [
                false,
                RefData::JOURNEY_MULTIPLE,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
                RefData::JOURNEY_SINGLE,
                false,
            ],
            [
                false,
                RefData::JOURNEY_MULTIPLE,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
                RefData::JOURNEY_MULTIPLE,
                false,
            ],
            [
                true,
                RefData::JOURNEY_SINGLE,
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
                RefData::JOURNEY_SINGLE,
                false,
            ],
            [
                true,
                RefData::JOURNEY_SINGLE,
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
                RefData::JOURNEY_MULTIPLE,
                false,
            ],
            [
                true,
                RefData::JOURNEY_SINGLE,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
                RefData::JOURNEY_SINGLE,
                true,
            ],
            [
                true,
                RefData::JOURNEY_SINGLE,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
                RefData::JOURNEY_MULTIPLE,
                false,
            ],
            [
                true,
                RefData::JOURNEY_MULTIPLE,
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
                RefData::JOURNEY_SINGLE,
                false,
            ],
            [
                true,
                RefData::JOURNEY_MULTIPLE,
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
                RefData::JOURNEY_MULTIPLE,
                false,
            ],
            [
                true,
                RefData::JOURNEY_MULTIPLE,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
                RefData::JOURNEY_SINGLE,
                false,
            ],
            [
                true,
                RefData::JOURNEY_MULTIPLE,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED,
                RefData::JOURNEY_MULTIPLE,
                true,
            ],
        ];
    }
}
