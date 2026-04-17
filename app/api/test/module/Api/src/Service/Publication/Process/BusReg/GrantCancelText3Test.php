<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\BusReg;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\BusReg\GrantCancelText3;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class GrantCancelText3
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class GrantCancelText3Test extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the Bus Reg GrantCancelText3 filter')]
    public function testProcess(): void
    {
        $sut = new GrantCancelText3();

        $startPoint = 'start point';
        $finishPoint = 'finish point';
        $busServices = 'bus services';
        $effectiveDate = '2014-05-14';
        $formattedEffectiveDate = '14 May 2014';
        $text = 'Operating between %s and %s given service number %s effective from %s.';

        $expectedString = sprintf(
            $text,
            $startPoint,
            $finishPoint,
            $busServices,
            $formattedEffectiveDate
        );

        $input = [
            'busServices' => $busServices
        ];

        $busRegMock = m::mock(BusRegEntity::class);
        $busRegMock->shouldReceive('getStartPoint')->andReturn($startPoint);
        $busRegMock->shouldReceive('getFinishPoint')->andReturn($finishPoint);
        $busRegMock->shouldReceive('getEffectiveDate')->andReturn($effectiveDate);

        $publicationLink = m::mock(PublicationLink::class)->makePartial();
        $publicationLink->shouldReceive('getBusReg')->andReturn($busRegMock);

        $output = $sut->process($publicationLink, new ImmutableArrayObject($input));
        $this->assertEquals($expectedString, $output->getText3());
    }
}
