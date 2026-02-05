<?php

declare(strict_types=1);

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
    protected Sut $sut;

    protected $auth;

    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->auth = m::mock(AuthorizationService::class);
    }

    public function testAssertWithoutContext(): void
    {
        $this->assertEquals(true, $this->sut->assert($this->auth));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAssertForPartnerDataProvider')]
    public function testAssertForPartner(
        mixed $isGranted,
        mixed $currentUserType,
        mixed $currentUserEntityId,
        mixed $userType,
        mixed $userEntityId,
        mixed $expected
    ): void {
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

    public static function getAssertForPartnerDataProvider(): array
    {
        return [
            [true, User::USER_TYPE_PARTNER, 123, User::USER_TYPE_PARTNER, 123, true],
            [true, User::USER_TYPE_PARTNER, 123, User::USER_TYPE_PARTNER, 1, false],
            [true, User::USER_TYPE_LOCAL_AUTHORITY, 123, User::USER_TYPE_PARTNER, 123, false],
            [false, User::USER_TYPE_PARTNER, 123, User::USER_TYPE_PARTNER, 123, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAssertForLocalAuthorityDataProvider')]
    public function testAssertForLocalAuthority(
        mixed $isGranted,
        mixed $currentUserType,
        mixed $currentUserEntityId,
        mixed $userType,
        mixed $userEntityId,
        mixed $expected
    ): void {
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

    public static function getAssertForLocalAuthorityDataProvider(): array
    {
        return [
            [true, User::USER_TYPE_LOCAL_AUTHORITY, 123, User::USER_TYPE_LOCAL_AUTHORITY, 123, true],
            [true, User::USER_TYPE_LOCAL_AUTHORITY, 123, User::USER_TYPE_LOCAL_AUTHORITY, 1, false],
            [true, User::USER_TYPE_PARTNER, 123, User::USER_TYPE_LOCAL_AUTHORITY, 123, false],
            [false, User::USER_TYPE_LOCAL_AUTHORITY, 123, User::USER_TYPE_LOCAL_AUTHORITY, 123, false],
        ];
    }

    public function testAssertForOperatorTm(): void
    {
        $user = m::mock(User::class);
        $user->expects('getUserType')->withNoArgs()->andReturn(User::USER_TYPE_OPERATOR);

        $this->auth->expects('isGranted')->with(Permission::OPERATOR_ADMIN)->andReturnFalse();
        $this->auth->expects('isGranted')->with(Permission::OPERATOR_TC)->andReturnFalse();

        $this->assertFalse($this->sut->assert($this->auth, $user));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanRead')]
    public function testAssertForOperatorAdmin(bool $canRead): void
    {
        $user = m::mock(User::class);
        $user->expects('getUserType')->withNoArgs()->andReturn(User::USER_TYPE_OPERATOR);

        $this->auth->expects('isGranted')->with(Permission::OPERATOR_ADMIN)->andReturnTrue();

        $this->auth->expects('isGranted')
            ->with(Permission::CAN_READ_USER_SELFSERVE, $user)
            ->andReturn($canRead);

        $this->assertEquals($canRead, $this->sut->assert($this->auth, $user));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCanRead')]
    public function testAssertForOperatorTc(bool $canRead): void
    {
        $user = m::mock(User::class);
        $user->expects('getUserType')->withNoArgs()->andReturn(User::USER_TYPE_OPERATOR);

        $this->auth->expects('isGranted')->with(Permission::OPERATOR_ADMIN)->andReturnFalse();
        $this->auth->expects('isGranted')->with(Permission::OPERATOR_TC)->andReturnTrue();

        $this->auth->expects('isGranted')
            ->with(Permission::CAN_READ_USER_SELFSERVE, $user)
            ->andReturn($canRead);

        $this->assertEquals($canRead, $this->sut->assert($this->auth, $user));
    }

    public static function dpCanRead(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testAssertForInternal(): void
    {
        $user = m::mock(User::class);
        $user->expects('getUserType')->withNoArgs()->andReturn(User::USER_TYPE_INTERNAL);

        $this->assertFalse($this->sut->assert($this->auth, $user));
    }
}
