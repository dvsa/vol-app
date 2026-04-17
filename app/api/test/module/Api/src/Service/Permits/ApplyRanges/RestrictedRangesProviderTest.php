<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RestrictedRangesProvider;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RestrictedRangesProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedRangesProviderTest extends MockeryTestCase
{
    public function testGetIds(): void
    {
        $range1 = [
            'countryIds' => ['HU', 'IT']
        ];

        $range2 = [
            'countryIds' => []
        ];

        $range3 = [
            'countryIds' => ['AT']
        ];

        $range4 = [
            'countryIds' => ['GR', 'IT', 'AT']
        ];

        $range5 = [
            'countryIds' => []
        ];

        $ranges = [$range1, $range2, $range3, $range4, $range5];
        $expectedRestrictedRanges = [$range1, $range3, $range4];

        $restrictedRangesProvider = new RestrictedRangesProvider();

        $this->assertEquals(
            $expectedRestrictedRanges,
            $restrictedRangesProvider->getRanges($ranges)
        );
    }
}
