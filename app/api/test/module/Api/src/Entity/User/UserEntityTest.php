<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as OrganisationUserEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\User\Role as RoleEntity;
use Dvsa\Olcs\Api\Entity\User\User as Entity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Mockery as m;
use ReflectionClass;

/**
 * User Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class UserEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var Entity
     */
    protected $sut;

    protected $entityClass = Entity::class;


    public function setUp(): void
    {
        $this->entity = $this->instantiate($this->entityClass);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getUserTypeDataProvider')]
    public function testGetUserType(
        mixed $team,
        mixed $localAuthority,
        mixed $transportManager,
        mixed $partnerContactDetails,
        mixed $expected,
        mixed $expectedIsInternal
    ): void {
        $this->entity->setTeam($team);
        $this->entity->setLocalAuthority($localAuthority);
        $this->entity->setTransportManager($transportManager);
        $this->entity->setPartnerContactDetails($partnerContactDetails);

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($this->entity, $expected);

        $this->assertEquals($expected, $this->entity->getUserType());
        $this->assertEquals($expectedIsInternal, $this->entity->isInternal());
    }

    public static function getUserTypeDataProvider(): array
    {
        $team = m::mock(TeamEntity::class);
        $localAuthority = m::mock(LocalAuthorityEntity::class);
        $transportManager = m::mock(TransportManagerEntity::class);
        $partnerContactDetails = m::mock(ContactDetailsEntity::class);

        return [
            [$team, null, null, null, Entity::USER_TYPE_INTERNAL, true],
            [null, $localAuthority, null, null, Entity::USER_TYPE_LOCAL_AUTHORITY, false],
            [null, null, $transportManager, null, Entity::USER_TYPE_TRANSPORT_MANAGER, false],
            [null, null, null, $partnerContactDetails, Entity::USER_TYPE_PARTNER, false],
            [null, null, null, null, Entity::USER_TYPE_OPERATOR, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCreateInternal')]
    public function testCreateInternal(mixed $role): void
    {
        $roleMock = m::mock(RoleEntity::class)->makePartial();
        $roleMock->setRole($role);

        $data = [
            'loginId' => 'loginId',
            'roles' => [$roleMock],
            'translateToWelsh' => 'N',
            'accountDisabled' => 'Y',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [
                m::mock(OrganisationEntity::class)
            ],
        ];

        $entity = Entity::create('pid', Entity::USER_TYPE_INTERNAL, $data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertInstanceOf(\DateTime::class, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_INTERNAL, $entity->getUserType());
        $this->assertEquals($data['team'], $entity->getTeam());
        $this->assertEquals(null, $entity->getTransportManager());
        $this->assertEquals(null, $entity->getPartnerContactDetails());
        $this->assertEquals(null, $entity->getLocalAuthority());
        $this->assertEquals(0, $entity->getOrganisationUsers()->count());
        $this->assertEquals('DVSA', $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertFalse($entity->isAdministrator());
    }

    public static function dpCreateInternal(): array
    {
        return [
            [RoleEntity::ROLE_SYSTEM_ADMIN],
            [RoleEntity::ROLE_INTERNAL_ADMIN],
            [RoleEntity::ROLE_INTERNAL_CASE_WORKER],
            [RoleEntity::ROLE_INTERNAL_READ_ONLY],
            [RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpUpdateInternal')]
    public function testUpdateInternal(mixed $role): void
    {
        $roleMock = m::mock(RoleEntity::class)->makePartial();
        $roleMock->setRole($role);

        $data = [
            'userType' => Entity::USER_TYPE_INTERNAL,
            'loginId' => 'loginId',
            'roles' => [$roleMock],
            'translateToWelsh' => 'Y',
            'accountDisabled' => 'N',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [
                m::mock(OrganisationEntity::class)
            ],
        ];

        // create an object of different type first
        $entity = Entity::create(
            'pid',
            Entity::USER_TYPE_PARTNER,
            [
                'loginId' => 'currentLoginId',
                'translateToWelsh' => 'N',
                'accountDisabled' => 'Y',
                'team' => m::mock(TeamEntity::class),
                'transportManager' => m::mock(TransportManagerEntity::class),
                'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
                'localAuthority' => m::mock(LocalAuthorityEntity::class),
                'organisations' => [
                    m::mock(OrganisationEntity::class)
                ],
            ]
        );

        // update the entity
        $entity->update($data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertEquals(null, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_INTERNAL, $entity->getUserType());
        $this->assertEquals($data['team'], $entity->getTeam());
        $this->assertEquals(null, $entity->getTransportManager());
        $this->assertEquals(null, $entity->getPartnerContactDetails());
        $this->assertEquals(null, $entity->getLocalAuthority());
        $this->assertEquals(0, $entity->getOrganisationUsers()->count());
        $this->assertEquals('DVSA', $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertFalse($entity->isAdministrator());
    }

    public static function dpUpdateInternal(): array
    {
        return [
            [RoleEntity::ROLE_SYSTEM_ADMIN],
            [RoleEntity::ROLE_INTERNAL_ADMIN],
            [RoleEntity::ROLE_INTERNAL_CASE_WORKER],
            [RoleEntity::ROLE_INTERNAL_READ_ONLY],
            [RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY],
        ];
    }

    public function testCreateTransportManager(): void
    {
        $adminRole = m::mock(RoleEntity::class)->makePartial();
        $adminRole->setRole(RoleEntity::ROLE_OPERATOR_ADMIN);

        $orgName = 'Org Name';
        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setName($orgName);

        $data = [
            'loginId' => 'loginId',
            'roles' => [$adminRole],
            'translateToWelsh' => 'N',
            'accountDisabled' => 'Y',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [$org],
        ];

        $entity = Entity::create('pid', Entity::USER_TYPE_TRANSPORT_MANAGER, $data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertInstanceOf(\DateTime::class, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_TRANSPORT_MANAGER, $entity->getUserType());
        $this->assertEquals(null, $entity->getTeam());
        $this->assertEquals($data['transportManager'], $entity->getTransportManager());
        $this->assertEquals(null, $entity->getPartnerContactDetails());
        $this->assertEquals(null, $entity->getLocalAuthority());
        $this->assertEquals(1, $entity->getOrganisationUsers()->count());
        $this->assertEquals('Y', $entity->getOrganisationUsers()->first()->getIsAdministrator());
        $this->assertEquals($orgName, $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertTrue($entity->isAdministrator());
        $this->assertTrue($entity->isOperatorAdministrator());
        $this->assertFalse($entity->isConsultantAdministrator());
    }

    public function testUpdateTransportManager(): void
    {
        $nonAdminRole = m::mock(RoleEntity::class)->makePartial();
        $nonAdminRole->setRole(RoleEntity::ROLE_OPERATOR_USER);

        $orgName = 'Org Name';
        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setName($orgName);

        $data = [
            'userType' => Entity::USER_TYPE_TRANSPORT_MANAGER,
            'loginId' => 'loginId',
            'roles' => [$nonAdminRole],
            'translateToWelsh' => 'Y',
            'accountDisabled' => 'N',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [$org],
        ];

        // create an object of different type first
        $entity = Entity::create(
            'pid',
            Entity::USER_TYPE_PARTNER,
            [
                'loginId' => 'currentLoginId',
                'translateToWelsh' => 'N',
                'accountDisabled' => 'Y',
                'team' => m::mock(TeamEntity::class),
                'transportManager' => m::mock(TransportManagerEntity::class),
                'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
                'localAuthority' => m::mock(LocalAuthorityEntity::class),
                'organisations' => [
                    m::mock(OrganisationEntity::class)
                ],
            ]
        );

        // update the entity
        $entity->update($data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertEquals(null, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_TRANSPORT_MANAGER, $entity->getUserType());
        $this->assertEquals(null, $entity->getTeam());
        $this->assertEquals($data['transportManager'], $entity->getTransportManager());
        $this->assertEquals(null, $entity->getPartnerContactDetails());
        $this->assertEquals(null, $entity->getLocalAuthority());
        $this->assertEquals(1, $entity->getOrganisationUsers()->count());
        $this->assertEquals('N', $entity->getOrganisationUsers()->first()->getIsAdministrator());
        $this->assertEquals($orgName, $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertFalse($entity->isAdministrator());
    }

    public function testCreatePartner(): void
    {
        $role = m::mock(RoleEntity::class)->makePartial();
        $role->setRole(RoleEntity::ROLE_PARTNER_USER);

        $orgName = 'Org Name';
        $contactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $contactDetails->setDescription($orgName);

        $data = [
            'loginId' => 'loginId',
            'roles' => [$role],
            'translateToWelsh' => 'N',
            'accountDisabled' => 'Y',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => $contactDetails,
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [
                m::mock(OrganisationEntity::class)
            ],
        ];

        $entity = Entity::create('pid', Entity::USER_TYPE_PARTNER, $data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertInstanceOf(\DateTime::class, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_PARTNER, $entity->getUserType());
        $this->assertEquals(null, $entity->getTeam());
        $this->assertEquals(null, $entity->getTransportManager());
        $this->assertEquals($data['partnerContactDetails'], $entity->getPartnerContactDetails());
        $this->assertEquals(null, $entity->getLocalAuthority());
        $this->assertEquals(0, $entity->getOrganisationUsers()->count());
        $this->assertEquals($orgName, $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertFalse($entity->isAdministrator());
    }

    public function testUpdatePartner(): void
    {
        $role = m::mock(RoleEntity::class)->makePartial();
        $role->setRole(RoleEntity::ROLE_PARTNER_USER);

        $orgName = 'Org Name';
        $contactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $contactDetails->setDescription($orgName);

        $data = [
            'userType' => Entity::USER_TYPE_PARTNER,
            'loginId' => 'loginId',
            'roles' => [$role],
            'translateToWelsh' => 'Y',
            'accountDisabled' => 'N',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => $contactDetails,
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [
                m::mock(OrganisationEntity::class)
            ],
        ];

        // create an object of different type first
        $entity = Entity::create(
            'pid',
            Entity::USER_TYPE_LOCAL_AUTHORITY,
            [
                'loginId' => 'currentLoginId',
                'translateToWelsh' => 'N',
                'accountDisabled' => 'Y',
                'team' => m::mock(TeamEntity::class),
                'transportManager' => m::mock(TransportManagerEntity::class),
                'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
                'localAuthority' => m::mock(LocalAuthorityEntity::class),
                'organisations' => [
                    m::mock(OrganisationEntity::class)
                ],
            ]
        );

        // update the entity
        $entity->update($data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertEquals(null, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_PARTNER, $entity->getUserType());
        $this->assertEquals(null, $entity->getTeam());
        $this->assertEquals(null, $entity->getTransportManager());
        $this->assertEquals($data['partnerContactDetails'], $entity->getPartnerContactDetails());
        $this->assertEquals(null, $entity->getLocalAuthority());
        $this->assertEquals(0, $entity->getOrganisationUsers()->count());
        $this->assertEquals($orgName, $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertFalse($entity->isAdministrator());
    }

    public function testCreateLocalAuthority(): void
    {
        $role = m::mock(RoleEntity::class)->makePartial();
        $role->setRole(RoleEntity::ROLE_LOCAL_AUTHORITY_USER);

        $orgName = 'Org Name';
        $localAuthority = m::mock(LocalAuthorityEntity::class)->makePartial();
        $localAuthority->setDescription($orgName);

        $data = [
            'loginId' => 'loginId',
            'roles' => [$role],
            'translateToWelsh' => 'N',
            'accountDisabled' => 'Y',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => $localAuthority,
            'organisations' => [
                m::mock(OrganisationEntity::class)
            ],
        ];

        $entity = Entity::create('pid', Entity::USER_TYPE_LOCAL_AUTHORITY, $data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertInstanceOf(\DateTime::class, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_LOCAL_AUTHORITY, $entity->getUserType());
        $this->assertEquals(null, $entity->getTeam());
        $this->assertEquals(null, $entity->getTransportManager());
        $this->assertEquals(null, $entity->getPartnerContactDetails());
        $this->assertEquals($data['localAuthority'], $entity->getLocalAuthority());
        $this->assertEquals(0, $entity->getOrganisationUsers()->count());
        $this->assertEquals($orgName, $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertFalse($entity->isAdministrator());
    }

    public function testUpdateLocalAuthority(): void
    {
        $role = m::mock(RoleEntity::class)->makePartial();
        $role->setRole(RoleEntity::ROLE_LOCAL_AUTHORITY_USER);

        $orgName = 'Org Name';
        $localAuthority = m::mock(LocalAuthorityEntity::class)->makePartial();
        $localAuthority->setDescription($orgName);

        $data = [
            'userType' => Entity::USER_TYPE_LOCAL_AUTHORITY,
            'loginId' => 'loginId',
            'roles' => [$role],
            'translateToWelsh' => 'Y',
            'accountDisabled' => 'N',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => $localAuthority,
            'organisations' => [
                m::mock(OrganisationEntity::class)
            ],
        ];

        // create an object of different type first
        $entity = Entity::create(
            'pid',
            Entity::USER_TYPE_PARTNER,
            [
                'loginId' => 'currentLoginId',
                'translateToWelsh' => 'N',
                'accountDisabled' => 'Y',
                'team' => m::mock(TeamEntity::class),
                'transportManager' => m::mock(TransportManagerEntity::class),
                'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
                'localAuthority' => m::mock(LocalAuthorityEntity::class),
                'organisations' => [
                    m::mock(OrganisationEntity::class)
                ],
            ]
        );

        // update the entity
        $entity->update($data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertEquals(null, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_LOCAL_AUTHORITY, $entity->getUserType());
        $this->assertEquals(null, $entity->getTeam());
        $this->assertEquals(null, $entity->getTransportManager());
        $this->assertEquals(null, $entity->getPartnerContactDetails());
        $this->assertEquals($data['localAuthority'], $entity->getLocalAuthority());
        $this->assertEquals(0, $entity->getOrganisationUsers()->count());
        $this->assertEquals($orgName, $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertFalse($entity->isAdministrator());
    }

    public function testCreateOperatorAdmin(): void
    {
        $adminRole = m::mock(RoleEntity::class)->makePartial();
        $adminRole->setRole(RoleEntity::ROLE_OPERATOR_ADMIN);

        $orgName = 'Org Name';
        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setName($orgName);

        $data = [
            'loginId' => 'loginId',
            'roles' => [$adminRole],
            'translateToWelsh' => 'N',
            'accountDisabled' => 'Y',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [$org],
        ];

        $entity = Entity::create('pid', Entity::USER_TYPE_OPERATOR, $data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertInstanceOf(\DateTime::class, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_OPERATOR, $entity->getUserType());
        $this->assertEquals(null, $entity->getTeam());
        $this->assertEquals(null, $entity->getTransportManager());
        $this->assertEquals(null, $entity->getPartnerContactDetails());
        $this->assertEquals(null, $entity->getLocalAuthority());
        $this->assertEquals(1, $entity->getOrganisationUsers()->count());
        $this->assertEquals('Y', $entity->getOrganisationUsers()->first()->getIsAdministrator());
        $this->assertEquals($orgName, $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertTrue($entity->isAdministrator());
        $this->assertTrue($entity->isOperatorAdministrator());
        $this->assertFalse($entity->isConsultantAdministrator());
    }

    public function testCreateTransportConsultant(): void
    {
        $adminRole = m::mock(RoleEntity::class)->makePartial();
        $adminRole->setRole(RoleEntity::ROLE_OPERATOR_TC);

        $orgName = 'Org Name';
        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setName($orgName);

        $data = [
            'loginId' => 'loginId',
            'roles' => [$adminRole],
            'translateToWelsh' => 'N',
            'accountDisabled' => 'Y',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [$org],
        ];

        $entity = Entity::create('pid', Entity::USER_TYPE_OPERATOR, $data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertInstanceOf(\DateTime::class, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_OPERATOR, $entity->getUserType());
        $this->assertEquals(null, $entity->getTeam());
        $this->assertEquals(null, $entity->getTransportManager());
        $this->assertEquals(null, $entity->getPartnerContactDetails());
        $this->assertEquals(null, $entity->getLocalAuthority());
        $this->assertEquals(1, $entity->getOrganisationUsers()->count());
        $this->assertEquals('Y', $entity->getOrganisationUsers()->first()->getIsAdministrator());
        $this->assertEquals($orgName, $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertTrue($entity->isAdministrator());
        $this->assertFalse($entity->isOperatorAdministrator());
        $this->assertTrue($entity->isConsultantAdministrator());
    }

    public function testUpdateOperatorUser(): void
    {
        $nonAdminRole = m::mock(RoleEntity::class)->makePartial();
        $nonAdminRole->setRole(RoleEntity::ROLE_OPERATOR_USER);

        $orgName = 'Org Name';
        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setName($orgName);

        $data = [
            'userType' => Entity::USER_TYPE_OPERATOR,
            'loginId' => 'loginId',
            'roles' => [$nonAdminRole],
            'translateToWelsh' => 'Y',
            'accountDisabled' => 'N',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [$org],
        ];

        // create an object of different type first
        $entity = Entity::create(
            'pid',
            Entity::USER_TYPE_PARTNER,
            [
                'loginId' => 'currentLoginId',
                'translateToWelsh' => 'N',
                'accountDisabled' => 'Y',
                'team' => m::mock(TeamEntity::class),
                'transportManager' => m::mock(TransportManagerEntity::class),
                'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
                'localAuthority' => m::mock(LocalAuthorityEntity::class),
                'organisations' => [
                    m::mock(OrganisationEntity::class)
                ],
            ]
        );

        // update the entity
        $entity->update($data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());
        $this->assertEquals($data['translateToWelsh'], $entity->getTranslateToWelsh());
        $this->assertEquals($data['accountDisabled'], $entity->getAccountDisabled());
        $this->assertEquals(null, $entity->getDisabledDate());

        $this->assertEquals(Entity::USER_TYPE_OPERATOR, $entity->getUserType());
        $this->assertEquals(null, $entity->getTeam());
        $this->assertEquals(null, $entity->getTransportManager());
        $this->assertEquals(null, $entity->getPartnerContactDetails());
        $this->assertEquals(null, $entity->getLocalAuthority());
        $this->assertEquals(1, $entity->getOrganisationUsers()->count());
        $this->assertEquals('N', $entity->getOrganisationUsers()->first()->getIsAdministrator());
        $this->assertEquals($orgName, $entity->getRelatedOrganisationName());
        $this->assertEquals(false, $entity->isAnonymous());
        $this->assertFalse($entity->isAdministrator());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpOperatorAdminRoles')]
    public function testUpdateOperatorIsAdministratorOnly(string $adminRoleAsString): void
    {
        $adminRole = m::mock(RoleEntity::class)->makePartial();
        $adminRole->setRole($adminRoleAsString);

        $nonAdminRole = m::mock(RoleEntity::class)->makePartial();
        $nonAdminRole->setRole(RoleEntity::ROLE_OPERATOR_USER);

        $data = [
            'userType' => Entity::USER_TYPE_OPERATOR,
            'loginId' => 'loginId',
            'roles' => [$adminRole],
        ];

        $entity = Entity::create(
            'pid',
            Entity::USER_TYPE_OPERATOR,
            [
                'loginId' => 'currentLoginId',
                'roles' => [$nonAdminRole],
                'organisations' => [
                    m::mock(OrganisationEntity::class)->makePartial()
                ],
            ]
        );

        // update the entity
        $entity->update($data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());

        $this->assertEquals(Entity::USER_TYPE_OPERATOR, $entity->getUserType());
        $this->assertEquals(1, $entity->getOrganisationUsers()->count());
        $this->assertEquals('Y', $entity->getOrganisationUsers()->first()->getIsAdministrator());
    }

    public function testCreateThrowsInvalidRoleException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $role = m::mock(RoleEntity::class)->makePartial();
        $role->setRole(RoleEntity::ROLE_INTERNAL_ADMIN);

        $data = [
            'loginId' => 'loginId',
            'roles' => [$role],
            'accountDisabled' => 'Y',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [
                m::mock(OrganisationEntity::class)->makePartial()
            ],
        ];

        Entity::create('pid', Entity::USER_TYPE_OPERATOR, $data);
    }

    public function testUpdateThrowsInvalidRoleException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $role = m::mock(RoleEntity::class)->makePartial();
        $role->setRole(RoleEntity::ROLE_INTERNAL_ADMIN);

        $data = [
            'userType' => Entity::USER_TYPE_OPERATOR,
            'loginId' => 'loginId',
            'roles' => [$role],
            'accountDisabled' => 'N',
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [
                m::mock(OrganisationEntity::class)->makePartial()
            ],
        ];

        // create an object of different type first
        $entity = Entity::create(
            'pid',
            Entity::USER_TYPE_PARTNER,
            [
                'loginId' => 'currentLoginId',
                'accountDisabled' => 'Y',
                'team' => m::mock(TeamEntity::class),
                'transportManager' => m::mock(TransportManagerEntity::class),
                'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
                'localAuthority' => m::mock(LocalAuthorityEntity::class),
                'organisations' => [
                    m::mock(OrganisationEntity::class)
                ],
            ]
        );

        // update the entity
        $entity->update($data);
    }

    public function testMissingRoleException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $role = m::mock(RoleEntity::class)->makePartial();
        $role->setRole('invalid_role');

        $data = [
            'userType' => 'random user type',
            'loginId' => 'loginId',
            'roles' => [$role],
        ];

        // create an object of different type first
        $entity = Entity::create(
            'pid',
            'random user type',
            [
                'loginId' => 'currentLoginId',
            ]
        );

        // update the entity
        $entity->update($data);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getPermissionProvider')]
    public function testGetPermission(mixed $userType, mixed $roleIds, mixed $expected): void
    {
        $roles = array_map(
            function ($id) {
                $role = m::mock(RoleEntity::class)->makePartial();
                $role->setRole($id);

                return $role;
            },
            $roleIds
        );

        $data = [
            'loginId' => 'loginId',
            'roles' => $roles,
            'team' => m::mock(TeamEntity::class),
            'transportManager' => m::mock(TransportManagerEntity::class),
            'partnerContactDetails' => m::mock(ContactDetailsEntity::class),
            'localAuthority' => m::mock(LocalAuthorityEntity::class),
            'organisations' => [
                m::mock(OrganisationEntity::class)->makePartial()
            ],
        ];

        $entity = Entity::create('pid', $userType, $data);

        $this->assertEquals($expected, $entity->getPermission());
    }

    public static function getPermissionProvider(): array
    {
        return [
            // local authority - admin
            [
                Entity::USER_TYPE_LOCAL_AUTHORITY,
                Entity::getRolesByUserType(Entity::USER_TYPE_LOCAL_AUTHORITY, Entity::PERMISSION_ADMIN),
                Entity::PERMISSION_ADMIN
            ],
            // local authority - user
            [
                Entity::USER_TYPE_LOCAL_AUTHORITY,
                Entity::getRolesByUserType(Entity::USER_TYPE_LOCAL_AUTHORITY, Entity::PERMISSION_USER),
                Entity::PERMISSION_USER
            ],
            // operator - admin
            [
                Entity::USER_TYPE_OPERATOR,
                Entity::getRolesByUserType(Entity::USER_TYPE_OPERATOR, Entity::PERMISSION_ADMIN),
                Entity::PERMISSION_ADMIN
            ],
            // operator - user
            [
                Entity::USER_TYPE_OPERATOR,
                Entity::getRolesByUserType(Entity::USER_TYPE_OPERATOR, Entity::PERMISSION_USER),
                Entity::PERMISSION_USER
            ],
            // operator - tm
            [
                Entity::USER_TYPE_OPERATOR,
                Entity::getRolesByUserType(Entity::USER_TYPE_OPERATOR, Entity::PERMISSION_TM),
                Entity::PERMISSION_TM
            ],
            // operator - admin with tm role
            [
                Entity::USER_TYPE_OPERATOR,
                array_merge(
                    Entity::getRolesByUserType(Entity::USER_TYPE_OPERATOR, Entity::PERMISSION_ADMIN),
                    Entity::getRolesByUserType(Entity::USER_TYPE_OPERATOR, Entity::PERMISSION_TM)
                ),
                Entity::PERMISSION_ADMIN
            ],
            // operator - user with tm role
            [
                Entity::USER_TYPE_OPERATOR,
                array_merge(
                    Entity::getRolesByUserType(Entity::USER_TYPE_OPERATOR, Entity::PERMISSION_USER),
                    Entity::getRolesByUserType(Entity::USER_TYPE_OPERATOR, Entity::PERMISSION_TM)
                ),
                Entity::PERMISSION_USER
            ],
            // partner - admin
            [
                Entity::USER_TYPE_PARTNER,
                Entity::getRolesByUserType(Entity::USER_TYPE_PARTNER, Entity::PERMISSION_ADMIN),
                Entity::PERMISSION_ADMIN
            ],
            // partner - user
            [
                Entity::USER_TYPE_PARTNER,
                Entity::getRolesByUserType(Entity::USER_TYPE_PARTNER, Entity::PERMISSION_USER),
                Entity::PERMISSION_USER
            ],
            // internal - user
            [
                Entity::USER_TYPE_INTERNAL,
                Entity::getRolesByUserType(Entity::USER_TYPE_INTERNAL, Entity::PERMISSION_USER),
                null
            ],
        ];
    }

    public function testAnon(): void
    {
        $user = Entity::anon();

        $role = $user->getRoles()->current();

        $this->assertEquals(1, $user->getRoles()->count());
        $this->assertInstanceOf(RoleEntity::class, $role);
        $this->assertEquals(RoleEntity::ROLE_ANON, $role->getId());
        $this->assertEquals('anon', $user->getLoginId());
        $this->assertEquals(true, $user->isAnonymous());
    }

    public function testIsSystemUserYes(): void
    {
        $user = new Entity('PID', 'type');
        $user->setId(IdentityProviderInterface::SYSTEM_USER);

        $this->assertTrue($user->isSystemUser());
    }

    public function testIsSystemUserNo(): void
    {
        $user = new Entity('PID', 'type');
        $user->setId(123);

        $this->assertFalse($user->isSystemUser());
    }

    public function testAnonUsernameReserved(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        Entity::create('123456', Entity::USER_TYPE_INTERNAL, ['loginId' => 'anon']);
    }

    public function testHasActivePsvLicence(): void
    {
        $user = new Entity('pid', Entity::USER_TYPE_OPERATOR);
        $this->assertEquals(false, $user->hasActivePsvLicence());

        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->shouldReceive('hasActiveLicences')->with(LicenceEntity::LICENCE_CATEGORY_PSV)->andReturn(true);

        $orgUser = new OrganisationUserEntity();
        $orgUser->setUser($user);
        $orgUser->setOrganisation($org);

        $user->addOrganisationUsers($orgUser);
        $this->assertEquals(true, $user->hasActivePsvLicence());
    }

    public function testGetNumberOfVehicles(): void
    {
        $user = new Entity('pid', Entity::USER_TYPE_OPERATOR);

        $mockLicence = m::mock(LicenceEntity::class)->makePartial();
        $mockLicence->setStatus(LicenceEntity::LICENCE_STATUS_VALID);
        $mockLicence->setTotAuthVehicles(2);

        $activeLicences = new ArrayCollection();
        $activeLicences->add($mockLicence);

        $mockApplication = m::mock(ApplicationEntity::class)->makePartial();
        $mockApplication->setStatus(ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION);
        $mockApplication->setTotAuthVehicles(1);

        $outstandingApplications = new ArrayCollection();
        $outstandingApplications->add($mockApplication);

        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->shouldReceive('getActiveLicences')->andReturn($activeLicences);
        $org->shouldReceive('getOutstandingApplications')->andReturn($outstandingApplications);

        $orgUser = new OrganisationUserEntity();
        $orgUser->setUser($user);
        $orgUser->setOrganisation($org);

        $user->addOrganisationUsers($orgUser);
        $this->assertEquals(3, $user->getNumberOfVehicles());
    }

    public function testReturnGetNumberOfVehiclesWithNoRelatedOrg(): void
    {
        $user = new Entity('pid', Entity::USER_TYPE_INTERNAL);

        $organisationUsers = new ArrayCollection();
        $user->setOrganisationUsers($organisationUsers);

        $this->assertEquals(0, $user->getNumberOfVehicles());
    }

    public function testGetRolesByUserTypeNoRoles(): void
    {
        $user = new Entity('pid', 'foo');
        $this->assertEquals([], $user->getRolesByUserType('foo', 'bar'));
    }

    public function testGetPermissionNoRoles(): void
    {
        $user = new Entity('pid', Entity::USER_TYPE_PARTNER);
        $this->assertNull($user->getPermission());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderHasRoles')]
    public function testHasRoles(array $roles, bool $expectedResult): void
    {
        $adminRole = m::mock(RoleEntity::class)->makePartial();
        $adminRole->setRole(RoleEntity::ROLE_OPERATOR_ADMIN);

        $nonAdminRole = m::mock(RoleEntity::class)->makePartial();
        $nonAdminRole->setRole(RoleEntity::ROLE_OPERATOR_USER);

        $user = new Entity('pid', Entity::USER_TYPE_PARTNER);
        $user->setRoles(new ArrayCollection([$adminRole, $nonAdminRole]));

        $result = $user->hasRoles($roles);
        $this->assertEquals($expectedResult, $result);
    }

    public static function dataProviderHasRoles(): array
    {
        return [
            'User has no roles' => [
                [],
                false
            ],
            'User has role' => [
                [RoleEntity::ROLE_OPERATOR_ADMIN],
                true
            ],
            'User does not have role' => [
                [RoleEntity::ROLE_OPERATOR_TM],
                false
            ],
            'User has two of roles' => [
                [RoleEntity::ROLE_OPERATOR_ADMIN, RoleEntity::ROLE_OPERATOR_USER],
                true
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpOperatorAdminRoles')]
    public function testUpdateOperatorWithOrganisationUsers(string $adminRoleAsString): void
    {
        $adminRole = m::mock(RoleEntity::class)->makePartial();
        $adminRole->setRole($adminRoleAsString);

        $nonAdminRole = m::mock(RoleEntity::class)->makePartial();
        $nonAdminRole->setRole(RoleEntity::ROLE_OPERATOR_USER);

        $data = [
            'userType' => Entity::USER_TYPE_OPERATOR,
            'loginId' => 'loginId',
            'roles' => [$adminRole],
        ];

        $organisation = m::mock(OrganisationEntity::class)->makePartial()
            ->setId(1);

        $entity = Entity::create(
            'pid',
            Entity::USER_TYPE_OPERATOR,
            [
                'loginId' => 'currentLoginId',
                'roles' => [$nonAdminRole],
                'organisations' => [
                    1 => $organisation
                ],
            ]
        );
        $organisationUser = new OrganisationUser();
        $organisationUser->setOrganisation($organisation);
        $organisationUser->setUser($entity);
        $organisationUsers = new ArrayCollection([1 => $organisationUser]);
        $entity->setOrganisationUsers($organisationUsers);

        $entity->update($data);

        $this->assertEquals($data['loginId'], $entity->getLoginId());
        $this->assertEquals($data['roles'], $entity->getRoles()->toArray());

        $this->assertEquals(Entity::USER_TYPE_OPERATOR, $entity->getUserType());
        $this->assertEquals(1, $entity->getOrganisationUsers()->count());
        $this->assertEquals('Y', $entity->getOrganisationUsers()->first()->getIsAdministrator());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpAssignedDataRetention')]
    public function testCanBeAssignedDataRetention(mixed $userType, mixed $accountDisabled, mixed $expectedResult): void
    {
        $data = [
            'accountDisabled' => $accountDisabled,
            'loginId' => 'usr'
        ];

        $entity = Entity::create('pid', $userType, $data);

        $this->assertEquals($expectedResult, $entity->canBeAssignedDataRetention());
    }

    /**
     * data provider for testCanBeAssignedDataRetention
     *
     * @return array
     */
    public static function dpAssignedDataRetention(): array
    {
        return [
            [Entity::USER_TYPE_INTERNAL, 'N', true],
            [Entity::USER_TYPE_INTERNAL, 'Y', false],
            [Entity::USER_TYPE_ANON, 'N', false],
            [Entity::USER_TYPE_ANON, 'Y', false],
            [Entity::USER_TYPE_LOCAL_AUTHORITY, 'N', false],
            [Entity::USER_TYPE_LOCAL_AUTHORITY, 'Y', false],
            [Entity::USER_TYPE_OPERATOR, 'N', false],
            [Entity::USER_TYPE_OPERATOR, 'Y', false],
            [Entity::USER_TYPE_PARTNER, 'N', false],
            [Entity::USER_TYPE_PARTNER, 'Y', false],
            [Entity::USER_TYPE_TRANSPORT_MANAGER, 'N', false],
            [Entity::USER_TYPE_TRANSPORT_MANAGER, 'Y', false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsEligibleForPermits')]
    public function testIsEligibleForPermits(mixed $isOrgEligibleForPermits, mixed $expected): void
    {
        $user = new Entity('pid', Entity::USER_TYPE_OPERATOR);

        if (isset($isOrgEligibleForPermits)) {
            $org = m::mock(OrganisationEntity::class)->makePartial();
            $org->shouldReceive('isEligibleForPermits')->andReturn($isOrgEligibleForPermits);

            $orgUser = new OrganisationUserEntity();
            $orgUser->setUser($user);
            $orgUser->setOrganisation($org);

            $user->addOrganisationUsers($orgUser);
        }

        $this->assertSame($expected, $user->isEligibleForPermits());
    }

    public static function dpIsEligibleForPermits(): array
    {
        return [
            [null, false],
            [true, true],
            [false, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsAllowedToPerformActionOnRoles')]
    public function testIsAllowedToPerformActionOnRoles(mixed $rolesOwn, mixed $rolesToCheck, mixed $expected): void
    {
        $entity = Entity::create(
            'pid',
            Entity::USER_TYPE_INTERNAL,
            [
                'loginId' => 'loginId',
                'roles' => $rolesOwn,
            ]
        );

        $this->assertEquals($expected, $entity->isAllowedToPerformActionOnRoles($rolesToCheck));
    }

    public static function dpIsAllowedToPerformActionOnRoles(): array
    {
        $systemAdminRole = m::mock(RoleEntity::class)->makePartial();
        $systemAdminRole->setRole(RoleEntity::ROLE_SYSTEM_ADMIN);

        $internalAdminRole = m::mock(RoleEntity::class)->makePartial();
        $internalAdminRole->setRole(RoleEntity::ROLE_INTERNAL_ADMIN);

        $internalIrhpAdminRole = m::mock(RoleEntity::class)->makePartial();
        $internalIrhpAdminRole->setRole(RoleEntity::ROLE_INTERNAL_IRHP_ADMIN);

        $internalCaseWorkerRole = m::mock(RoleEntity::class)->makePartial();
        $internalCaseWorkerRole->setRole(RoleEntity::ROLE_INTERNAL_CASE_WORKER);

        $internalReadOnlyRole = m::mock(RoleEntity::class)->makePartial();
        $internalReadOnlyRole->setRole(RoleEntity::ROLE_INTERNAL_READ_ONLY);

        return [
            'user with no roles' => [
                'rolesOwn' => [],
                'rolesToCheck' => [RoleEntity::ROLE_SYSTEM_ADMIN],
                'expected' => false,
            ],
            'ROLE_SYSTEM_ADMIN user - allowed to perform action on the following roles' => [
                'rolesOwn' => [$systemAdminRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_SYSTEM_ADMIN,
                    RoleEntity::ROLE_INTERNAL_ADMIN,
                    RoleEntity::ROLE_INTERNAL_IRHP_ADMIN,
                    RoleEntity::ROLE_INTERNAL_CASE_WORKER,
                    RoleEntity::ROLE_INTERNAL_READ_ONLY,
                    RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY,
                    RoleEntity::ROLE_OPERATOR_TC,
                    RoleEntity::ROLE_OPERATOR_ADMIN,
                    RoleEntity::ROLE_OPERATOR_USER,
                    RoleEntity::ROLE_OPERATOR_TM,
                    RoleEntity::ROLE_PARTNER_ADMIN,
                    RoleEntity::ROLE_PARTNER_USER,
                    RoleEntity::ROLE_LOCAL_AUTHORITY_ADMIN,
                    RoleEntity::ROLE_LOCAL_AUTHORITY_USER,
                ],
                'expected' => true,
            ],
            'ROLE_INTERNAL_ADMIN user - allowed to perform action on the following roles' => [
                'rolesOwn' => [$internalAdminRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_ADMIN,
                    RoleEntity::ROLE_INTERNAL_IRHP_ADMIN,
                    RoleEntity::ROLE_INTERNAL_CASE_WORKER,
                    RoleEntity::ROLE_INTERNAL_READ_ONLY,
                    RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY,
                    RoleEntity::ROLE_OPERATOR_TC,
                    RoleEntity::ROLE_OPERATOR_ADMIN,
                    RoleEntity::ROLE_OPERATOR_USER,
                    RoleEntity::ROLE_OPERATOR_TM,
                    RoleEntity::ROLE_PARTNER_ADMIN,
                    RoleEntity::ROLE_PARTNER_USER,
                    RoleEntity::ROLE_LOCAL_AUTHORITY_ADMIN,
                    RoleEntity::ROLE_LOCAL_AUTHORITY_USER,
                ],
                'expected' => true,
            ],
            'ROLE_INTERNAL_IRHP_ADMIN user - not allowed to perform action on ROLE_SYSTEM_ADMIN' => [
                'rolesOwn' => [$internalIrhpAdminRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_SYSTEM_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_IRHP_ADMIN user - allowed to perform action on the following roles' => [
                'rolesOwn' => [$internalIrhpAdminRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_ADMIN,
                    RoleEntity::ROLE_INTERNAL_IRHP_ADMIN,
                    RoleEntity::ROLE_INTERNAL_CASE_WORKER,
                    RoleEntity::ROLE_INTERNAL_READ_ONLY,
                    RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY,
                    RoleEntity::ROLE_OPERATOR_TC,
                    RoleEntity::ROLE_OPERATOR_ADMIN,
                    RoleEntity::ROLE_OPERATOR_USER,
                    RoleEntity::ROLE_OPERATOR_TM,
                    RoleEntity::ROLE_PARTNER_ADMIN,
                    RoleEntity::ROLE_PARTNER_USER,
                    RoleEntity::ROLE_LOCAL_AUTHORITY_ADMIN,
                    RoleEntity::ROLE_LOCAL_AUTHORITY_USER,
                ],
                'expected' => true,
            ],
            'ROLE_INTERNAL_ADMIN user - not allowed to perform action on ROLE_SYSTEM_ADMIN' => [
                'rolesOwn' => [$internalAdminRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_SYSTEM_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_CASE_WORKER user - allowed to perform action on the following roles' => [
                'rolesOwn' => [$internalCaseWorkerRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_CASE_WORKER,
                    RoleEntity::ROLE_INTERNAL_READ_ONLY,
                    RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY,
                    RoleEntity::ROLE_OPERATOR_TC,
                    RoleEntity::ROLE_OPERATOR_ADMIN,
                    RoleEntity::ROLE_OPERATOR_USER,
                    RoleEntity::ROLE_OPERATOR_TM,
                    RoleEntity::ROLE_PARTNER_ADMIN,
                    RoleEntity::ROLE_PARTNER_USER,
                    RoleEntity::ROLE_LOCAL_AUTHORITY_ADMIN,
                    RoleEntity::ROLE_LOCAL_AUTHORITY_USER,
                ],
                'expected' => true,
            ],
            'ROLE_INTERNAL_CASE_WORKER user - not allowed to perform action on ROLE_SYSTEM_ADMIN' => [
                'rolesOwn' => [$internalCaseWorkerRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_SYSTEM_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_CASE_WORKER user - not allowed to perform action on ROLE_INTERNAL_ADMIN' => [
                'rolesOwn' => [$internalCaseWorkerRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_CASE_WORKER user - not allowed to perform action on ROLE_INTERNAL_IRHP_ADMIN' => [
                'rolesOwn' => [$internalCaseWorkerRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_IRHP_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_SYSTEM_ADMIN' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_SYSTEM_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_INTERNAL_ADMIN' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_INTERNAL_IRHP_ADMIN' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_IRHP_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_INTERNAL_CASE_WORKER' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_CASE_WORKER,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_INTERNAL_READ_ONLY' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_READ_ONLY,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_INTERNAL_LIMITED_READ_ONLY' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_OPERATOR_TC' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_OPERATOR_TC,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_OPERATOR_ADMIN' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_OPERATOR_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_OPERATOR_USER' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_OPERATOR_USER,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_OPERATOR_TM' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_OPERATOR_TM,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_PARTNER_ADMIN' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_PARTNER_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_PARTNER_USER' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_PARTNER_USER,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_LOCAL_AUTHORITY_ADMIN' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_LOCAL_AUTHORITY_ADMIN,
                ],
                'expected' => false,
            ],
            'ROLE_INTERNAL_READ_ONLY user - not allowed to perform action on ROLE_LOCAL_AUTHORITY_USER' => [
                'rolesOwn' => [$internalReadOnlyRole],
                'rolesToCheck' => [
                    RoleEntity::ROLE_LOCAL_AUTHORITY_USER,
                ],
                'expected' => false,
            ],
        ];
    }

    public function testHasOrganisationSubmittedLicenceApplicationWithNoOrgAssumesNoExistingLicences(): void
    {
        $user = new Entity('pid', Entity::USER_TYPE_OPERATOR);

        $this->assertFalse($user->hasOrganisationSubmittedLicenceApplication());
    }

    public function testHasOrganisationSubmittedLicenceApplicationWithOrgCallsHasNeverHadLicenceDecisionOnOrgEntity(): void
    {
        $user = new Entity('pid', Entity::USER_TYPE_OPERATOR);

        $organisation = m::mock(OrganisationEntity::class)->makePartial()->setId(1);
        $organisation->expects('hasSubmittedLicenceApplication')->withNoArgs()->andReturns(true);

        $orgUser = new OrganisationUserEntity();
        $orgUser->setUser($user);
        $orgUser->setOrganisation($organisation);
        $user->addOrganisationUsers($orgUser);

        $this->assertTrue($user->hasOrganisationSubmittedLicenceApplication());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanResetPassword')]
    public function testCanResetPassword(string $accountDisabled, bool $resetAllowed): void
    {
        $user = new Entity('pid', Entity::USER_TYPE_INTERNAL);
        $user->setAccountDisabled($accountDisabled);
        $this->assertEquals($resetAllowed, $user->canResetPassword());
    }

    public static function dpCanResetPassword(): array
    {
        return [
            'account disabled, reset not allowed' => ['Y', false],
            'account active, reset allowed' => ['N', true],
        ];
    }

    public static function dpOperatorAdminRoles(): array
    {
        return [
            [RoleEntity::ROLE_OPERATOR_ADMIN],
            [RoleEntity::ROLE_OPERATOR_TC],
        ];
    }

    public function testAgreeTermsAndConditions(): void
    {
        $user = new Entity('pid', Entity::USER_TYPE_OPERATOR);
        $this->assertFalse($user->hasAgreedTermsAndConditions());
        $user->agreeTermsAndConditions();
        $this->assertTrue($user->hasAgreedTermsAndConditions());
    }

    public function testIsNotLastOperatorAdmin(): void
    {
        $adminRole = m::mock(RoleEntity::class)->makePartial();
        $adminRole->setRole(RoleEntity::ROLE_OPERATOR_ADMIN);

        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->expects('canDeleteOperatorAdmin')->withNoArgs()->andReturnTrue();

        $data = [
            'loginId' => 'loginId',
            'roles' => [$adminRole],
            'organisations' => [$org],
        ];

        $entity = Entity::create('pid', Entity::USER_TYPE_OPERATOR, $data);
        $this->assertFalse($entity->isLastOperatorAdmin());
    }

    public function testIsLastOperatorAdmin(): void
    {
        $adminRole = m::mock(RoleEntity::class)->makePartial();
        $adminRole->setRole(RoleEntity::ROLE_OPERATOR_ADMIN);

        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->expects('canDeleteOperatorAdmin')->withNoArgs()->andReturnFalse();

        $data = [
            'loginId' => 'loginId',
            'roles' => [$adminRole],
            'organisations' => [$org],
        ];

        $entity = Entity::create('pid', Entity::USER_TYPE_OPERATOR, $data);
        $this->assertTrue($entity->isLastOperatorAdmin());
    }

    public function testTransportConsultantNeverLastAdministrator(): void
    {
        $adminRole = m::mock(RoleEntity::class)->makePartial();
        $adminRole->setRole(RoleEntity::ROLE_OPERATOR_TC);

        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->expects('canDeleteOperatorAdmin')->never();

        $data = [
            'loginId' => 'loginId',
            'roles' => [$adminRole],
            'organisations' => [$org],
        ];

        $entity = Entity::create('pid', Entity::USER_TYPE_OPERATOR, $data);
        $this->assertFalse($entity->isLastOperatorAdmin());
    }
}
