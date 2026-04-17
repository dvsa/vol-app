<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpWithCountriesAndMultipleMatchingRangesProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpWithCountriesAndNoMatchingRangesProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpWithCountriesProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RestrictedWithMostMatchingCountriesProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ForCpWithCountriesProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ForCpWithCountriesProviderTest extends MockeryTestCase
{
    private $result;

    private $applicationCountryIds;

    private $range1;

    private $range2;

    private $range3;

    private $ranges;

    private $restrictedWithMostMatchingCountriesProvider;

    private $forCpWithCountriesAndNoMatchingRangesProvider;

    private $forCpWithCountriesAndMultipleMatchingRangesProvider;

    private $forCpWithCountriesProvider;

    public function setUp(): void
    {
        $this->result = new Result();

        $this->applicationCountryIds = ['RU', 'IT'];

        $this->range1 = [
            'entity' => m::mock(IrhpPermitRange::class),
            'countryIds' => ['IT', 'RU'],
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF,
            'permitsRemaining' => 20
        ];

        $range2Entity = m::mock(IrhpPermitRange::class);
        $range2Entity->shouldReceive('getId')
            ->andReturn(45);

        $this->range2 = [
            'entity' => $range2Entity,
            'countryIds' => ['IT', 'RU'],
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5_REF,
            'permitsRemaining' => 20
        ];

        $this->range3 = [
            'entity' => m::mock(IrhpPermitRange::class),
            'countryIds' => ['ES'],
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5_REF,
            'permitsRemaining' => 25
        ];

        $this->ranges = [$this->range1, $this->range2, $this->range3];

        $this->restrictedWithMostMatchingCountriesProvider = m::mock(
            RestrictedWithMostMatchingCountriesProvider::class
        );

        $this->forCpWithCountriesAndNoMatchingRangesProvider = m::mock(
            ForCpWithCountriesAndNoMatchingRangesProvider::class
        );

        $this->forCpWithCountriesAndMultipleMatchingRangesProvider = m::mock(
            ForCpWithCountriesAndMultipleMatchingRangesProvider::class
        );

        $this->forCpWithCountriesProvider = new ForCpWithCountriesProvider(
            $this->restrictedWithMostMatchingCountriesProvider,
            $this->forCpWithCountriesAndNoMatchingRangesProvider,
            $this->forCpWithCountriesAndMultipleMatchingRangesProvider
        );
    }

    public function testNoMatchingRanges(): void
    {
        $matchingRanges = [];

        $this->restrictedWithMostMatchingCountriesProvider->shouldReceive('getRanges')
            ->with($this->ranges, $this->applicationCountryIds)
            ->once()
            ->andReturn($matchingRanges);

        $this->forCpWithCountriesAndNoMatchingRangesProvider->shouldReceive('selectRange')
            ->with($this->result, $this->ranges)
            ->once()
            ->andReturn($this->range1);

        $result = $this->forCpWithCountriesProvider->selectRange(
            $this->result,
            $this->ranges,
            $this->applicationCountryIds
        );

        $this->assertEquals($this->range1, $result);

        $this->assertEquals([], $this->result->getMessages());
    }

    public function testOneMatchingRange(): void
    {
        $matchingRanges = [$this->range2];

        $this->restrictedWithMostMatchingCountriesProvider->shouldReceive('getRanges')
            ->with($this->ranges, $this->applicationCountryIds)
            ->once()
            ->andReturn($matchingRanges);

        $result = $this->forCpWithCountriesProvider->selectRange(
            $this->result,
            $this->ranges,
            $this->applicationCountryIds
        );

        $this->assertEquals($this->range2, $result);

        $expectedMessages = [
            '    - range 45 with countries IT, RU has the most matching countries'
        ];

        $this->assertEquals(
            $expectedMessages,
            $this->result->getMessages()
        );
    }

    public function testMultipleMatchingRanges(): void
    {
        $matchingRanges = [$this->range2, $this->range3];

        $this->restrictedWithMostMatchingCountriesProvider->shouldReceive('getRanges')
            ->with($this->ranges, $this->applicationCountryIds)
            ->once()
            ->andReturn($matchingRanges);

        $this->forCpWithCountriesAndMultipleMatchingRangesProvider->shouldReceive('selectRange')
            ->with($this->result, $matchingRanges, $this->applicationCountryIds)
            ->once()
            ->andReturn($this->range3);

        $result = $this->forCpWithCountriesProvider->selectRange(
            $this->result,
            $this->ranges,
            $this->applicationCountryIds
        );

        $this->assertEquals($this->range3, $result);

        $this->assertEquals([], $this->result->getMessages());
    }
}
