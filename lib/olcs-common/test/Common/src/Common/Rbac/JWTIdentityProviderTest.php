<?php

declare(strict_types=1);

namespace CommonTest\Common\Rbac;

use Common\Auth\Service\RefreshTokenService;
use Common\Rbac\JWTIdentityProvider;
use Common\Rbac\User;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Cqrs\Response;
use Common\Test\MocksServicesTrait;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Laminas\Authentication\Storage\Session;
use Laminas\Http\Response as HttpResponse;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Session\Container;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;

class JWTIdentityProviderTest extends MockeryTestCase
{
    use MocksServicesTrait;

    public const DATA_WITH_ROLES = [
        'userType' => 'user_type',
        'loginId' => 'login_id',
        'id' => 1,
        'roles' => [
            ['role' => 'role1'],
            ['role' => 'role2'],
            ['role' => 'role3'],
        ]
    ];

    public const DATA_WITHOUT_ROLES = [
        'userType' => 'user_type',
        'loginId' => 'login_id',
        'id' => 1
    ];

    public const TOKEN_SESSION_DATA = [
        'Token' => [
            'refreshToken' => 'abc1234'
        ],
        'AccessTokenClaims' => [
            'username' => 'username'
        ]
    ];

    private array $tokenExpired = [
        'Token' => [
            'expires' => 0,
            'refreshToken' => 'abc1234',
        ],
    ];

    private array $tokenWrongUser = [
        'Token' => [
            'expires' => 99999999999999999999999999999999999999,
            'refreshToken' => 'abc1234',
        ],
        'AccessTokenClaims' => [
            'username' => 'username',
        ],
    ];

    private array $tokenCorrectUser = [
        'Token' => [
            'expires' => 99999999999999999999999999999999999999,
            'refreshToken' => 'abc1234',
        ],
        'AccessTokenClaims' => [
            'username' => 'usr999',
        ],
    ];

    private int $defaultUserId = 999;

    private string $defaultLoginId = 'usr999';

    private string $defaultUserType = 'user_type';

    private array $defaultUserData = [
        'id' => 999,
        'loginId' => 'usr999',
        'userType' => 'user_type',
        'roles' => [],
    ];

    /**
     * @var JWTIdentityProvider
     */
    protected $sut;

    /**
     * @test
     */
    public function validateTokenShouldReturnFalseWhenAnonymous(): void
    {
        $this->setupSut();

        $identity = $this->identity();
        $identity->expects('isAnonymous')->andReturnTrue();

        $this->identitySessionWithCachedIdentity($identity);
        $this->tokenSession()->expects('isEmpty')->twice()->andReturnTrue();

        $result = $this->sut->validateToken();

        // Execute
        $this->assertFalse($result['valid']);
    }

    /**
     * @test
     */
    public function validateTokenShouldReturnFalseWhenEmptyToken(): void
    {
        $this->setupSut();

        $identity = $this->identity();
        $identity->expects('isAnonymous')->andReturnFalse();

        $this->identitySessionWithCachedIdentity($identity);
        $this->tokenSession()->expects('isEmpty')->times(3)->andReturnTrue();

        $result = $this->sut->validateToken();

        // Execute
        $this->assertFalse($result['valid']);
    }

    /**
     * @test
     */
    public function validateTokenShouldReturnFalseWhenCantReadToken(): void
    {
        $this->setupSut();

        $identity = $this->identity();
        $identity->expects('isAnonymous')->andReturnFalse();

        $this->identitySessionWithCachedIdentity($identity);
        $this->tokenSession()->expects('isEmpty')->times(3)->andReturnFalse();
        $this->tokenSession()->expects('read')->times(5)->andReturn([]);

        $result = $this->sut->validateToken();

        // Execute
        $this->assertFalse($result['valid']);
    }

    /**
     * @test
     */
    public function validateTokenShouldReturnFalseWhenExpiredToken(): void
    {
        $this->setupSut();

        $identity = $this->identity();
        $identity->expects('isAnonymous')->andReturnFalse();

        $this->identitySessionWithCachedIdentity($identity);
        $this->tokenSession()->expects('isEmpty')->times(3)->andReturnFalse();
        $this->tokenSession()->expects('read')->times(5)->andReturn($this->tokenExpired);

        $this->refreshTokenService()->expects('isRefreshRequired')
            ->with($this->tokenExpired['Token'])
            ->twice()
            ->andReturnFalse();

        $result = $this->sut->validateToken();

        // Execute
        $this->assertFalse($result['valid']);
    }

