<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Availability;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Availability\EmissionsCategoryAvailabilityCounter;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityCounter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StockAvailabilityCounterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StockAvailabilityCounterTest extends MockeryTestCase
{
    public function testGetCount(): void
    {
        $euro5Available = 20;
        $euro6Available = 12;
        $irhpPermitStockId = 48;

        $expectedCount = 32;

        $emissionsCategoryAvailabilityCounter = m::mock(EmissionsCategoryAvailabilityCounter::class);
        $emissionsCategoryAvailabilityCounter->shouldReceive('getCount')
            ->with($irhpPermitStockId, RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5Available);
        $emissionsCategoryAvailabilityCounter->shouldReceive('getCount')
            ->with($irhpPermitStockId, RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6Available);

        $stockAvailabilityCounter = new StockAvailabilityCounter($emissionsCategoryAvailabilityCounter);

        $this->assertEquals(
            $expectedCount,
            $stockAvailabilityCounter->getCount($irhpPermitStockId)
        );
    }
}
