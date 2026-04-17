<?php

declare(strict_types=1);

namespace OlcsTest\Assertion\Ebsr;

use Common\Rbac\User;
use Olcs\Assertion\Ebsr\EbsrList as Sut;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Ebsr List Test
 */
class EbsrListTest extends MockeryTestCase
{
    protected $sut;

    protected $auth;

    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->auth = m::mock(AuthorizationService::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getAssertDataProvider')]
    public function testAssert(
        string $userType,
        array $userData,
        bool $expected
    ): void {
        $currentUser = m::mock(User::class)->makePartial();
        $currentUser->shouldReceive('getUserType')->andReturn($userType);
        $currentUser->shouldReceive('getUserData')->andReturn($userData);

        $this->auth->shouldReceive('getIdentity')->andReturn($currentUser);

        $this->assertEquals($expected, $this->sut->assert($this->auth));
    }

    /**
     * @return (bool|bool[]|string)[][]
     *
     * @psalm-return list{list{'local-authority', array<never, never>, true}, list{'operator', array{hasActivePsvLicence: true}, true}, list{'operator', array{hasActivePsvLicence: false}, false}, list{'partner', array<never, never>, false}}
     */
    public static function getAssertDataProvider(): array
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
