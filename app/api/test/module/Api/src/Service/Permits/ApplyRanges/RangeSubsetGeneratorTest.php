<?php

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\RangeSubsetGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RangeSubsetGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RangeSubsetGeneratorTest extends MockeryTestCase
{
   /**
     * @dataProvider dpTestGenerate
     */
    public function testGenerate($emissionsCategoryId, $ranges, $expectedRanges)
    {
        $irhpCandidatePermit = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit->shouldReceive('getAssignedEmissionsCategory->getId')
            ->andReturn($emissionsCategoryId);

        $rangeSubsetGenerator = new RangeSubsetGenerator();

        $this->assertEquals(
            $expectedRanges,
            $rangeSubsetGenerator->generate($irhpCandidatePermit, $ranges)
        );
    }

    public function dpTestGenerate()
    {
        $range1 = [
            'entity' => m::mock(IrhpPermitRange::class),
            'countryIds' => ['IT', 'RU'],
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF,
            'permitsRemaining' => 15
        ];

        $range2 = [
            'entity' => m::mock(IrhpPermitRange::class),
            'countryIds' => ['RU'],
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5_REF,
            'permitsRemaining' => 20
        ];

        $range3 = [
            'entity' => m::mock(IrhpPermitRange::class),
            'countryIds' => ['ES'],
            'emissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6_REF,
            'permitsRemaining' => 25
        ];

        $ranges = [$range1, $range2, $range3];

        return [
            [
                RefData::EMISSIONS_CATEGORY_EURO5_REF,
                $ranges,
                [$range2]
            ],
            [
                RefData::EMISSIONS_CATEGORY_EURO6_REF,
                $ranges,
                [$range1, $range3]
            ]
        ];
    }
}
