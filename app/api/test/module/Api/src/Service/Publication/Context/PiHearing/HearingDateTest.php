<?php

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\PiHearing;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\HearingDate;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class HearingDateTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class HearingDateTest extends MockeryTestCase
{
    /**
     * @group publicationFilter
     *
     * Test the hearing date filter
     */
    public function testProvideGmt()
    {
        $input = [
            'hearingDate' => '2014-03-16 14:30:00',
        ];

        $output = [
            'hearingDate' => '2014-03-16 14:30:00',
            'formattedHearingDate' => '16 March 2014',
            'formattedHearingTime' => '14:30'
        ];

        $expectedOutput = new \ArrayObject($output);

        $sut = new HearingDate(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));
        $this->assertEquals($expectedOutput, $sut->provide(new PublicationLink(), new \ArrayObject($input)));
    }

    public function testProvideBst()
    {
        $input = [
            'hearingDate' => '2014-04-16 14:30:00',
        ];

        $output = [
            'hearingDate' => '2014-04-16 14:30:00',
            'formattedHearingDate' => '16 April 2014',
            'formattedHearingTime' => '15:30'
        ];

        $expectedOutput = new \ArrayObject($output);

        $sut = new HearingDate(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));
        $this->assertEquals($expectedOutput, $sut->provide(new PublicationLink(), new \ArrayObject($input)));
    }
}
