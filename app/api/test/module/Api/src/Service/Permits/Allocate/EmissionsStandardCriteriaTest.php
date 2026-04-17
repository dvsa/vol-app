<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Allocate;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Allocate\EmissionsStandardCriteria;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EmissionsStandardCriteriaTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsStandardCriteriaTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpMatches')]
    public function testMatches(mixed $rangeEmissionsCategoryId, mixed $criteriaEmissionsCategoryId, mixed $expected): void
    {
        $irhpPermitRange = m::mock(IrhpPermitRange::class);
        $irhpPermitRange->shouldReceive('getEmissionsCategory->getId')
            ->withNoArgs()
            ->andReturn($rangeEmissionsCategoryId);

        $emissionsStandardCriteria = new EmissionsStandardCriteria($criteriaEmissionsCategoryId);

        $this->assertEquals(
            $expected,
            $emissionsStandardCriteria->matches($irhpPermitRange)
        );
    }

    public static function dpMatches(): array
    {
        return [
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, RefData::EMISSIONS_CATEGORY_EURO5_REF, true],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, RefData::EMISSIONS_CATEGORY_EURO6_REF, false],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, RefData::EMISSIONS_CATEGORY_EURO5_REF, false],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, RefData::EMISSIONS_CATEGORY_EURO6_REF, true],
        ];
    }
}