    /**
     * @test
     */
    public function validateTokenShouldReturnFalseWhenTokenHasWrongUser(): void
    {
        $this->setupSut();

        $identity = $this->identity();
        $identity->expects('isAnonymous')->andReturnFalse();
        $identity->expects('getUsername')->andReturn($this->defaultLoginId);

        $this->identitySessionWithCachedIdentity($identity);
        $this->tokenSession()->expects('isEmpty')->times(3)->andReturnFalse();
        $this->tokenSession()->expects('read')->times(6)->andReturn($this->tokenWrongUser);

        $this->refreshTokenService()->expects('isRefreshRequired')
            ->with($this->tokenWrongUser['Token'])
            ->twice()
            ->andReturnFalse();

        $result = $this->sut->validateToken();

        // Execute
        $this->assertFalse($result['valid']);
    }

    /**
     * @test
     */
    public function validateTokenShouldReturnTrueWhenValid(): void
    {
        $this->setupSut();

        $identity = $this->identity();
        $identity->expects('isAnonymous')->andReturnFalse();
        $identity->expects('getUsername')->andReturn($this->defaultLoginId);

        $this->identitySessionWithCachedIdentity($identity);
        $this->tokenSession()->expects('isEmpty')->times(3)->andReturnFalse();
        $this->tokenSession()->expects('read')->times(6)->andReturn($this->tokenCorrectUser);

        $this->refreshTokenService()->expects('isRefreshRequired')
            ->with($this->tokenCorrectUser['Token'])
            ->twice()
            ->andReturnFalse();

        $result = $this->sut->validateToken();

        // Execute
        $this->assertTrue($result['valid']);
        $this->assertEquals($result['uid'], $this->defaultLoginId);
    }

    /**
     * @test
     */
    public function getIdentityShouldRetriveDataFromCacheWhenShouldntUpdateAndCacheExists(): void
    {
        $this->setupSut();

        $session = $this->identitySession();
        $session->allows('offsetGet')->with('identity')->andReturnUsing(static function () {
            $user = new User();
            $user->setId(1);
            return $user;
        });

        $cacheService = $this->cacheService();
        $cacheService->allows('hasCustomItem')->andReturnTrue();

        $cacheService->expects('getCustomItem')->andReturn(static::DATA_WITHOUT_ROLES)->once();

        // Execute
        $this->sut->getIdentity();
    }

    /**
     * @test
     */
    public function getIdentityShouldRetriveDataFromDBWhenIdentityIsNotInstanceOfUser(): void
    {
        // Setup
        $this->setupSut();

        $session = $this->identitySession();
        $session->allows('offsetGet')->with('identity')->andReturnNull();

        // Expectations
        $querySender = $this->querySender();
        $querySender->expects('send')->once()->andReturn($this->response());

        // Execute
        $this->sut->getIdentity();
    }

    /**
     * @test
     */
    public function getIdentityShouldRetriveDataFromDBWhenIdentityHasNoId(): void
    {
        // Setup
        $this->setupSut();

        $session = $this->identitySession();
        $session->allows('offsetGet')->with('identity')->andReturn(new User());

        // Expectations
        $querySender = $this->querySender();
        $querySender->expects('send')->once()->andReturn($this->response());

        // Execute
        $this->sut->getIdentity();
    }

    /**
     * @test
     */
    public function getIdentityShouldRetriveDataFromDBWhenCacheDoesntExist(): void
    {
        // Setup
        $this->setupSut();

        $session = $this->identitySession();
        $session->allows('offsetGet')->with('identity')->andReturnUsing(static function () {
            $user = new User();
            $user->setId(1);
            return $user;
        });

        $cacheService = $this->cacheService();
        $cacheService->allows('hasCustomItem')->andReturnFalse();

        // Expectations
        $querySender = $this->querySender();
        $querySender->expects('send')->once()->andReturn($this->response());

        // Execute
        $this->sut->getIdentity();
    }

    /**
     * @test
     */
    public function getIdentityShouldStoreIdentityInTheSession(): void
    {
        // Setup
        $this->setupSut();

        // Expectations
        $session = $this->identitySession();
        $session->expects('offsetSet')->withSomeOfArgs('identity')->once();

        // Execute
        $this->sut->getIdentity();
    }

    /**
     * @test
     */
    public function getIdentityShouldReturnInstanceofUser(): void
    {
        // Setup
        $this->setupSut();

        // Execute
        $identity = $this->sut->getIdentity();

        // Assertions
        $this->assertInstanceOf(User::class, $identity);
    }

    /**
     * @test
     */
    public function getIdentityShouldReturnInstanceofUserWithRoles(): void
    {
        // Setup
        $this->setupSut();

        $querySender = $this->querySender();
        $querySender->expects('send')->andReturn($this->response(true, static::DATA_WITH_ROLES));

        // Execute
        $identity = $this->sut->getIdentity();

        // Assertions
        $this->assertInstanceOf(User::class, $identity);
        $this->assertSame(['role1', 'role2', 'role3'], $identity->getRoles());
    }

