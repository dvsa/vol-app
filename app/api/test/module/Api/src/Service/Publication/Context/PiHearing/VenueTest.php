<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\PiHearing;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Helper\FormatAddress;
use Dvsa\Olcs\Api\Service\Publication\Context\PiHearing\Venue;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class VenueTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class VenueTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the pi venue filter')]
    public function testProvide(): void
    {
        $venueAddress = 'al1, al2, Town, pc';
        $venueName = 'venue name';
        $venueId = 99;

        $input = [
            'venue' => $venueId
        ];

        $output = [
            'venue' => $venueId,
            'venueOther' => $venueName  . ', ' . $venueAddress
        ];

        $expectedOutput = new \ArrayObject($output);

        $venueResult = m::mock()
            ->shouldReceive('serialize')
            ->andReturn(
                [
                    'name' => $venueName,
                    'address' => [
                        'addressLine1' => 'al1',
                        'addressLine2' => 'al2',
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => 'Town',
                        'postcode' => 'pc'
                    ]
                ]
            )
            ->once()
            ->getMock();

        $mockQueryHandler = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class);
        $mockQueryHandler->shouldReceive('handleQuery')->once()->andReturn($venueResult);

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $mockAddressFormatter->shouldReceive('format')->once()->andReturn($venueAddress);

        $sut = new Venue($mockQueryHandler);
        $sut->setAddressFormatter($mockAddressFormatter);

        $this->assertEquals($expectedOutput, $sut->provide(new PublicationLink(), new \ArrayObject($input)));
    }
}
