<?php

declare(strict_types=1);

/**
 * Create Transport Manager Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Tm;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Tm\Create as CreateTransportManager;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Tm\Create as Cmd;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress as SaveAddressCmd;
use Dvsa\Olcs\Api\Domain\Command\Person\Create as CreatePersonCmd;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\Queue\Queue;

/**
 * Create Transport Manager Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateTransportManager();
        $this->mockRepo('TransportManager', TransportManagerRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetailsEntity::CONTACT_TYPE_TRANSPORT_MANAGER,
            TransportManagerEntity::TRANSPORT_MANAGER_STATUS_CURRENT,
            TransportManagerEntity::TRANSPORT_MANAGER_TYPE_EXTERNAL
        ];

        $this->references = [
            PersonEntity::class => [
                5 => m::mock(PersonEntity::class)
            ],
            ContactDetailsEntity::class => [
                2 => m::mock(ContactDetailsEntity::class)
            ],
            ContactDetailsEntity::class => [
                4 => m::mock(ContactDetailsEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $this->mockAuthService();

        $workAddressResult = new Result();
        $workAddressResult->addId('address', 1);
        $workAddressResult->addId('contactDetails', 2);

        $this->expectedSideEffect(
            SaveAddressCmd::class,
            [
                'id'           => null,
                'version'      => null,
                'addressLine1' => 'wAddressLine1',
                'addressLine2' => 'wAddressLine2',
                'addressLine3' => 'wAddressLine3',
                'addressLine4' => 'wAddressLine4',
                'town'         => 'wTown',
                'postcode'     => 'wPostcode',
                'countryCode'  => 'wCountryCode',
                'contactType'  => 'ct_tm'
            ],
            $workAddressResult
        );

        $homeAddressResult = new Result();
        $homeAddressResult->addId('address', 3);
        $homeAddressResult->addId('contactDetails', 4);

        $this->expectedSideEffect(
            SaveAddressCmd::class,
            [
                'id'           => null,
                'version'      => null,
                'addressLine1' => 'hAddressLine1',
                'addressLine2' => 'hAddressLine2',
                'addressLine3' => 'hAddressLine3',
                'addressLine4' => 'hAddressLine4',
                'town'         => 'hTown',
                'postcode'     => 'hPostcode',
                'countryCode'  => 'hCountryCode',
                'contactType'  => 'ct_tm'
            ],
            $homeAddressResult
        );
        $personResult = new Result();
        $personResult->addId('person', 5);
        $personResult->addMessage('Person ID 5 created');

        $this->expectedSideEffect(
            CreatePersonCmd::class,
            [
                'firstName'        => 'fname',
                'lastName'         => 'lname',
                'title'            => 'title',
                'birthDate'        => '2015-01-01',
                'birthPlace'       => 'bplace'
            ],
            $personResult
        );

        $command = Cmd::create(
            [
                'homeAddressLine1' => 'hAddressLine1',
                'homeAddressLine2' => 'hAddressLine2',
                'homeAddressLine3' => 'hAddressLine3',
                'homeAddressLine4' => 'hAddressLine4',
                'homeTown'         => 'hTown',
                'homePostcode'     => 'hPostcode',
                'homeCountryCode'  => 'hCountryCode',
                'workAddressLine1' => 'wAddressLine1',
                'workAddressLine2' => 'wAddressLine2',
                'workAddressLine3' => 'wAddressLine3',
                'workAddressLine4' => 'wAddressLine4',
                'workTown'         => 'wTown',
                'workPostcode'     => 'wPostcode',
                'workCountryCode'  => 'wCountryCode',
                'emailAddress'     => 'email@address.com',
                'firstName'        => 'fname',
                'lastName'         => 'lname',
                'title'            => 'title',
                'birthDate'        => '2015-01-01',
                'birthPlace'       => 'bplace'
            ]
        );

        $mockContactDetails = m::mock()
            ->shouldReceive('updateContactDetailsWithPersonAndEmailAddress')
            ->with(m::type(PersonEntity::class), 'email@address.com')
            ->once()
            ->getMock();

        $this->repoMap['ContactDetails']
            ->shouldReceive('fetchById')
            ->with(4)
            ->once()
            ->andReturn($mockContactDetails)
            ->shouldReceive('save')
            ->with($mockContactDetails)
            ->once()
            ->getMock();

        $transportManager = null;

        $this->repoMap['TransportManager']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(TransportManagerEntity::class))
            ->andReturnUsing(
                function (TransportManagerEntity $tm) use (&$transportManager) {
                    $tm->setId(111);
                    $transportManager = $tm;
                }
            );

        $this->expectedQueueSideEffect(111, Queue::TYPE_UPDATE_NYSIIS_TM_NAME, ['id' => 111]);

        $result = $this->sut->handleCommand($command);

        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['transportManager']);
        $this->assertEquals(1, $res['id']['workAddress']);
        $this->assertEquals(2, $res['id']['workContactDetails']);
        $this->assertEquals(3, $res['id']['homeAddress']);
        $this->assertEquals(4, $res['id']['homeContactDetails']);
    }

    protected function mockAuthService(): void
    {
        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);

        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);
        $mockUser->setTeam($mockTeam);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);
    }
}
