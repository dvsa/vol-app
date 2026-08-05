<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\Rbac\User;
use Common\View\Helper\CurrentUser;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\View\Renderer\RendererInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see CurrentUser
 */
final class CurrentUserTest extends MockeryTestCase
{
    public function testGetFullNameEmpty(): void
    {
        $identity = new User();

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);
        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals('Not logged in', $sut->getFullName());
    }

    public function testGetFullNameAnon(): void
    {
        $userData = [
            'userType' => User::USER_TYPE_ANON
        ];

        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);
        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals('Not logged in', $sut->getFullName());
    }

    public function testGetFullName(): void
    {
        $person = ['forename' => 'Terry', 'familyName' => 'Barret-Edgecombe'];

        $userData = [
            'userType' => User::USER_TYPE_OPERATOR,
            'contactDetails' => [
                'person' => $person
            ]
        ];

        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $mockView = m::mock(RendererInterface::class);
        $mockView->shouldReceive('personName')
            ->with($person, ['forename', 'familyName'])
            ->andReturn('Terry Barret-Edgecombe');

        $sut = new CurrentUser($mockAuthService, '1234');
        $sut->setView($mockView);

        $this->assertEquals('Terry Barret-Edgecombe', $sut->getFullName());
    }

    public function testGetFullNameUsername(): void
    {
        $userData = [
            'userType' => User::USER_TYPE_OPERATOR,
            'loginId' => 'username',
            'contactDetails' => [
                'person' => []
            ]
        ];

        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $mockView = m::mock(RendererInterface::class);
        $mockView->shouldReceive('personName')
            ->with([], ['forename', 'familyName'])
            ->andReturn('');

        $sut = new CurrentUser($mockAuthService, '1234');
        $sut->setView($mockView);

        $this->assertEquals('username', $sut->getFullName());
    }

    /**
     * @param $userData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideGetOperatorName')]
    public function testGetOperatorName($userData, $expected): void
    {
        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals($expected, $sut->getOrganisationName());
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<(array<array<string>> | string)> | string)> | string)>>
     *
     * @psalm-return list{list{array{userType: '', organisationUsers: list{array{organisation: array{name: 'Organisation Ltd'}}}, partnerContactDetails: array{description: 'Partner'}, localAuthority: array{description: 'Local Authority'}}, ''}, list{array{userType: 'anon', organisationUsers: list{array{organisation: array{name: 'Organisation Ltd'}}}, partnerContactDetails: array{description: 'Partner'}, localAuthority: array{description: 'Local Authority'}}, ''}, list{array{userType: 'transport-manager', organisationUsers: list{array{organisation: array{name: 'Organisation Ltd'}}}, partnerContactDetails: array{description: 'Partner'}, localAuthority: array{description: 'Local Authority'}}, 'Organisation Ltd'}, list{array{userType: 'operator', organisationUsers: list{array{organisation: array{name: 'Organisation Ltd'}}}, partnerContactDetails: array{description: 'Partner'}, localAuthority: array{description: 'Local Authority'}}, 'Organisation Ltd'}, list{array{userType: 'partner', organisationUsers: list{array{organisation: array{name: 'Organisation Ltd'}}}, partnerContactDetails: array{description: 'Partner'}, localAuthority: array{description: 'Local Authority'}}, 'Partner'}, list{array{userType: 'local-authority', organisationUsers: list{array{organisation: array{name: 'Organisation Ltd'}}}, partnerContactDetails: array{description: 'Partner'}, localAuthority: array{description: 'Local Authority'}}, 'Local Authority'}}
     */
    public static function provideGetOperatorName(): \Iterator
    {
        $userdata = [
            'userType' => '',
            'organisationUsers' => [
                [
                    'organisation' => [
                        'name' => 'Organisation Ltd'
                    ]
                ]
            ],
            'partnerContactDetails' => [
                'description' => 'Partner'
            ],
            'localAuthority' => [
                'description' => 'Local Authority'
            ]
        ];
        yield [$userdata, ''];
        yield [array_merge($userdata, ['userType' => User::USER_TYPE_ANON]), ''];
        yield [array_merge($userdata, ['userType' => User::USER_TYPE_TRANSPORT_MANAGER]), 'Organisation Ltd'];
        yield [array_merge($userdata, ['userType' => User::USER_TYPE_OPERATOR]), 'Organisation Ltd'];
        yield [array_merge($userdata, ['userType' => User::USER_TYPE_PARTNER]), 'Partner'];
        yield [array_merge($userdata, ['userType' => User::USER_TYPE_LOCAL_AUTHORITY]), 'Local Authority'];
    }

    /**
     * @param $userData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsLoggedIn')]
    public function testIsLoggedIn($userData, $expected): void
    {
        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals($expected, $sut->isLoggedIn());
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | bool)>>
     *
     * @psalm-return list{list{array<never, never>, false}, list{array{userType: 'anon'}, false}, list{array{userType: 'operator'}, true}}
     */
    public static function provideIsLoggedIn(): \Iterator
    {
        yield [[], false];
        yield [['userType' => User::USER_TYPE_ANON], false];
        yield [['userType' => User::USER_TYPE_OPERATOR], true];
    }

    /**
     * @param $userData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsOperator')]
    public function testIsOperator($userData, $expected): void
    {
        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals($expected, $sut->isOperator());
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | bool)>>
     *
     * @psalm-return list{list{array<never, never>, false}, list{array{userType: 'anon'}, false}, list{array{userType: 'operator'}, true}, list{array{userType: 'local-authority'}, false}, list{array{userType: 'partner'}, false}, list{array{userType: 'transport-manager'}, false}}
     */
    public static function provideIsOperator(): \Iterator
    {
        yield [[], false];
        yield [['userType' => User::USER_TYPE_ANON], false];
        yield [['userType' => User::USER_TYPE_OPERATOR], true];
        yield [['userType' => User::USER_TYPE_LOCAL_AUTHORITY], false];
        yield [['userType' => User::USER_TYPE_PARTNER], false];
        yield [['userType' => User::USER_TYPE_TRANSPORT_MANAGER], false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsLocalAuthority')]
    public function testIsLocalAuthority($userData, $expected): void
    {
        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals($expected, $sut->isLocalAuthority());
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | bool)>>
     *
     * @psalm-return list{list{array<never, never>, false}, list{array{userType: 'anon'}, false}, list{array{userType: 'operator'}, false}, list{array{userType: 'local-authority'}, true}, list{array{userType: 'partner'}, false}, list{array{userType: 'transport-manager'}, false}}
     */
    public static function provideIsLocalAuthority(): \Iterator
    {
        yield [[], false];
        yield [['userType' => User::USER_TYPE_ANON], false];
        yield [['userType' => User::USER_TYPE_OPERATOR], false];
        yield [['userType' => User::USER_TYPE_LOCAL_AUTHORITY], true];
        yield [['userType' => User::USER_TYPE_PARTNER], false];
        yield [['userType' => User::USER_TYPE_TRANSPORT_MANAGER], false];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsPartner')]
    public function testIsPartner($userData, $expected): void
    {
        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals($expected, $sut->isPartner());
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | bool)>>
     *
     * @psalm-return list{list{array<never, never>, false}, list{array{userType: 'anon'}, false}, list{array{userType: 'operator'}, false}, list{array{userType: 'local-authority'}, false}, list{array{userType: 'partner'}, true}, list{array{userType: 'transport-manager'}, false}}
     */
    public static function provideIsPartner(): \Iterator
    {
        yield [[], false];
        yield [['userType' => User::USER_TYPE_ANON], false];
        yield [['userType' => User::USER_TYPE_OPERATOR], false];
        yield [['userType' => User::USER_TYPE_LOCAL_AUTHORITY], false];
        yield [['userType' => User::USER_TYPE_PARTNER], true];
        yield [['userType' => User::USER_TYPE_TRANSPORT_MANAGER], false];
    }

    /**
     * @param $userData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsTransportManager')]
    public function testIsTransportManager($userData, $expected): void
    {
        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals($expected, $sut->isTransportManager());
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | bool)>>
     *
     * @psalm-return list{list{array<never, never>, false}, list{array{userType: 'anon'}, false}, list{array{userType: 'operator'}, false}, list{array{userType: 'local-authority'}, false}, list{array{userType: 'partner'}, false}, list{array{userType: 'transport-manager'}, true}}
     */
    public static function provideIsTransportManager(): \Iterator
    {
        yield [[], false];
        yield [['userType' => User::USER_TYPE_ANON], false];
        yield [['userType' => User::USER_TYPE_OPERATOR], false];
        yield [['userType' => User::USER_TYPE_LOCAL_AUTHORITY], false];
        yield [['userType' => User::USER_TYPE_PARTNER], false];
        yield [['userType' => User::USER_TYPE_TRANSPORT_MANAGER], true];
    }

    /**
     * @param $userData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideGetUniqueId')]
    public function testGetUniqueId($userData, $expected): void
    {
        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals($expected, $sut->getUniqueId());
    }

    /**
     * @return \Iterator<(int | string), array<(array<string> | string)>>
     *
     * @psalm-return list{list{array<never, never>, ''}, list{array{userType: 'anon'}, ''}, list{array{userType: 'operator', loginId: 'testing'}, string}}
     */
    public static function provideGetUniqueId(): \Iterator
    {
        yield [[], ''];
        yield [['userType' => User::USER_TYPE_ANON], ''];
        yield [['userType' => User::USER_TYPE_OPERATOR, 'loginId' => 'testing'], hash('sha256', 'testing1234')];
    }

    public function testGetNumberOfVehicles(): void
    {
        $userData = [
            'numberOfVehicles' => 25
        ];
        $identity = new User();
        $identity->setUserData($userData);

        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertEquals(25, $sut->getNumberOfVehicles());
    }

    public function testIsInternalUser(): void
    {
        $mockAuthService = m::mock(AuthorizationService::class);
        $mockAuthService->shouldReceive('getIdentity')->andReturn(new User());
        $mockAuthService->shouldReceive('isGranted')
            ->with('internal-user')
            ->andReturn(true);

        $sut = new CurrentUser($mockAuthService, '1234');

        $this->assertTrue($sut->isInternalUser());
    }
}
