<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RestrictedRangesProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RestrictedWithFewestCountriesProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RestrictedWithFewestCountriesProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedWithFewestCountriesProviderTest extends MockeryTestCase
{
    public function testGetRanges(): void
    {
        $range1 = [
            'countryIds' => ['HU', 'IT']
        ];

        $range2 = [
            'countryIds' => []
        ];

        $range3 = [
            'countryIds' => ['GR', 'IT', 'AT']
        ];

        $range4 = [
            'countryIds' => []
        ];

        $range5 = [
            'countryIds' => ['GR', 'AT']
        ];

        $range6 = [
            'countryIds' => ['HU', 'IT', 'AT']
        ];

        $range7 = [
            'countryIds' => ['HU', 'IT', 'AT', 'GR']
        ];

        $ranges = [$range1, $range2, $range3, $range4, $range5, $range6];
        $restrictedRanges = [$range1, $range3, $range5, $range6, $range7];

        $expectedRestrictedRangesWithFewestCountries = [$range1, $range5];

        $restrictedRangesProvider = m::mock(RestrictedRangesProvider::class);
        $restrictedRangesProvider->shouldReceive('getRanges')
            ->with($ranges)
            ->andReturn($restrictedRanges);

        $restrictedWithFewestCountriesProvider = new RestrictedWithFewestCountriesProvider($restrictedRangesProvider);

        $this->assertEquals(
            $expectedRestrictedRangesWithFewestCountries,
            $restrictedWithFewestCountriesProvider->getRanges($ranges)
        );
    }
}
