<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\BusReg;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\BusReg\GrantNewText3;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class GrantNewText3
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class GrantNewText3Test extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the Bus Reg GrantNewText3 filter')]
    public function testProcess(): void
    {
        $sut = new GrantNewText3();

        $startPoint = 'start point';
        $finishPoint = 'finish point';
        $via = 'via point';
        $busServices = 'bus services';
        $busServiceTypes = 'bus service types';
        $otherDetails = 'Other details';
        $effectiveDate = '2014-05-14';
        $endDate = '2014-06-30';
        $formattedEffectiveDate = '14 May 2014';
        $formattedEndDate = '30 June 2014';

        $text = "From: " . $startPoint . "\n"
            . "To: " . $finishPoint . "\n"
            . "Via: " . $via . "\n"
            . "Name or No.: " . $busServices . "\n"
            . "Service type: " . $busServiceTypes . "\n"
            . "Effective date: " . $formattedEffectiveDate . "\n"
            . "End date: " . $formattedEndDate . "\n"
            . "Other details: " . $otherDetails;

        $expectedString = sprintf(
            $text,
            $startPoint,
            $finishPoint,
            $via,
            $busServices,
            $busServiceTypes,
            $formattedEffectiveDate,
            $formattedEndDate,
            $otherDetails
        );

        $input = [
            'busServices' => $busServices,
            'busServiceTypes' => $busServiceTypes
        ];

        $busRegMock = m::mock(BusRegEntity::class);
        $busRegMock->shouldReceive('getStartPoint')->andReturn($startPoint);
        $busRegMock->shouldReceive('getFinishPoint')->andReturn($finishPoint);
        $busRegMock->shouldReceive('getEffectiveDate')->andReturn($effectiveDate);
        $busRegMock->shouldReceive('getEndDate')->andReturn($endDate);
        $busRegMock->shouldReceive('getVia')->andReturn($via);
        $busRegMock->shouldReceive('getOtherDetails')->andReturn($otherDetails);

        $publicationLink = m::mock(PublicationLink::class)->makePartial();
        $publicationLink->shouldReceive('getBusReg')->andReturn($busRegMock);

        $output = $sut->process($publicationLink, new ImmutableArrayObject($input));
        $this->assertEquals($expectedString, $output->getText3());
    }
}
