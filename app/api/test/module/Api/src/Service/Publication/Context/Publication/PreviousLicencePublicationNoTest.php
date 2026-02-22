<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Publication;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\Publication\PreviousLicencePublicationNo;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PreviousApplicationPublicationNoTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PreviousLicencePublicationNoTest extends MockeryTestCase
{
    public function testProvide(): void
    {
        $pubType = 'PUB_TYPE';
        $trafficArea = 'TRAFFIC_AREA';
        $currentPublicationNo = 889;
        $previousPublicationNo = 888;

        $output = [
            'previousPublication' => $previousPublicationNo,
        ];
        $expectedOutput = new \ArrayObject($output);

        $publicationLink = m::mock(PublicationLink::class);
        $publicationLink->shouldReceive('getPublication->getPubType')->once()->andReturn($pubType);
        $publicationLink->shouldReceive('getTrafficArea')->once()->andReturn($trafficArea);
        $publicationLink->shouldReceive('getPublication->getPublicationNo')->once()->andReturn($currentPublicationNo);
        $publicationLink->shouldReceive('getLicence->getId')->with()->once()->andReturn(1510);

        $previousPublicationResult = m::mock(PublicationLink::class);
        $previousPublicationResult
            ->shouldReceive('getPublication->getPublicationNo')
            ->once()
            ->andReturn($previousPublicationNo);

        $mockQueryHandler = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class);
        $mockQueryHandler->shouldReceive('handleQuery')->once()->andReturn($previousPublicationResult);

        $context = new \ArrayObject();
        $sut = new PreviousLicencePublicationNo($mockQueryHandler);
        $sut->provide($publicationLink, $context);

        $this->assertEquals($expectedOutput, $context);
    }
}