    /**
     * @test
     */
    public function getIdentityShouldRefreshTokensWhenRequired(): void
    {
        // Setup
        $this->setupSut();

        $tokenSession = $this->tokenSession();
        $tokenSession->allows('read')->andReturn(static::TOKEN_SESSION_DATA);

        // Expectations
        $refreshService = $this->refreshTokenService();
        $refreshService->expects('isRefreshRequired')->andReturnTrue();

        $tokenSession->expects('write')->with([]);

        // Execute
        $this->sut->getIdentity();
    }

    /**
     * @test
     */
    public function getIdentityShouldNotRefreshTokensWhenTokenSessionIsEmpty(): void
    {
        // Setup
        $this->setupSut();

        $tokenSession = $this->tokenSession();
        $tokenSession->allows('isEmpty')->andReturnTrue();

        // Expectations
        $refreshService = $this->refreshTokenService();
        $refreshService->shouldNotReceive('isRefreshRequired');

        $tokenSession->shouldNotReceive('write');

        // Execute
        $this->sut->getIdentity();
    }

    /**
     * @test
     */
    public function getIdentityShouldReturnExistingIdentityWhenPresent(): void
    {
        // Setup
        $this->setupSut();

        // Expectations

        $session = $this->identitySession();
        $session->expects('offsetGet')->with('identity')->once();

        // Execute
        $this->sut->getIdentity();
        $this->sut->getIdentity();
    }

    /**
     * @test
     */
    public function clearSessionShouldClearSession(): void
    {
        $this->setupSut();
        $this->identitySession()->expects('exchangeArray')->with([]);
        $this->tokenSession()->expects('clear')->withNoArgs();
        $this->sut->clearSession();
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setupSut(): void
    {
        $this->sut = new JWTIdentityProvider(
            $this->identitySession(),
            $this->querySender(),
            $this->cacheService(),
            $this->refreshTokenService(),
            $this->tokenSession()
        );
    }

    #[\Override]
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager
    {
        $this->cacheService();
        $this->querySender();
        $this->identitySession();
        $this->tokenSession();
        return $serviceManager;
    }

    /**
     * @return MockInterface|CacheEncryption
     */
    private function cacheService()
    {
        if (!$this->serviceManager->has(CacheEncryption::class)) {
            $instance = $this->setUpMockService(CacheEncryption::class);
            $this->serviceManager->setService(CacheEncryption::class, $instance);
        }
        return $this->serviceManager->get(CacheEncryption::class);
    }

    /**
     * @return MockInterface|QuerySender
     */
    private function querySender()
    {
        if (!$this->serviceManager->has(QuerySender::class)) {
            $instance = $this->setUpMockService(QuerySender::class);
            $instance->allows('send')->andReturn($this->response())->byDefault();
            $this->serviceManager->setService(QuerySender::class, $instance);
        }
        return $this->serviceManager->get(QuerySender::class);
    }

    private function identity(): MockInterface
    {
        $identity = m::mock(User::class);
        $identity->expects('getId')->times(3)->andReturn($this->defaultUserId);

        $identity->expects('setUserType')->with($this->defaultUserType);
        $identity->expects('setUsername')->with($this->defaultLoginId);
        $identity->expects('setUserData')->with($this->defaultUserData);

        return $identity;
    }

    private function identitySessionWithCachedIdentity(MockInterface $identity): void
    {
        $this->identitySession()->expects('offsetGet')->with('identity')->andReturn($identity);
        $this->identitySession()->expects('offsetSet')->with('identity', $identity);
        $this->cacheService()->expects('hasCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $this->defaultUserId)
            ->andReturnTrue();
        $this->cacheService()->expects('getCustomItem')
            ->with(CacheEncryption::USER_ACCOUNT_IDENTIFIER, $this->defaultUserId)
            ->andReturn($this->defaultUserData);
    }

    /**
     * @return MockInterface|Container
     */
    private function identitySession()
    {
        if (!$this->serviceManager->has(Container::class)) {
            $instance = $this->setUpMockService(Container::class);
            $this->serviceManager->setService(Container::class, $instance);
        }
        return $this->serviceManager->get(Container::class);
    }

    /**
     * @return MockInterface|RefreshTokenService
     */
    protected function refreshTokenService()
    {
        if (!$this->serviceManager->has(RefreshTokenService::class)) {
            $instance = $this->setUpMockService(RefreshTokenService::class);
            $this->serviceManager->setService(RefreshTokenService::class, $instance);
        }

        return $this->serviceManager->get(RefreshTokenService::class);
    }

    /**
     * @return MockInterface|Session
     */
    protected function tokenSession()
    {
        if (!$this->serviceManager->has(Session::class)) {
            $instance = $this->setUpMockService(Session::class);
            $this->serviceManager->setService(Session::class, $instance);
        }

        return $this->serviceManager->get(Session::class);
    }

    private function response(bool $isSuccess = false, array $result = []): Response
    {
        $httpResponse = new HttpResponse();
        $httpResponse->setStatusCode($isSuccess ? 200 : 500);

        $response = new Response($httpResponse);
        $response->setResult($result);

        return $response;
    }
}
