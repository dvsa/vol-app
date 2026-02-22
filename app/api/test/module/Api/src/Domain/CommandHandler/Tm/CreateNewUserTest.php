<?php

declare(strict_types=1);

/**
 * Create New User Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Tm;

use Dvsa\Contracts\Auth\Exceptions\ClientException;
use Dvsa\Olcs\Api\Domain\Command\Email\SendTmUserCreated as SendTmUserCreatedDto;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUserTemporaryPassword as SendUserTemporaryPasswordDto;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Tm\CreateNewUser;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\User\Role;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Auth\Service\PasswordService;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Tm\CreateNewUser as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create New User Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateNewUserTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var CreateNewUser
     */
    protected $sut;

    /**
     * @var ValidatableAdapterInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $mockedAdapter;

    public function setUp(): void
    {
        $mockedPasswordService = m::mock(PasswordService::class);
        $mockedPasswordService->shouldReceive('generatePassword')->andReturn('GENERATED_PASSWORD');

        $this->mockedAdapter = m::mock(ValidatableAdapterInterface::class);

        $this->sut = new CreateNewUser($mockedPasswordService, $this->mockedAdapter);
        $this->mockRepo('User', Repository\User::class);
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('ContactDetails', Repository\ContactDetails::class);
        $this->mockRepo('Person', Repository\Person::class);
        $this->mockRepo('TransportManager', Repository\TransportManager::class);
        $this->mockRepo('TransportManagerApplication', Repository\TransportManagerApplication::class);
        $this->mockRepo('Address', Repository\Address::class);
        $this->mockRepo('Role', Repository\Role::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetails::CONTACT_TYPE_TRANSPORT_MANAGER,
            TransportManager::TRANSPORT_MANAGER_STATUS_CURRENT,
            TransportManagerApplication::STATUS_INCOMPLETE,
            TransportManagerApplication::STATUS_POSTAL_APPLICATION
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'application' => 111,
            'username' => null,
            'emailAddress' => null,
            'hasEmail' => 'N',
            'firstName' => 'Bob',
            'familyName' => 'Barker',
            'birthDate' => '1965-01-01'
        ];

        $command = Cmd::create($data);

        $mockApplication = m::mock(Application::class);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->once()
            ->with(111)
            ->andReturn($mockApplication);

        $savedPerson = null;
        $this->repoMap['Person']->shouldReceive('save')
            ->once()
            ->with(m::type(Person::class))
            ->andReturnUsing(
                function (Person $person) use (&$savedPerson) {
                    $savedPerson = $person;
                    $person->setId(222);
                    $this->assertEquals('Bob', $person->getForename());
                    $this->assertEquals('Barker', $person->getFamilyName());
                    $this->assertEquals('1965-01-01', $person->getBirthDate()->format('Y-m-d'));
                }
            );

        $addressCount = 0;
        $this->repoMap['Address']->shouldReceive('save')
            ->twice()
            ->with(m::type(Address::class))
            ->andReturnUsing(
                function (Address $address) use (&$addressCount) {
                    $address->setId('33' . ++$addressCount);
                }
            );

        $cdCount = 0;
        $savedContactDetails = [];
        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->twice()
            ->with(m::type(ContactDetails::class))
            ->andReturnUsing(
                function (ContactDetails $contactDetails) use (&$savedPerson, &$cdCount, &$savedContactDetails) {
                    $savedContactDetails[] = $contactDetails;
                    $contactDetails->setId('44' . ++$cdCount);
                    if ($cdCount === 1) {
                        $this->assertNull($contactDetails->getEmailAddress());
                        $this->assertSame($savedPerson, $contactDetails->getPerson());
                    }
                    $this->assertEquals('33' . $cdCount, $contactDetails->getAddress()->getId());
                }
            );

        $savedTm = null;
        $this->repoMap['TransportManager']->shouldReceive('save')
            ->once()
            ->with(m::type(TransportManager::class))
            ->andReturnUsing(
                function (TransportManager $transportManager) use (&$savedContactDetails, &$savedTm) {
                    $savedTm = $transportManager;
                    $transportManager->setId(555);
                    $this->assertSame($savedContactDetails[0], $transportManager->getHomeCd());
                    $this->assertSame($savedContactDetails[1], $transportManager->getWorkCd());
                    $this->assertSame(
                        $this->refData[TransportManager::TRANSPORT_MANAGER_STATUS_CURRENT],
                        $transportManager->getTmStatus()
                    );
                }
            );

        $this->repoMap['TransportManagerApplication']->shouldReceive('save')
            ->once()
            ->with(m::type(TransportManagerApplication::class))
            ->andReturnUsing(
                function (TransportManagerApplication $transportManagerApplication) use (&$savedTm, &$mockApplication) {
                    $transportManagerApplication->setId(666);
                    $this->assertSame($savedTm, $transportManagerApplication->getTransportManager());
                    $this->assertSame($mockApplication, $transportManagerApplication->getApplication());
                    $this->assertEquals('A', $transportManagerApplication->getAction());
                    $this->assertSame(
                        $this->refData[TransportManagerApplication::STATUS_POSTAL_APPLICATION],
                        $transportManagerApplication->getTmApplicationStatus()
                    );
                }
            );

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            [
                'id' => 111,
                'section' => 'transportManagers'
            ],
            new Result()
        );

        $this->expectedQueueSideEffect(555, Queue::TYPE_UPDATE_NYSIIS_TM_NAME, ['id' => 555]);

        $response = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'personId' => 222,
                'contactDetailsId' => 441,
                'transportManagerId' => 555,
                'transportManagerApplicationId' => 666
            ],
            'messages' => [
                'New transport manager created'
            ]
        ];

        $this->assertEquals($expected, $response->toArray());
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testHandleCommandWithEmail(): void
    {
        $data = [
            'application' => 111,
            'username' => 'Foo',
            'emailAddress' => 'foo@bar.com',
            'hasEmail' => 'Y',
            'firstName' => 'Bob',
            'familyName' => 'Barker',
            'birthDate' => '1965-01-01'
        ];

        $command = Cmd::create($data);

        $mockApplication = m::mock(Application::class);
        $mockApplication->shouldReceive('getLicence->getOrganisation')->with()->andReturn('ORG1');

        $this->mockedAdapter->shouldReceive('register')->once();

        $this->repoMap['User']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchByLoginId')
            ->with('Foo')
            ->andReturn([])
            ->shouldReceive('enableSoftDeleteable')
            ->once();

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->once()
            ->with(111)
            ->andReturn($mockApplication);

        $savedPerson = null;
        $this->repoMap['Person']->shouldReceive('save')
            ->once()
            ->with(m::type(Person::class))
            ->andReturnUsing(
                function (Person $person) use (&$savedPerson) {
                    $savedPerson = $person;
                    $person->setId(222);
                    $this->assertEquals('Bob', $person->getForename());
                    $this->assertEquals('Barker', $person->getFamilyName());
                    $this->assertEquals('1965-01-01', $person->getBirthDate()->format('Y-m-d'));
                }
            );

        $addressCount = 0;
        $this->repoMap['Address']->shouldReceive('save')
            ->twice()
            ->with(m::type(Address::class))
            ->andReturnUsing(
                function (Address $address) use (&$addressCount) {
                    $address->setId('33' . ++$addressCount);
                }
            );

        $cdCount = 0;
        $savedContactDetails = [];
        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->twice()
            ->with(m::type(ContactDetails::class))
            ->andReturnUsing(
                function (ContactDetails $contactDetails) use (&$savedPerson, &$cdCount, &$savedContactDetails) {
                    $savedContactDetails[] = $contactDetails;
                    $contactDetails->setId('44' . ++$cdCount);
                    if ($cdCount === 1) {
                        $this->assertEquals('foo@bar.com', $contactDetails->getEmailAddress());
                        $this->assertSame($savedPerson, $contactDetails->getPerson());
                    }
                    $this->assertEquals('33' . $cdCount, $contactDetails->getAddress()->getId());
                }
            );

        $savedTm = null;
        $this->repoMap['TransportManager']->shouldReceive('save')
            ->once()
            ->with(m::type(TransportManager::class))
            ->andReturnUsing(
                function (TransportManager $transportManager) use (&$savedContactDetails, &$savedTm) {
                    $savedTm = $transportManager;
                    $transportManager->setId(555);
                    $this->assertSame($savedContactDetails[0], $transportManager->getHomeCd());
                    $this->assertSame($savedContactDetails[1], $transportManager->getWorkCd());
                    $this->assertSame(
                        $this->refData[TransportManager::TRANSPORT_MANAGER_STATUS_CURRENT],
                        $transportManager->getTmStatus()
                    );
                }
            );

        $savedTma = null;
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')
            ->once()
            ->with(m::type(TransportManagerApplication::class))
            ->andReturnUsing(
                function (TransportManagerApplication $transportManagerApplication) use (
                    &$savedTm,
                    &$savedTma,
                    &$mockApplication
                ) {
                    $savedTma = $transportManagerApplication;
                    $transportManagerApplication->setId(666);
                    $this->assertSame($savedTm, $transportManagerApplication->getTransportManager());
                    $this->assertSame($mockApplication, $transportManagerApplication->getApplication());
                    $this->assertEquals('A', $transportManagerApplication->getAction());
                    $this->assertSame(
                        $this->refData[TransportManagerApplication::STATUS_INCOMPLETE],
                        $transportManagerApplication->getTmApplicationStatus()
                    );
                }
            );

        $role = m::mock(Role::class)->makePartial();
        $role->setRole(Role::ROLE_OPERATOR_TM);

        $this->repoMap['Role']->shouldReceive('fetchOneByRole')
            ->with(Role::ROLE_OPERATOR_TM)
            ->andReturn($role);

        $this->repoMap['User']->shouldReceive('save')->once()
            ->with(m::type(User::class))
            ->andReturnUsing(
                function (User $user) use (&$savedContactDetails, &$savedTm, &$savedTma) {
                    $userId = 777;
                    $user->setId($userId);
                    $this->assertSame($savedContactDetails[0], $user->getContactDetails());
                    $this->assertEquals('Foo', $user->getLoginId());
                    $this->assertCount(1, $user->getRoles());
                    $this->assertEquals(Role::ROLE_OPERATOR_TM, $user->getRoles()->first()->getRole());
                    $this->assertSame($savedTm, $user->getTransportManager());

                    $this->expectedSideEffect(
                        SendTmUserCreatedDto::class,
                        [
                            'user' => $userId,
                            'tma' => $savedTma->getId()
                        ],
                        new Result()
                    );

                    $this->expectedSideEffect(
                        SendUserTemporaryPasswordDto::class,
                        [
                            'user' => $userId,
                            'password' => 'GENERATED_PASSWORD',
                        ],
                        new Result()
                    );
                }
            );

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            [
                'id' => 111,
                'section' => 'transportManagers'
            ],
            new Result()
        );

        $this->expectedQueueSideEffect(555, Queue::TYPE_UPDATE_NYSIIS_TM_NAME, ['id' => 555]);

        $response = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'personId' => 222,
                'contactDetailsId' => 441,
                'transportManagerId' => 555,
                'transportManagerApplicationId' => 666,
                'userId' => 777
            ],
            'messages' => [
                'New user created',
                'New transport manager created'
            ]
        ];

        $this->assertEquals($expected, $response->toArray());
    }

    public function testHandleCommandWithEmailWithUsernameInUse(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'application' => 111,
            'username' => 'Foo',
            'emailAddress' => 'foo@bar.com',
            'hasEmail' => 'Y',
            'firstName' => 'Bob',
            'familyName' => 'Barker',
            'birthDate' => '1965-01-01'
        ];

        $command = Cmd::create($data);

        $this->repoMap['User']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchByLoginId')
            ->once()
            ->with('Foo')
            ->andReturn(['foo'])
            ->shouldReceive('enableSoftDeleteable')
            ->once();

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithEmailWithMissingEmail(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'application' => 111,
            'username' => 'Foo',
            'emailAddress' => '',
            'hasEmail' => 'Y',
            'firstName' => 'Bob',
            'familyName' => 'Barker',
            'birthDate' => '1965-01-01'
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithEmailWithMissingEmailAndUsername(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'application' => 111,
            'username' => '',
            'emailAddress' => '',
            'hasEmail' => 'Y',
            'firstName' => 'Bob',
            'familyName' => 'Barker',
            'birthDate' => '1965-01-01'
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithExceptionCreatingUserInAuthService(): void
    {
        $data = [
            'application' => 111,
            'username' => 'Foo',
            'emailAddress' => 'foo@bar.com',
            'hasEmail' => 'Y',
            'firstName' => 'Bob',
            'familyName' => 'Barker',
            'birthDate' => '1965-01-01'
        ];

        $command = Cmd::create($data);

        $mockApplication = m::mock(Application::class);
        $mockApplication->shouldReceive('getLicence->getOrganisation')->with()->andReturn('ORG1');

        $this->mockedAdapter->shouldReceive('register')->andThrow(ClientException::class, '', 1);

        $this->repoMap['User']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchByLoginId')
            ->with('Foo')
            ->andReturn([])
            ->shouldReceive('enableSoftDeleteable')
            ->once()
            ->shouldReceive('delete')
            ->once();

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->once()
            ->with(111)
            ->andReturn($mockApplication);

        $savedPerson = null;
        $this->repoMap['Person']->shouldReceive('save')
            ->once()
            ->with(m::type(Person::class))
            ->andReturnUsing(
                function (Person $person) use (&$savedPerson) {
                    $savedPerson = $person;
                    $person->setId(222);
                    $this->assertEquals('Bob', $person->getForename());
                    $this->assertEquals('Barker', $person->getFamilyName());
                    $this->assertEquals('1965-01-01', $person->getBirthDate()->format('Y-m-d'));
                }
            );

        $addressCount = 0;
        $this->repoMap['Address']->shouldReceive('save')
            ->twice()
            ->with(m::type(Address::class))
            ->andReturnUsing(
                function (Address $address) use (&$addressCount) {
                    $address->setId('33' . ++$addressCount);
                }
            );

        $cdCount = 0;
        $savedContactDetails = [];
        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->twice()
            ->with(m::type(ContactDetails::class))
            ->andReturnUsing(
                function (ContactDetails $contactDetails) use (&$savedPerson, &$cdCount, &$savedContactDetails) {
                    $savedContactDetails[] = $contactDetails;
                    $contactDetails->setId('44' . ++$cdCount);
                    if ($cdCount === 1) {
                        $this->assertEquals('foo@bar.com', $contactDetails->getEmailAddress());
                        $this->assertSame($savedPerson, $contactDetails->getPerson());
                    }
                    $this->assertEquals('33' . $cdCount, $contactDetails->getAddress()->getId());
                }
            );

        $savedTm = null;
        $this->repoMap['TransportManager']->shouldReceive('save')
            ->once()
            ->with(m::type(TransportManager::class))
            ->andReturnUsing(
                function (TransportManager $transportManager) use (&$savedContactDetails, &$savedTm) {
                    $savedTm = $transportManager;
                    $transportManager->setId(555);
                    $this->assertSame($savedContactDetails[0], $transportManager->getHomeCd());
                    $this->assertSame($savedContactDetails[1], $transportManager->getWorkCd());
                    $this->assertSame(
                        $this->refData[TransportManager::TRANSPORT_MANAGER_STATUS_CURRENT],
                        $transportManager->getTmStatus()
                    );
                }
            );

        $savedTma = null;
        $this->repoMap['TransportManagerApplication']->shouldReceive('save')
            ->once()
            ->with(m::type(TransportManagerApplication::class))
            ->andReturnUsing(
                function (TransportManagerApplication $transportManagerApplication) use (
                    &$savedTm,
                    &$savedTma,
                    &$mockApplication
                ) {
                    $savedTma = $transportManagerApplication;
                    $transportManagerApplication->setId(666);
                    $this->assertSame($savedTm, $transportManagerApplication->getTransportManager());
                    $this->assertSame($mockApplication, $transportManagerApplication->getApplication());
                    $this->assertEquals('A', $transportManagerApplication->getAction());
                    $this->assertSame(
                        $this->refData[TransportManagerApplication::STATUS_INCOMPLETE],
                        $transportManagerApplication->getTmApplicationStatus()
                    );
                }
            );

        $role = m::mock(Role::class)->makePartial();
        $role->setRole(Role::ROLE_OPERATOR_TM);

        $this->repoMap['Role']->shouldReceive('fetchOneByRole')
            ->with(Role::ROLE_OPERATOR_TM)
            ->andReturn($role);

        $this->repoMap['User']->shouldReceive('save')->once()
            ->with(m::type(User::class))
            ->andReturnUsing(
                function (User $user) use (&$savedContactDetails, &$savedTm, &$savedTma) {
                    $userId = 777;
                    $user->setId($userId);
                    $this->assertSame($savedContactDetails[0], $user->getContactDetails());
                    $this->assertEquals('Foo', $user->getLoginId());
                    $this->assertCount(1, $user->getRoles());
                    $this->assertEquals(Role::ROLE_OPERATOR_TM, $user->getRoles()->first()->getRole());
                    $this->assertSame($savedTm, $user->getTransportManager());
                }
            );

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            [
                'id' => 111,
                'section' => 'transportManagers'
            ],
            new Result()
        );

        $this->expectedQueueSideEffect(555, Queue::TYPE_UPDATE_NYSIIS_TM_NAME, ['id' => 555]);

        $this->expectException(\RuntimeException::class);

        $this->sut->handleCommand($command);
    }
}
