<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RestrictedCountryIdsProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\WithFewestNonRequestedCountriesProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * WithFewestNonRequestedCountriesProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class WithFewestNonRequestedCountriesProviderTest extends MockeryTestCase
{
    public function testGetRanges(): void
    {
        $range1 = ['countryIds' => ['HU', 'IT']];
        $range2 = ['countryIds' => ['IT']];
        $range3 = ['countryIds' => ['GR', 'IT', 'AT', 'RU']];
        $range4 = ['countryIds' => ['AT']];
        $range5 = ['countryIds' => ['AT', 'GR']];
        $range6 = ['countryIds' => ['IT', 'AT', 'RU']];
        $range7 = ['countryIds' => ['RU']];
        $range8 = ['countryIds' => ['GR']];

        $ranges = [$range1, $range2, $range3, $range4, $range5, $range6, $range7, $range8];

        $applicationCountryIds = ['GR', 'IT'];

        $restrictedCountryIdsProvider = m::mock(RestrictedCountryIdsProvider::class);
        $restrictedCountryIdsProvider->shouldReceive('getIds')
            ->andReturn(['AT', 'GR', 'HU', 'IT', 'RU']);

        $withFewestNonRequestedCountriesProvider = new WithFewestNonRequestedCountriesProvider(
            $restrictedCountryIdsProvider
        );

        $expectedRanges = [$range2, $range8];

        $this->assertEquals(
            $expectedRanges,
            $withFewestNonRequestedCountriesProvider->getRanges($applicationCountryIds, $ranges)
        );
    }
}
