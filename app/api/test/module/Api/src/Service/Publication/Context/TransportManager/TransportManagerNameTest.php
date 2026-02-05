<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\TransportManager;

use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\Context\TransportManager\TransportManagerName;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class TransportManagerNameTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TransportManagerNameTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the transport manager name filter')]
    public function testProvideWithTitle(): void
    {
        $title = 'title';
        $forename = 'forename';
        $familyName = 'family name';

        $output = [
            'transportManagerName' => $title . ' ' . $forename . ' ' . $familyName
        ];

        $expectedOutput = new \ArrayObject($output);

        $mockTitle = m::mock(RefData::class)->makePartial();
        $mockTitle->shouldReceive('getDescription')->andReturn($title);

        $mockPerson = m::mock(PersonEntity::class);
        $mockPerson->shouldReceive('getTitle')->once()->andReturn($mockTitle);
        $mockPerson->shouldReceive('getForename')->once()->andReturn($forename);
        $mockPerson->shouldReceive('getFamilyName')->once()->andReturn($familyName);

        $publication = m::mock(PublicationLink::class);
        $publication->shouldReceive('getTransportManager->getHomeCd->getPerson')->andReturn($mockPerson);

        $sut = new TransportManagerName(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));
        $this->assertEquals($expectedOutput, $sut->provide($publication, new \ArrayObject()));
    }

    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the transport manager name filter')]
    public function testProvideNoTitle(): void
    {
        $forename = 'forename';
        $familyName = 'family name';

        $output = [
            'transportManagerName' => $forename . ' ' . $familyName
        ];

        $expectedOutput = new \ArrayObject($output);

        $mockPerson = m::mock(PersonEntity::class);
        $mockPerson->shouldReceive('getTitle')->once()->andReturnNull();
        $mockPerson->shouldReceive('getForename')->once()->andReturn($forename);
        $mockPerson->shouldReceive('getFamilyName')->once()->andReturn($familyName);

        $publication = m::mock(PublicationLink::class);
        $publication->shouldReceive('getTransportManager->getHomeCd->getPerson')->andReturn($mockPerson);

        $sut = new TransportManagerName(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));
        $this->assertEquals($expectedOutput, $sut->provide($publication, new \ArrayObject()));
    }
}
