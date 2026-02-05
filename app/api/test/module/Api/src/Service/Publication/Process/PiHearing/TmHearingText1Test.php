<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\PiHearing;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\PiHearing\TmHearingText1;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class TmHearingText1Test
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TmHearingText1Test extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the TmHearingText1 filter')]
    public function testProcess(): void
    {
        $sut = new TmHearingText1();

        $pi = 99;
        $venueOther = 'Pi Venue Information';
        $hearingDate = '12 May 2014';
        $previousHearingDate = '3 April 2014';
        $hearingTime = '14:30';
        $previousPublication = 6830;
        $caseId = 84;
        $transportManagerName = 'transport manager name';

        $input = [
            'id' => $pi,
            'previousPublication' => $previousPublication,
            'previousHearing' => $previousHearingDate,
            'venueOther' => $venueOther,
            'formattedHearingDate' => $hearingDate,
            'formattedHearingTime' => $hearingTime,
            'transportManagerName' => $transportManagerName
        ];

        $publicationLink = m::mock(PublicationLink::class)->makePartial();
        $publicationLink->shouldReceive('getPi->getCase->getId')->andReturn($caseId);

        $expectedString = sprintf(
            'TM Public Inquiry (Case ID: %s, Public Inquiry ID: %s) for %s to be held at %s,
    on %s commencing at %s (Previous Publication:'
            . '(%s)) Previous hearing on %s was adjourned.',
            $caseId,
            $pi,
            $transportManagerName,
            $venueOther,
            $hearingDate,
            $hearingTime,
            $previousPublication,
            $previousHearingDate
        );

        $output = $sut->process($publicationLink, new ImmutableArrayObject($input));
        $this->assertEquals($expectedString, $output->getText1());
    }
}
