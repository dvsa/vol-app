<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContactDetails\PhoneContact;

use Dvsa\Olcs\Api\Domain\CommandHandler\ContactDetails\PhoneContact\Create as Handler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact\Create as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\ContactDetails\PhoneContact\Create
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->mockRepo('PhoneContact', Repository\PhoneContact::class);
        $this->mockRepo('ContactDetails', Repository\ContactDetails::class);

        parent::setUp();
    }

    public function test(): void
    {
        $id = 8888;
        $contactDetailsId = 999;
        $phoneContactType = Entity\ContactDetails\PhoneContact::TYPE_PRIMARY;

        $data = [
            'phoneNumber' => 'unit_PhoneNr',
            'phoneContactType' => $phoneContactType,
            'contactDetailsId' => $contactDetailsId,
        ];
        $command = Cmd::create($data);

        //  mock contact details repo
        $mockContactDetailsEntity = m::mock(Entity\ContactDetails\ContactDetails::class);

        $this->repoMap['ContactDetails']->shouldReceive('fetchById')
            ->with($contactDetailsId)
            ->andReturn($mockContactDetailsEntity);

        //  mock phone contact repo
        $mockPhoneContactTypeEntity = m::mock(Entity\System\RefData::class);

        $this->refData = [
            $phoneContactType => $mockPhoneContactTypeEntity,
        ];

        $this->repoMap['PhoneContact']
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (Entity\ContactDetails\PhoneContact $entity) use (
                    $id,
                    $mockPhoneContactTypeEntity,
                    $mockContactDetailsEntity
                ) {
                    static::assertEquals($entity->getPhoneNumber(), 'unit_PhoneNr');
                    static::assertSame($entity->getPhoneContactType(), $mockPhoneContactTypeEntity);
                    static::assertSame($entity->getContactDetails(), $mockContactDetailsEntity);

                    $entity->setId($id);
                }
            );

        //  call & check
        $actual = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['phoneContact' => $id],
            'messages' => ['Phone Contact \'' . $id . '\' created'],
        ];
        static::assertEquals($expected, $actual->toArray());
    }
}
