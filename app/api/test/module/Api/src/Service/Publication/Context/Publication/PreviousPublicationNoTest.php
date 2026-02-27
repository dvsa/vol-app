<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Publication;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\Publication\PreviousPublicationNo;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PreviousPublicationNoTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PreviousPublicationNoTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the previous hearing date filter')]
    public function testProvide(): void
    {
        $pi = 99;
        $pubType = 'A&D';
        $trafficArea = 'trafficArea';
        $currentPublicationNo = 889;
        $previousPublicationNo = 888;

        $output = [
            'previousPublication' => $previousPublicationNo,
        ];

        $expectedOutput = new \ArrayObject($output);

        $publication = m::mock(PublicationLink::class);
        $publication->shouldReceive('getPi->getId')->once()->andReturn($pi);
        $publication->shouldReceive('getPublication->getPubType')->once()->andReturn($pubType);
        $publication->shouldReceive('getPublication->getPublicationNo')->once()->andReturn($currentPublicationNo);
        $publication->shouldReceive('getTrafficArea')->once()->andReturn($trafficArea);

        $previousPublicationResult = m::mock()
            ->shouldReceive('serialize')
            ->andReturn(['publication' => ['publicationNo' => $previousPublicationNo]])
            ->once()
            ->getMock();

        $mockQueryHandler = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class);
        $mockQueryHandler->shouldReceive('handleQuery')->once()->andReturn($previousPublicationResult);

        $sut = new PreviousPublicationNo($mockQueryHandler);

        $this->assertEquals($expectedOutput, $sut->provide($publication, new \ArrayObject()));
    }
}
