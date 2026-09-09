<?php

declare(strict_types=1);

namespace OlcsTest\Assertion\Ebsr;

use Common\Rbac\User;
use Olcs\Assertion\Ebsr\EbsrUpload as Sut;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Ebsr Upload Test
 */
final class EbsrUploadTest extends MockeryTestCase
{
    protected $sut;

    protected $auth;

    #[\Override]
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
     * @return \Iterator<string, array{string, array<string, bool>, bool}>
     */
    public static function getAssertDataProvider(): \Iterator
    {
        yield 'operator with an eligible licence' => [
            User::USER_TYPE_OPERATOR,
            ['hasEbsrEligibleLicence' => true],
            true,
        ];
        yield 'operator without an eligible licence' => [
            User::USER_TYPE_OPERATOR,
            ['hasEbsrEligibleLicence' => false],
            false,
        ];

        //an operator may hold an active PSV licence and still not be able to submit, for example
        //while a surrender is under consideration
        yield 'operator with an active psv licence only' => [
            User::USER_TYPE_OPERATOR,
            ['hasActivePsvLicence' => true],
            false,
        ];

        //the api has not been deployed with the flag yet, or the user data predates it
        yield 'operator with no flag at all' => [User::USER_TYPE_OPERATOR, [], false];

        //uploading is for operators only, local authorities receive registrations
        yield 'local authority' => [
            User::USER_TYPE_LOCAL_AUTHORITY,
            ['hasEbsrEligibleLicence' => true],
            false,
        ];
        yield 'partner' => [User::USER_TYPE_PARTNER, ['hasEbsrEligibleLicence' => true], false];
    }
}
