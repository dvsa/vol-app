<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpWithCountriesAndNoMatchingRangesProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\HighestAvailabilityRangeSelector;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RestrictedWithFewestCountriesProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\UnrestrictedWithLowestStartNumberProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * ForCpWithCountriesAndNoMatchingRangesProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ForCpWithCountriesAndNoMatchingRangesProviderTest extends MockeryTestCase
{
    private $result;

    private $range1;

    private $range2;

    private $range3;

    private $ranges;

    private $unrestrictedWithLowestStartNumberProvider;

    private $restrictedWithFewestCountriesProvider;

    private $highestAvailabilityRangeSelector;

    private $forCpWithCountriesAndNoMatchingRangesProvider;

    public function setUp(): void
    {
        $this->result = new Result();

        $rangeEntity1 = m::mock(IrhpPermitRange::class);

        $rangeEntity2 = m::mock(IrhpPermitRange::class);
        $rangeEntity2->shouldReceive('getId')
            ->andReturn(49);

        $rangeEntity3 = m::mock(IrhpPermitRange::class);
        $rangeEntity3->shouldReceive('getId')
            ->andReturn(55);
        $rangeEntity3->shouldReceive('getFromNo')
            ->andReturn(400);

        $this->range1 = [
            'entity' => $rangeEntity1,
        ];

        $this->range2 = [
            'entity' => $rangeEntity2,
            'countryIds' => ['AT', 'GR']
        ];

        $this->range3 = [
            'entity' => $rangeEntity3,
        ];

        $this->ranges = [$this->range1, $this->range2, $this->range3];
        $this->unrestrictedWithLowestStartNumberProvider = m::mock(UnrestrictedWithLowestStartNumberProvider::class);

        $this->restrictedWithFewestCountriesProvider = m::mock(RestrictedWithFewestCountriesProvider::class);

        $this->highestAvailabilityRangeSelector = m::mock(HighestAvailabilityRangeSelector::class);

        $this->forCpWithCountriesAndNoMatchingRangesProvider = new ForCpWithCountriesAndNoMatchingRangesProvider(
            $this->unrestrictedWithLowestStartNumberProvider,
            $this->restrictedWithFewestCountriesProvider,
            $this->highestAvailabilityRangeSelector
        );
    }

    public function testUseFirstRestrictedWithFewestCountries(): void
    {
        $matchingRanges = [$this->range2];

        $this->unrestrictedWithLowestStartNumberProvider->shouldReceive('getRange')
            ->with($this->ranges)
            ->andReturn(null);

        $this->restrictedWithFewestCountriesProvider->shouldReceive('getRanges')
            ->with($this->ranges)
            ->andReturn($matchingRanges);

        $this->assertEquals(
            $this->range2,
            $this->forCpWithCountriesAndNoMatchingRangesProvider->selectRange($this->result, $this->ranges)
        );

        $this->assertEquals(
            [
                '    - no restricted ranges found with matching countries',
                '    - no unrestricted ranges found with lowest start number',
                '    - using first restricted range with fewest countries: id 49 has countries AT, GR'
            ],
            $this->result->getMessages()
        );
    }

    public function testExceptionNoRangesWithFewestCountries(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Assertion failed in method %s::selectRange: count($ranges) == 0',
                ForCpWithCountriesAndNoMatchingRangesProvider::class
            )
        );

        $matchingRanges = [];

        $this->unrestrictedWithLowestStartNumberProvider->shouldReceive('getRange')
            ->with($this->ranges)
            ->andReturn(null);

        $this->restrictedWithFewestCountriesProvider->shouldReceive('getRanges')
            ->with($this->ranges)
            ->andReturn($matchingRanges);

        $this->forCpWithCountriesAndNoMatchingRangesProvider->selectRange($this->result, $this->ranges);
    }

    public function testMultipleRangesWithFewestCountries(): void
    {
        $matchingRanges = [$this->range1, $this->range2];

        $this->unrestrictedWithLowestStartNumberProvider->shouldReceive('getRange')
            ->with($this->ranges)
            ->andReturn(null);

        $this->restrictedWithFewestCountriesProvider->shouldReceive('getRanges')
            ->with($this->ranges)
            ->andReturn($matchingRanges);

        $this->highestAvailabilityRangeSelector->shouldReceive('getRange')
            ->with($this->result, $matchingRanges)
            ->andReturn($this->range1);

        $this->assertEquals(
            $this->range1,
            $this->forCpWithCountriesAndNoMatchingRangesProvider->selectRange($this->result, $this->ranges)
        );

        $this->assertEquals(
            [
                '    - no restricted ranges found with matching countries',
                '    - no unrestricted ranges found with lowest start number',
                '    - multiple ranges have the fewest countries'
            ],
            $this->result->getMessages()
        );
    }

    public function testUseUnrestrictedWithLowestStartNumber(): void
    {
        $this->unrestrictedWithLowestStartNumberProvider->shouldReceive('getRange')
            ->with($this->ranges)
            ->andReturn($this->range3);

        $this->assertEquals(
            $this->range3,
            $this->forCpWithCountriesAndNoMatchingRangesProvider->selectRange($this->result, $this->ranges)
        );

        $this->assertEquals(
            [
                '    - no restricted ranges found with matching countries',
                '    - using unrestricted range with lowest start number: id 55 starts at 400',
            ],
            $this->result->getMessages()
        );
    }
}
