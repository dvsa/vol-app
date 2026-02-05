<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\PiHearing;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\PreviousHearingData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PreviousHearingDataTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PreviousHearingDataTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the previous hearing date filter')]
    public function testProvide(): void
    {
        $pi = 99;
        $hearingDate = '2014-03-16 14:30:00';
        $previousAdjournedDate = '2014-03-18 12:45:00';
        $formattedAdjournedDate = '18 March 2014';

        $input = [
            'hearingDate' => $hearingDate,
        ];

        $output = [
            'hearingDate' => $hearingDate,
            'previousHearing' => $formattedAdjournedDate
        ];

        $expectedOutput = new \ArrayObject($output);

        $publication = m::mock(PublicationLink::class);
        $publication->shouldReceive('getPi->getId')->once()->andReturn($pi);

        $piHearingResult = m::mock();
        $piHearingResult->shouldReceive('serialize')
            ->andReturn(['hearingDate' => $previousAdjournedDate])
            ->once()
            ->getMock();

        $mockQueryHandler = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class);
        $mockQueryHandler->shouldReceive('handleQuery')->once()->andReturn($piHearingResult);

        $sut = new PreviousHearingData($mockQueryHandler);

        $this->assertEquals($expectedOutput, $sut->provide($publication, new \ArrayObject($input)));
    }
}
