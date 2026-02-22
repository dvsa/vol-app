<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\MyAccount;

use Dvsa\Olcs\Api\Rbac\JWTIdentityProvider;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\MyAccount\UpdateMyAccount as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\MyAccount\UpdateMyAccount;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Domain\Repository\Person;
use Dvsa\Olcs\Api\Domain\Repository\PhoneContact;
use Dvsa\Olcs\Api\Domain\Repository\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as PhoneContactEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use ReflectionClass;

/**
 * Update MyAccount Test
 */
class UpdateMyAccountTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateMyAccount();
        $this->mockRepo('User', User::class);
        $this->mockRepo('ContactDetails', ContactDetails::class);
        $this->mockRepo('PhoneContact', PhoneContact::class);
        $this->mockRepo('Address', Address::class);
        $this->mockRepo('Person', Person::class);

        $mockConfig = [
            'auth' => [
                'identity_provider' => JWTIdentityProvider::class
            ]
        ];

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
            AuthorizationService::class => m::mock(AuthorizationService::class),
            ValidatableAdapterInterface::class => m::mock(ValidatableAdapterInterface::class),
            'config' => $mockConfig
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetailsEntity::CONTACT_TYPE_USER,
            PhoneContactEntity::TYPE_PRIMARY,
            'title_mr',
        ];

        $this->references = [
            Country::class => [
                'GB' => m::mock(Country::class)
            ],
            Team::class => [
                '1' => m::mock(Team::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithNewContactDetails(): void
    {
        $userId = 1;

        $data = [
            'id' => 111,
            'version' => 1,
            'team' => 1,
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'person' => [
                    'title' => m::mock(RefData::class),
                    'forename' => 'updated forename',
                    'familyName' => 'updated familyName',
                    'birthDate' => '1975-12-12',
                ],
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => m::mock(Country::class),
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '111',
                    ],
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '222',
                    ]
                ],
            ],
        ];

        $command = Cmd::create($data);

        /** @var TeamEntity $user */
        $team = m::mock(Team::class)->makePartial();

        /** @var UserEntity $user */
        $user = m::mock(UserEntity::class)->makePartial();
        $user->setId($userId);
        $user->setLoginId('login_id');
        $user->setTeam($team);
        $user->setPid('some-pid');

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($user, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_LOCAL_AUTHORITY);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $this->mockedSmServices[ValidatableAdapterInterface::class]->shouldReceive('changeAttribute')
            ->once()
            ->with('login_id', 'email', 'test1@test.me')
            ->andReturnTrue();

        $this->repoMap['User']->shouldReceive('fetchById')
            ->once()
            ->with($userId, Query::HYDRATE_OBJECT, 1)
            ->andReturn($user)
            ->shouldReceive('populateRefDataReference')
            ->once()
            ->andReturn($data);

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['contactDetails'])
            ->andReturn($data['contactDetails']);

        /** @var UserEntity $savedUser */
        $savedUser = null;

        $this->repoMap['User']->shouldReceive('save')
            ->once()
            ->with(m::type(UserEntity::class))
            ->andReturnUsing(
                function (UserEntity $user) use (&$savedUser) {
                    $savedUser = $user;
                }
            );

        $this->expectedUserCacheClear([$userId]);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId,
            ],
            'messages' => [
                'User updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ContactDetailsEntity::class, $savedUser->getContactDetails());
        $this->assertEquals(
            $data['contactDetails']['emailAddress'],
            $savedUser->getContactDetails()->getEmailAddress()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommandWithUpdatedContactDetails')]
    public function testHandleCommandWithUpdatedContactDetails(mixed $data, mixed $isInternal): void
    {
        $this->setupIsInternalUser($isInternal);

        $userId = 1;

        /** @var User $mockUser */
        $mockUser = m::mock(UserEntity::class)->makePartial();
        $mockUser->setId($userId);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create($data);

        $savedPerson = null;
        $this->repoMap['Person']
            ->shouldReceive('save')
            ->with(m::type(PersonEntity::class))
            ->andReturnUsing(
                function (PersonEntity $person) use ($data, &$savedPerson) {
                    $savedPerson = $person;
                    $dataPerson = $data['contactDetails']['person'];
                    $this->assertSame($dataPerson['forename'], $savedPerson->getForename());
                    $this->assertSame($dataPerson['familyName'], $savedPerson->getFamilyName());
                    $this->assertSame($this->refData[$dataPerson['title']], $savedPerson->getTitle());
                    $this->assertEquals(new \DateTime($dataPerson['birthDate']), $savedPerson->getBirthDate());
                    $savedPerson->setId(753);
                }
            )
            ->times($isInternal);

        $savedAddress = null;
        $this->repoMap['Address']
            ->shouldReceive('save')
            ->with(m::type(AddressEntity::class))
            ->andReturnUsing(
                function (AddressEntity $address) use ($data, &$savedAddress) {
                    $savedAddress = $address;
                    $dataAddress = $data['contactDetails']['address'];
                    $this->assertSame($dataAddress['addressLine1'], $savedAddress->getAddressLine1());
                    $this->assertSame($dataAddress['addressLine2'], $savedAddress->getAddressLine2());
                    $this->assertSame($dataAddress['addressLine3'], $savedAddress->getAddressLine3());
                    $this->assertSame($dataAddress['addressLine4'], $savedAddress->getAddressLine4());
                    $this->assertSame($dataAddress['town'], $savedAddress->getTown());
                    $this->assertSame($dataAddress['postcode'], $savedAddress->getPostcode());
                    $this->assertSame(
                        $this->references[Country::class][$dataAddress['countryCode']],
                        $savedAddress->getCountryCode()
                    );
                    $savedAddress->setId(754);
                }
            )
            ->once();

        $existingPhoneContact = m::mock(PhoneContactEntity::class);

        $this->repoMap['PhoneContact']
            ->shouldReceive('delete')
            ->with($existingPhoneContact)
            ->once()
            ->shouldReceive('save')
            ->with(m::type(PhoneContactEntity::class))
            ->andReturnUsing(
                function (PhoneContactEntity $phoneContact) use ($data) {
                    $dataPhoneContacts = $data['contactDetails']['phoneContacts'][0];
                    $this->assertSame($dataPhoneContacts['phoneNumber'], $phoneContact->getPhoneNumber());
                    $this->assertSame(
                        $this->refData[PhoneContactEntity::TYPE_PRIMARY],
                        $phoneContact->getPhoneContactType()
                    );
                    $phoneContact->setId(755);
                }
            )
            ->once();

        /** @var ContactDetailsEntity $contactDetails */
        $contactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $contactDetails->shouldReceive('setPerson')
            ->with(m::type(PersonEntity::class))
            ->times($isInternal)
            ->shouldReceive('setAddress')
            ->with(m::type(AddressEntity::class))
            ->once()
            ->shouldReceive('setEmailAddress')
            ->with('test1@test.me')
            ->once()
            ->shouldReceive('getPhoneContacts')
            ->andReturn([$existingPhoneContact])
            ->once()
            ->shouldReceive('getPerson')
            ->andReturnNull()
            ->times($isInternal)
            ->shouldReceive('getAddress')
            ->andReturnNull()
            ->once()
            ->getMock();

        /** @var TeamEntity $user */
        $team = m::mock(Team::class)->makePartial();

        /** @var UserEntity $user */
        $user = m::mock(UserEntity::class)->makePartial();
        $user->setId($userId);
        $user->setLoginId('login_id');
        $user->setTeam($team);
        $user->setContactDetails($contactDetails);
        $user->setPid('some-pid');

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($user, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_LOCAL_AUTHORITY);

        $this->repoMap['User']->shouldReceive('fetchById')
            ->once()
            ->with($userId, Query::HYDRATE_OBJECT, 1)
            ->andReturn($user)
            ->shouldReceive('populateRefDataReference')
            ->once()
            ->andReturn($data);

        /** @var UserEntity $savedUser */
        $savedUser = null;

        $this->repoMap['User']->shouldReceive('save')
            ->once()
            ->with(m::type(UserEntity::class))
            ->andReturnUsing(
                function (UserEntity $user) use (&$savedUser) {
                    $savedUser = $user;
                }
            );

        $this->mockedSmServices[ValidatableAdapterInterface::class]->shouldReceive('changeAttribute')
            ->once()
            ->with('login_id', 'email', 'test1@test.me')
            ->andReturnTrue();

        $this->expectedUserCacheClear([$userId]);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId,
            ],
            'messages' => [
                'User updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $contactDetails,
            $savedUser->getContactDetails()
        );
    }

    public static function dpTestHandleCommandWithUpdatedContactDetails(): array
    {
        return [
            [
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'team' => 1,
                    'contactDetails' => [
                        'emailAddress' => 'test1@test.me',
                        'address' => [
                            'addressLine1' => 'a12',
                            'addressLine2' => 'a23',
                            'addressLine3' => 'a34',
                            'addressLine4' => 'a45',
                            'town' => 'town',
                            'postcode' => 'LS1 2AB',
                            'countryCode' => 'GB',
                        ],
                        'phoneContacts' => [
                            [
                                'phoneContactType' => PhoneContactEntity::TYPE_PRIMARY,
                                'phoneNumber' => '111',
                            ],
                        ],
                    ],
                ],
                "isInternal" => 0
            ],
            [
                'data' => [
                    'id' => 111,
                    'version' => 1,
                    'team' => 1,
                    'contactDetails' => [
                        'emailAddress' => 'test1@test.me',
                        'person' => [
                            'title' => 'title_mr',
                            'forename' => 'updated forename',
                            'familyName' => 'updated familyName',
                            'birthDate' => '1975-12-12',
                        ],
                        'address' => [
                            'addressLine1' => 'a12',
                            'addressLine2' => 'a23',
                            'addressLine3' => 'a34',
                            'addressLine4' => 'a45',
                            'town' => 'town',
                            'postcode' => 'LS1 2AB',
                            'countryCode' => 'GB',
                        ],
                        'phoneContacts' => [
                            [
                                'phoneContactType' => PhoneContactEntity::TYPE_PRIMARY,
                                'phoneNumber' => '111',
                            ],
                        ],
                    ],
                ],
                "isInternal" => 1
            ]
        ];
    }

    public function testHandleCommandWithNoPersonTitle(): void
    {
        $userId = 1;

        $data = [
            'id' => 111,
            'version' => 1,
            'team' => 1,
            'contactDetails' => [
                'emailAddress' => 'test1@test.me',
                'person' => [
                    'forename' => 'updated forename',
                    'familyName' => 'updated familyName',
                    'birthDate' => '1975-12-12',
                ],
                'address' => [
                    'addressLine1' => 'a12',
                    'addressLine2' => 'a23',
                    'addressLine3' => 'a34',
                    'addressLine4' => 'a45',
                    'town' => 'town',
                    'postcode' => 'LS1 2AB',
                    'countryCode' => m::mock(Country::class),
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '111',
                    ],
                    [
                        'phoneContactType' => m::mock(RefData::class),
                        'phoneNumber' => '222',
                    ]
                ],
            ],
        ];

        $command = Cmd::create($data);

        /** @var TeamEntity $user */
        $team = m::mock(Team::class)->makePartial();

        /** @var UserEntity $user */
        $user = m::mock(UserEntity::class)->makePartial();
        $user->setId($userId);
        $user->setLoginId('login_id');
        $user->setTeam($team);
        $user->setPid('some-pid');

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($user, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_LOCAL_AUTHORITY);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $this->repoMap['User']->shouldReceive('fetchById')
            ->once()
            ->with($userId, Query::HYDRATE_OBJECT, 1)
            ->andReturn($user)
            ->shouldReceive('populateRefDataReference')
            ->once()
            ->andReturn($data);

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data['contactDetails'])
            ->andReturn($data['contactDetails']);

        /** @var UserEntity $savedUser */
        $savedUser = null;

        $this->repoMap['User']->shouldReceive('save')
            ->once()
            ->with(m::type(UserEntity::class))
            ->andReturnUsing(
                function (UserEntity $user) use (&$savedUser) {
                    $savedUser = $user;
                }
            );

        $this->mockedSmServices[ValidatableAdapterInterface::class]->shouldReceive('changeAttribute')
            ->once()
            ->with('login_id', 'email', 'test1@test.me')
            ->andReturnTrue();

        $this->expectedUserCacheClear([$userId]);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'user' => $userId,
            ],
            'messages' => [
                'User updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ContactDetailsEntity::class, $savedUser->getContactDetails());
        $this->assertEquals(
            $data['contactDetails']['emailAddress'],
            $savedUser->getContactDetails()->getEmailAddress()
        );
    }
}
