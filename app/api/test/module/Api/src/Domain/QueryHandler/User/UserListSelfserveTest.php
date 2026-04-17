<?php

declare(strict_types=1);

/**
 * UserListSelfserveTest
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\User\UserListSelfserve as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\User as Repo;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as OrganisationUserEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Transfer\Query\User\UserListSelfserve as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use ReflectionClass;

/**
 * UserListSelfserveTest
 */
class UserListSelfserveTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('User', Repo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    public function commonHandleQueryTest(): void
    {
        $user = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $user->setId(74);

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($user, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_PARTNER);

        $this->repoMap['User']->shouldReceive('fetchList')->andReturn([$user]);
        $this->repoMap['User']->shouldReceive('fetchCount')->andReturn('COUNT');

        $query = Query::create(['QUERY']);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(74, $result['result'][0]['id']);
        $this->assertSame('COUNT', $result['count']);
    }

    public function testHandleQueryForPartner(): void
    {
        /** @var ContactDetailsEntity $partnerContactDetails */
        $partnerContactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $partnerContactDetails->setId(1000);
        $partnerContactDetails->shouldReceive('getId')->once()->andReturn(1000);

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->setId(222);
        $currentUser->setPartnerContactDetails($partnerContactDetails);

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($currentUser, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_PARTNER);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        $this->commonHandleQueryTest();
    }

    public function testHandleQueryForLocalAuthority(): void
    {
        /** @var LocalAuthorityEntity $localAuthority */
        $localAuthority = m::mock(LocalAuthorityEntity::class)->makePartial();
        $localAuthority->setId(1000);
        $localAuthority->shouldReceive('getId')->once()->andReturn(1000);

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->setId(222);
        $currentUser->setLocalAuthority($localAuthority);

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($currentUser, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_LOCAL_AUTHORITY);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        $this->commonHandleQueryTest();
    }

    public function testHandleQueryForOperator(): void
    {
        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(1000);
        $organisation->shouldReceive('getId')->once()->andReturn(1000);

        /** @var OrganisationUserEntity $organisation */
        $organisationUser = m::mock(OrganisationUserEntity::class)->makePartial();
        $organisationUser->setOrganisation($organisation);

        /** @var UserEntity $currentUser */
        $currentUser = new UserEntity('pid', UserEntity::USER_TYPE_OPERATOR);
        $currentUser->setId(222);
        $currentUser->getOrganisationUsers()->add($organisationUser);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        $this->commonHandleQueryTest();
    }

    public function testHandleQueryForTm(): void
    {
        /** @var OrganisationEntity $organisation */
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(1000);

        /** @var OrganisationUserEntity $organisation */
        $organisationUser = m::mock(OrganisationUserEntity::class)->makePartial();
        $organisationUser->setOrganisation($organisation);

        /** @var TransportManagerEntity $transportManager */
        $transportManager = m::mock(TransportManagerEntity::class)->makePartial();
        $transportManager->setId(777);

        /** @var UserEntity $currentUser */
        $currentUser = new UserEntity('pid', UserEntity::USER_TYPE_TRANSPORT_MANAGER);
        $currentUser->setId(222);
        $currentUser->setTransportManager($transportManager);
        $currentUser->getOrganisationUsers()->add($organisationUser);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        $this->commonHandleQueryTest();
    }

    public function testHandleQueryThrowsIncorrectUserTypeException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        /** @var TeamEntity $user */
        $team = m::mock(Team::class)->makePartial();

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->setId(222);
        $currentUser->setTeam($team);

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($currentUser, 'wrong_user_type');

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        $this->commonHandleQueryTest();
    }

    public function testHandleQueryThrowsIncorrectFilterException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        /** @var ContactDetailsEntity $partnerContactDetails */
        $partnerContactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $partnerContactDetails->setId(1000);
        $partnerContactDetails->shouldReceive('getId')->once()->andReturn();

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->setId(222);
        $currentUser->setPartnerContactDetails($partnerContactDetails);

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($currentUser, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_PARTNER);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        $this->commonHandleQueryTest();
    }
}
