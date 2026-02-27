<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Service\Permits\ApplyRanges\EntityIdsExtractor;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EntityIdsExtractorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EntityIdsExtractorTest extends MockeryTestCase
{
    public function testGetExtracted(): void
    {
        $country1Id = 4;
        $country1 = m::mock(Country::class);
        $country1->shouldReceive('getId')
            ->andReturn($country1Id);

        $country2Id = 7;
        $country2 = m::mock(Country::class);
        $country2->shouldReceive('getId')
            ->andReturn($country2Id);

        $country3Id = 15;
        $country3 = m::mock(Country::class);
        $country3->shouldReceive('getId')
            ->andReturn($country3Id);

        $countries = [
            $country1,
            $country2,
            $country3
        ];

        $expectedCountryIds = [
            $country1Id,
            $country2Id,
            $country3Id
        ];

        $entityIdsExtractor = new EntityIdsExtractor();
        $this->assertEquals(
            $expectedCountryIds,
            $entityIdsExtractor->getExtracted($countries)
        );
    }
}
