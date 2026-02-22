<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\EntityIdsExtractor;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpWithCountriesProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\ForCpWithNoCountriesProvider;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RangeSubsetGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ForCpProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ForCpProviderTest extends MockeryTestCase
{
    private $result;

    private $ranges;

    private $rangeSubset;

    private $irhpCandidatePermit;

    private $forCpWithCountriesProvider;

    private $forCpWithNoCountriesProvider;

    private $entityIdsExtractor;

    private $rangeSubsetGenerator;

    private $forCpProvider;

    public function setUp(): void
    {
        $this->result = new Result();

        $this->ranges = [
            [
                'entity' => m::mock(IrhpPermitRange::class),
                'countryIds' => ['IT', 'RU'],
                'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF,
                'permitsRemaining' => 20
            ],
            [
                'entity' => m::mock(IrhpPermitRange::class),
                'countryIds' => ['IT', 'RU'],
                'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5_REF,
                'permitsRemaining' => 20
            ]
        ];

        $this->rangeSubset = [
            [
                'entity' => m::mock(IrhpPermitRange::class),
                'countryIds' => ['IT', 'RU'],
                'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF,
                'permitsRemaining' => 20
            ]
        ];

        $this->irhpCandidatePermit = m::mock(IrhpCandidatePermit::class);

        $this->forCpWithCountriesProvider = m::mock(ForCpWithCountriesProvider::class);

        $this->forCpWithNoCountriesProvider = m::mock(ForCpWithNoCountriesProvider::class);

        $this->entityIdsExtractor = m::mock(EntityIdsExtractor::class);

        $this->rangeSubsetGenerator = m::mock(RangeSubsetGenerator::class);
        $this->rangeSubsetGenerator->shouldReceive('generate')
            ->with($this->irhpCandidatePermit, $this->ranges)
            ->andReturn($this->rangeSubset);

        $this->forCpProvider = new ForCpProvider(
            $this->forCpWithCountriesProvider,
            $this->forCpWithNoCountriesProvider,
            $this->entityIdsExtractor,
            $this->rangeSubsetGenerator
        );
    }

    public function testSelectRangeForNoCountriesRequested(): void
    {
        $selectedRangeEntity = m::mock(IrhpPermitRange::class);

        $selectedRange = [
            'entity' => $selectedRangeEntity,
            'countryIds' => ['IT', 'RU'],
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF,
            'permitsRemaining' => 20
        ];

        $this->irhpCandidatePermit
            ->shouldReceive('getIrhpPermitApplication->getIrhpApplication->getCountrys->getValues')
            ->andReturn([]);

        $this->forCpWithNoCountriesProvider->shouldReceive('selectRange')
            ->with($this->result, $this->rangeSubset)
            ->andReturn($selectedRange);

        $this->assertSame(
            $selectedRangeEntity,
            $this->forCpProvider->selectRange($this->result, $this->irhpCandidatePermit, $this->ranges)
        );

        $this->assertEquals(
            ['    - has no countries'],
            $this->result->getMessages()
        );
    }

    public function testSelectRangeForOneOrMoreCountriesRequested(): void
    {
        $applicationCountries = [
            m::mock(Country::class),
            m::mock(Country::class)
        ];

        $applicationCountryIds = ['AT', 'GR'];

        $this->entityIdsExtractor->shouldReceive('getExtracted')
            ->with($applicationCountries)
            ->andReturn($applicationCountryIds);

        $selectedRangeEntity = m::mock(IrhpPermitRange::class);

        $selectedRange = [
            'entity' => $selectedRangeEntity,
            'countryIds' => ['IT', 'RU'],
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF,
            'permitsRemaining' => 20
        ];

        $this->irhpCandidatePermit
            ->shouldReceive('getIrhpPermitApplication->getIrhpApplication->getCountrys->getValues')
            ->andReturn($applicationCountries);

        $this->forCpWithCountriesProvider->shouldReceive('selectRange')
            ->with($this->result, $this->rangeSubset, $applicationCountryIds)
            ->andReturn($selectedRange);

        $this->assertSame(
            $selectedRangeEntity,
            $this->forCpProvider->selectRange($this->result, $this->irhpCandidatePermit, $this->ranges)
        );

        $this->assertEquals(
            ['    - has one or more countries: AT, GR'],
            $this->result->getMessages()
        );
    }
}
