<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RestrictedRangesProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RestrictedWithMostMatchingCountriesProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RestrictedWithMostMatchingCountriesProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedWithMostMatchingCountriesProviderTest extends MockeryTestCase
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
            'countryIds' => ['GR', 'AT']
        ];

        $range5 = [
            'countryIds' => ['HU', 'IT', 'AT']
        ];

        $range6 = [
            'countryIds' => []
        ];

        $range7 = [
            'countryIds' => ['HU', 'IT', 'AT', 'GR']
        ];

        $range8 = [
            'countryIds' => ['GR', 'IT']
        ];

        $ranges = [$range1, $range2, $range3, $range4, $range5, $range6, $range7, $range8];
        $restrictedRanges = [$range1, $range3, $range4, $range5, $range7, $range8];
        $applicationCountryIds = ['AT', 'IT'];

        $expectedRestrictedRangesWithMostMatchingCountries = [$range3, $range5, $range7];

        $restrictedRangesProvider = m::mock(RestrictedRangesProvider::class);
        $restrictedRangesProvider->shouldReceive('getRanges')
            ->with($ranges)
            ->andReturn($restrictedRanges);

        $restrictedWithMostMatchingCountriesProvider = new RestrictedWithMostMatchingCountriesProvider(
            $restrictedRangesProvider
        );

        $this->assertEquals(
            $expectedRestrictedRangesWithMostMatchingCountries,
            $restrictedWithMostMatchingCountriesProvider->getRanges($ranges, $applicationCountryIds)
        );
    }
}
