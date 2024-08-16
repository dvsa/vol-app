<?php

namespace Dvsa\OlcsTest\Api\Service\Publication\Formatter;

use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Service\Publication\Formatter\OcVehicleTrailer as Formatter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * OcVehicleTrailerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class OcVehicleTrailerTest extends MockeryTestCase
{
    /**
     * @dataProvider dpFormat
     */
    public function testFormat($noOfVehiclesRequired, $noOfTrailersRequired, $useHgvCaption, $expectedOutput)
    {
        $applicationOperatingCentre = m::mock(ApplicationOperatingCentre::class);
        $applicationOperatingCentre->shouldReceive('getNoOfVehiclesRequired')
            ->withNoArgs()
            ->andReturn($noOfVehiclesRequired);
        $applicationOperatingCentre->shouldReceive('getNoOfTrailersRequired')
            ->withNoArgs()
            ->andReturn($noOfTrailersRequired);

        $this->assertEquals(
            $expectedOutput,
            Formatter::format($applicationOperatingCentre, $useHgvCaption)
        );
    }

    public function dpFormat()
    {
        return [
            'vehicles only' => [
                5,
                0,
                false,
                '5 vehicle(s)'
            ],
            'heavy goods vehicles only' => [
                4,
                0,
                true,
                '4 Heavy goods vehicle(s)'
            ],
            'vehicles and trailers' => [
                3,
                7,
                false,
                '3 vehicle(s), 7 trailer(s)'
            ],
            'heavy goods vehicles only' => [
                2,
                4,
                true,
                '2 Heavy goods vehicle(s), 4 trailer(s)'
            ],
            'trailers only' => [
                0,
                8,
                false,
                '8 trailer(s)'
            ],
        ];
    }
}
