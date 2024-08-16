<?php

namespace Dvsa\OlcsTest\Api\Service\Permits\Availability;

use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityChecker;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityCounter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StockAvailabilityCheckerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StockAvailabilityCheckerTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestHasAvailability
     */
    public function testHasAvailability($stockAvailabilityCount, $expected)
    {
        $irhpPermitStockId = 48;

        $stockAvailabilityCounter = m::mock(StockAvailabilityCounter::class);
        $stockAvailabilityCounter->shouldReceive('getCount')
            ->with($irhpPermitStockId)
            ->andReturn($stockAvailabilityCount);

        $stockAvailabilityChecker = new StockAvailabilityChecker($stockAvailabilityCounter);

        $this->assertEquals(
            $expected,
            $stockAvailabilityChecker->hasAvailability($irhpPermitStockId)
        );
    }

    public function dpTestHasAvailability()
    {
        return [
            [0, false],
            [1, true],
            [2, true],
        ];
    }
}
