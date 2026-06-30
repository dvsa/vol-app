<?php

declare(strict_types=1);

namespace CommonTest\Rbac;

use Common\Rbac\User;
use Common\RefData;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class User
 * @package CommonTest\Rbac
 */
class UserTest extends TestCase
{
    private $sut;


    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new User();
    }

    /**
     * @dataProvider dpIsLocalAuthority
     */
    public function testIsLocalAuthority($userType, $isLocalAuthority): void
    {
        $this->sut->setUserType($userType);
        $this->assertEquals($isLocalAuthority, $this->sut->isLocalAuthority());
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{'local-authority', true}, list{'anon', false}, list{'operator', false}, list{'partner', false}, list{'transport-manager', false}, list{'internal', false}, list{'not-identified', false}}
     */
    public function dpIsLocalAuthority(): array
    {
        return [
            [User::USER_TYPE_LOCAL_AUTHORITY, true],
            [User::USER_TYPE_ANON, false],
            [User::USER_TYPE_OPERATOR, false],
            [User::USER_TYPE_PARTNER, false],
            [User::USER_TYPE_TRANSPORT_MANAGER, false],
            [User::USER_TYPE_INTERNAL, false],
            [User::USER_TYPE_NOT_IDENTIFIED, false],
        ];
    }

    /**
     * @dataProvider dpIsNotIdentified
     */
    public function testIsNotIdentified($userType, $isNotIdentified): void
    {
        $this->sut->setUserType($userType);
        $this->assertEquals($isNotIdentified, $this->sut->isNotIdentified());
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{'local-authority', false}, list{'anon', false}, list{'operator', false}, list{'partner', false}, list{'transport-manager', false}, list{'internal', false}, list{'not-identified', true}}
     */
    public function dpIsNotIdentified(): array
    {
        return [
            [User::USER_TYPE_LOCAL_AUTHORITY, false],
            [User::USER_TYPE_ANON, false],
            [User::USER_TYPE_OPERATOR, false],
            [User::USER_TYPE_PARTNER, false],
            [User::USER_TYPE_TRANSPORT_MANAGER, false],
            [User::USER_TYPE_INTERNAL, false],
            [User::USER_TYPE_NOT_IDENTIFIED, true],
        ];
    }

    public function testIsNotIdentifiedFalse(): void
    {

        $this->sut->setUserType(User::USER_TYPE_ANON);

        $this->assertFalse($this->sut->isNotIdentified());
    }

    public function testHasRole(): void
    {
        $roles = [RefData::ROLE_INTERNAL_CASE_WORKER];
        $this->sut->setRoles($roles);
        $this->assertEquals($roles, $this->sut->getRoles());
        $this->assertTrue($this->sut->hasRole(RefData::ROLE_INTERNAL_CASE_WORKER));
    }

    public function testHasRoleFalse(): void
    {
        $roles = [RefData::ROLE_INTERNAL_CASE_WORKER];
        $this->sut->setRoles($roles);
        $this->assertEquals($roles, $this->sut->getRoles());
        $this->assertFalse($this->sut->hasRole(RefData::ROLE_INTERNAL_ADMIN));
    }

    public function testHasAgreedTermsTrue(): void
    {
        $userData = ['termsAgreed' => true];
        $this->sut->setUserData($userData);
        $this->assertTrue($this->sut->hasAgreedTerms());
    }

    public function testHasAgreedTermsFalse(): void
    {
        $userData = ['termsAgreed' => false];
        $this->sut->setUserData($userData);
        $this->assertFalse($this->sut->hasAgreedTerms());
    }
}
