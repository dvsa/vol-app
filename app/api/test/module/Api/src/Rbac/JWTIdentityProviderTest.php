<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Rbac;

use Dvsa\Authentication\Cognito\Client;
use Dvsa\Contracts\Auth\Exceptions\InvalidTokenException;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Repository\User;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Rbac\JWTIdentityProvider;
use Dvsa\Olcs\Cli\Request\CliRequest;
use Firebase\JWT\ExpiredException;
use Laminas\Http\Headers;
use Laminas\Http\Request;
use Laminas\ServiceManager\ServiceManager;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\OlcsTest\MocksServicesTrait;

/**
 * Class JWTIdentityProviderTest
 *
 * @see JWTIdentityProvider
 */
class JWTIdentityProviderTest extends MockeryTestCase
{
    use MocksServicesTrait;

    /**
     * @var JWTIdentityProvider
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function getIdentityThrowsBadRequestExceptionWhenHeaderIncorrect(): void
    {
        // Setup
        $this->setUpSut();

        $headers = new Headers();
        $headers->addHeaders([JWTIdentityProvider::HEADER_NAME => 'wrong']);

        $request = $this->request();
        $request->setHeaders($headers);

        // Expectations
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(JWTIdentityProvider::MESSAGE_MALFORMED_BEARER);

        // Execute
        $this->sut->getIdentity();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getIdentityThrowsInvalidTokenExceptionWhenTokenIncorrect(): void
    {
        // Setup
        $this->setUpSut();

        $headers = new Headers();
        $headers->addHeaders([JWTIdentityProvider::HEADER_NAME => 'Bearer dsafdsa.fdsafdas.fdafda']);

        $request = $this->request();
        $request->setHeaders($headers);

        // Expectations

        $client = $this->client();
        $client->expects('decodeToken')->andThrow(InvalidTokenException::class);

        // Expectations
        $this->expectException(InvalidTokenException::class);

        // Execute
        $this->sut->getIdentity();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getIdentityReturnsAnonUserWhenTokenExpired(): void
    {
        // Setup
        $this->setUpSut();

        $headers = new Headers();
        $headers->addHeaders([JWTIdentityProvider::HEADER_NAME => 'Bearer dsafdsa.fdsafdas.fdafda']);

        $request = $this->request();
        $request->setHeaders($headers);

        $client = $this->client();
        $client->expects('decodeToken')->andThrow(new InvalidTokenException('', 1, new ExpiredException()));

        // Execute
        $identity = $this->sut->getIdentity();

        // Assert
        $this->assertSame('anon', $identity->getUser()->getLoginId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getIdentityReturnsAnonUserWhenHeaderMissing(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $identity = $this->sut->getIdentity();

        // Assert
        $this->assertSame('anon', $identity->getUser()->getLoginId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getIdentityReturnsAnonUserWhenNoUserFound(): void
    {
        // Setup
        $this->setUpSut();

        $headers = new Headers();
        $headers->addHeaders([JWTIdentityProvider::HEADER_NAME => 'Bearer dsafdsa.fdsafdas.fdafda']);

        $request = $this->request();
        $request->setHeaders($headers);

        // Expectations
        $client = $this->client();
        $client->expects('decodeToken')->andReturn(['username' => 'username']);

        // Execute
        $identity = $this->sut->getIdentity();

        // Assert
        $this->assertSame('anon', $identity->getUser()->getLoginId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getIdentityReturnsExpectedUserWhenUserExists(): void
    {
        // Setup
        $this->setUpSut();

        $headers = new Headers();
        $headers->addHeaders([JWTIdentityProvider::HEADER_NAME => 'Bearer dsafdsa.fdsafdas.fdafda']);

        $request = $this->request();
        $request->setHeaders($headers);

        $client = $this->client();
        $client->expects('decodeToken')->andReturn(['username' => 'username']);

        $user = UserEntity::create('', UserEntity::USER_TYPE_OPERATOR, ['loginId' => 'username']);
        $repo = $this->userRepository();
        $repo->expects('fetchEnabledIdentityByLoginId')->andReturn($user);

        // Execute
        $identity = $this->sut->getIdentity();

        // Assert
        $this->assertSame('username', $identity->getUser()->getLoginId());
        $this->assertSame(UserEntity::USER_TYPE_OPERATOR, $identity->getUser()->getUserType());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getIdentityReturnsSystemUserWhenConsoleRequest(): void
    {
        // Setup
        $sut = new JWTIdentityProvider($this->userRepository(), new CliRequest(), $this->client());

        $user = UserEntity::create('', UserEntity::USER_TYPE_INTERNAL, ['loginId' => 'systemUser']);

        $userRepo = $this->userRepository();
        $userRepo->expects('fetchById')->with(IdentityProviderInterface::SYSTEM_USER)->andReturn($user);

        $identity = $sut->getIdentity();

        self::assertSame(UserEntity::USER_TYPE_INTERNAL, $identity->getUser()->getUserType());
        self::assertSame('systemUser', $identity->getUser()->getLoginId());
    }

    public function setUp(): void
    {
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new JWTIdentityProvider(
            $this->userRepository(),
            $this->request(),
            $this->client()
        );
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): void
    {
        $this->userRepository();
        $this->request();
        $this->client();
    }

    /**
     * @return MockInterface|User
     */
    protected function userRepository(): mixed
    {
        if (!$this->serviceManager->has(User::class)) {
            $instance = $this->setUpMockService(User::class);
            $this->serviceManager->setService(User::class, $instance);
        }
        $instance = $this->serviceManager->get(User::class);
        return $instance;
    }

    /**
     * @return Request
     */
    protected function request(): mixed
    {
        if (!$this->serviceManager->has(Request::class)) {
            $instance = new Request();
            $this->serviceManager->setService(Request::class, $instance);
        }
        $instance = $this->serviceManager->get(Request::class);
        return $instance;
    }

    /**
     * @return MockInterface|Client
     */
    protected function client(): mixed
    {
        if (!$this->serviceManager->has(Client::class)) {
            $instance = $this->setUpMockService(Client::class);
            $this->serviceManager->setService(Client::class, $instance);
        }
        $instance = $this->serviceManager->get(Client::class);
        return $instance;
    }
}
