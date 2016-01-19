<?php

namespace OlcsTest\Assertion\Ebsr;

use Common\Rbac\User;
use Olcs\Assertion\Ebsr\EbsrList as Sut;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use ZfcRbac\Service\AuthorizationService;

/**
 * Ebsr List Test
 */
class EbsrListTest extends MockeryTestCase
{
    protected $sut;

    protected $auth;

    public function setUp()
    {
        $this->sut = new Sut();
        $this->auth = m::mock(AuthorizationService::class);
    }

    /**
     * @dataProvider getAssertDataProvider
     *
     */
    public function testAssert(
        $userType,
        $userData,
        $expected
    ) {
        $currentUser = m::mock(User::class)->makePartial();
        $currentUser->shouldReceive('getUserType')->andReturn($userType);
        $currentUser->shouldReceive('getUserData')->andReturn($userData);

        $this->auth->shouldReceive('getIdentity')->andReturn($currentUser);

        $this->assertEquals($expected, $this->sut->assert($this->auth));
    }

    public function getAssertDataProvider()
    {
        return [
            // local authority
            [User::USER_TYPE_LOCAL_AUTHORITY, [], true],
            // operator with active PSV licence
            [User::USER_TYPE_OPERATOR, ['hasActivePsvLicence' => true], true],
            // operator without active PSV licence
            [User::USER_TYPE_OPERATOR, ['hasActivePsvLicence' => false], false],
            // partner
            [User::USER_TYPE_PARTNER, [], false],
        ];
    }
}
