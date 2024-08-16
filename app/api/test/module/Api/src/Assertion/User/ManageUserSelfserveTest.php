<?php

namespace Dvsa\OlcsTest\Api\Assertion\User;

use Dvsa\Olcs\Api\Assertion\User\ManageUserSelfserve as Sut;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Check whether the current user can manage a user via selfserve
 */
class ManageUserSelfserveTest extends MockeryTestCase
{
    protected $sut;

    protected $auth;

    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->auth = m::mock(AuthorizationService::class);
    }

    public function testAssertWithoutContext()
    {
        $this->assertEquals(true, $this->sut->assert($this->auth));
    }

    /**
     * @dataProvider getAssertForPartnerDataProvider
     */
    public function testAssertForPartner(
        $isGranted,
        $currentUserType,
        $currentUserEntityId,
        $userType,
        $userEntityId,
        $expected
    ) {
        $currentUser = m::mock(User::class);
        $currentUser->shouldReceive('getUserType')->andReturn($currentUserType);
        $currentUser->shouldReceive('getPartnerContactDetails->getId')->andReturn($currentUserEntityId);

        $user = m::mock(User::class);
        $user->shouldReceive('getUserType')->andReturn($userType);
        $user->shouldReceive('getPartnerContactDetails->getId')->andReturn($userEntityId);

        $this->auth->shouldReceive('getIdentity->getUser')->andReturn($currentUser);

        $this->auth->shouldReceive('isGranted')
            ->once()
            ->with(Permission::PARTNER_ADMIN)
            ->andReturn($isGranted);

        $this->assertEquals($expected, $this->sut->assert($this->auth, $user));
    }

    public function getAssertForPartnerDataProvider()
    {
        return [
            [true, User::USER_TYPE_PARTNER, 123, User::USER_TYPE_PARTNER, 123, true],
            [true, User::USER_TYPE_PARTNER, 123, User::USER_TYPE_PARTNER, 1, false],
            [true, User::USER_TYPE_LOCAL_AUTHORITY, 123, User::USER_TYPE_PARTNER, 123, false],
            [false, User::USER_TYPE_PARTNER, 123, User::USER_TYPE_PARTNER, 123, false],
        ];
    }

    /**
     * @dataProvider getAssertForLocalAuthorityDataProvider
     */
    public function testAssertForLocalAuthority(
        $isGranted,
        $currentUserType,
        $currentUserEntityId,
        $userType,
        $userEntityId,
        $expected
    ) {
        $currentUser = m::mock(User::class);
        $currentUser->shouldReceive('getUserType')->andReturn($currentUserType);
        $currentUser->shouldReceive('getLocalAuthority->getId')->andReturn($currentUserEntityId);

        $user = m::mock(User::class);
        $user->shouldReceive('getUserType')->andReturn($userType);
        $user->shouldReceive('getLocalAuthority->getId')->andReturn($userEntityId);

        $this->auth->shouldReceive('getIdentity->getUser')->andReturn($currentUser);

        $this->auth->shouldReceive('isGranted')
            ->once()
            ->with(Permission::LOCAL_AUTHORITY_ADMIN)
            ->andReturn($isGranted);

        $this->assertEquals($expected, $this->sut->assert($this->auth, $user));
    }

    public function getAssertForLocalAuthorityDataProvider()
    {
        return [
            [true, User::USER_TYPE_LOCAL_AUTHORITY, 123, User::USER_TYPE_LOCAL_AUTHORITY, 123, true],
            [true, User::USER_TYPE_LOCAL_AUTHORITY, 123, User::USER_TYPE_LOCAL_AUTHORITY, 1, false],
            [true, User::USER_TYPE_PARTNER, 123, User::USER_TYPE_LOCAL_AUTHORITY, 123, false],
            [false, User::USER_TYPE_LOCAL_AUTHORITY, 123, User::USER_TYPE_LOCAL_AUTHORITY, 123, false],
        ];
    }

    /**
     * @dataProvider getAssertForOperatorDataProvider
     */
    public function testAssertForOperator(
        $isGranted,
        $currentUserType,
        $userType,
        $expected,
        $canRead
    ) {
        $currentUser = m::mock(User::class)->makePartial();
        $currentUser->shouldReceive('getUserType')->andReturn($currentUserType);

        $user = m::mock(User::class)->makePartial();
        $user->shouldReceive('getUserType')->andReturn($userType);

        $this->auth->shouldReceive('getIdentity->getUser')->andReturn($currentUser);

        $this->auth->shouldReceive('isGranted')
            ->once()
            ->with(Permission::OPERATOR_ADMIN)
            ->andReturn($isGranted);

        $this->auth->shouldReceive('isGranted')
            ->with(Permission::CAN_READ_USER_SELFSERVE, $user)
            ->andReturn($canRead);

        $this->assertEquals($expected, $this->sut->assert($this->auth, $user));
    }

    public function getAssertForOperatorDataProvider()
    {
        return [
            // operator manages operator
            [true, User::USER_TYPE_OPERATOR, User::USER_TYPE_OPERATOR, true, true],
            [true, User::USER_TYPE_OPERATOR, User::USER_TYPE_OPERATOR, false, false],
            [false, User::USER_TYPE_OPERATOR, User::USER_TYPE_OPERATOR, false, true],
            // operator manages TM
            [true, User::USER_TYPE_OPERATOR, User::USER_TYPE_TRANSPORT_MANAGER, true, true],
            [true, User::USER_TYPE_OPERATOR, User::USER_TYPE_TRANSPORT_MANAGER, false, false],
            [false, User::USER_TYPE_OPERATOR, User::USER_TYPE_TRANSPORT_MANAGER, false, true],
        ];
    }

    public function testAssertForInternal()
    {
        $user = m::mock(User::class)->makePartial();
        $user->shouldReceive('getUserType')->andReturn(User::USER_TYPE_INTERNAL);

        $this->assertFalse($this->sut->assert($this->auth, $user));
    }
}
