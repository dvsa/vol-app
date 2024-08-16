<?php

/**
 * Delete Workshop Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Workshop;

use Dvsa\Olcs\Api\Domain\Repository\Address;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Domain\Repository\Workshop;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Workshop\DeleteWorkshop;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Workshop\DeleteWorkshop as Cmd;
use Dvsa\Olcs\Api\Entity\Licence\Workshop as WorkshopEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;

/**
 * Delete Workshop Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteWorkshopTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteWorkshop();
        $this->mockRepo('Workshop', Workshop::class);
        $this->mockRepo('ContactDetails', ContactDetails::class);
        $this->mockRepo('Address', Address::class);
        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $data = [
            'ids' => [111,222]
        ];

        $command = Cmd::create($data);
        $workshopEntity1 = m::mock(WorkshopEntity::class);
        $contactDetails1 = m::mock(ContactDetailsEntity::class);
        $address1 = m::mock(AddressEntity::class);
        $contactDetails1->shouldReceive('getAddress')->andReturn($address1);
        $workshopEntity1->shouldReceive('getContactDetails')->andReturn($contactDetails1);
        $workshopEntity2 = m::mock(WorkshopEntity::class);
        $workshopEntity2->shouldReceive('getContactDetails')->andReturn(null);
        $this->repoMap['Address']->shouldReceive('delete')
                                 ->once()
                                 ->with($address1);
        $this->repoMap['Address']->shouldReceive('refresh')
                                 ->once()
                                 ->with($address1);
        $this->repoMap['ContactDetails']->shouldReceive('delete')->with($contactDetails1);

        $this->repoMap['Workshop']->shouldReceive('fetchById')
            ->with(111)
            ->once()
            ->andReturn($workshopEntity1)
            ->shouldReceive('fetchById')
            ->with(222)
            ->once()
            ->andReturn($workshopEntity2)
            ->shouldReceive('delete')
            ->with($workshopEntity1)
            ->once()
            ->shouldReceive('delete')
            ->with($workshopEntity2)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '2 Workshop(s) removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
