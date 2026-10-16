<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\AccessToken;

use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Logging\Log\Logger;
use Psr\Log\NullLogger;

final class ProviderTest extends TestCase
{
    #[\Override]
    public function setUp(): void
    {
        Logger::setLogger(new NullLogger());
    }

    public function testGetTokenFetchesLazilyAndCaches(): void
    {
        $oauthProvider = m::mock(AbstractProvider::class);
        $oauthProvider->shouldReceive('getAccessToken')
            ->once()
            ->with('client_credentials', ['scope' => 'a-scope'])
            ->andReturn($this->token('token-1', time() + 3600));

        $sut = new Provider($oauthProvider, 'a-scope', 'a-service');

        $this->assertSame('token-1', $sut->getToken());
        $this->assertSame('token-1', $sut->getToken());
    }

    public function testGetTokenRefreshesExpiredToken(): void
    {
        $oauthProvider = m::mock(AbstractProvider::class);
        $oauthProvider->shouldReceive('getAccessToken')
            ->twice()
            ->andReturn($this->token('token-1', time() - 10), $this->token('token-2', time() + 3600));

        $sut = new Provider($oauthProvider, 'a-scope');

        $this->assertSame('token-1', $sut->getToken());
        $this->assertSame('token-2', $sut->getToken());
    }

    public function testGetTokenRefreshesWithinExpiryMargin(): void
    {
        $oauthProvider = m::mock(AbstractProvider::class);
        $oauthProvider->shouldReceive('getAccessToken')
            ->twice()
            ->andReturn($this->token('token-1', time() + 30), $this->token('token-2', time() + 3600));

        $sut = new Provider($oauthProvider, 'a-scope');

        $this->assertSame('token-1', $sut->getToken());
        $this->assertSame('token-2', $sut->getToken());
    }

    public function testGetTokenTreatsNullExpiryAsNonExpiring(): void
    {
        $oauthProvider = m::mock(AbstractProvider::class);
        $oauthProvider->shouldReceive('getAccessToken')
            ->once()
            ->andReturn($this->token('token-1', null));

        $sut = new Provider($oauthProvider, 'a-scope');

        $this->assertSame('token-1', $sut->getToken());
        $this->assertSame('token-1', $sut->getToken());
    }

    public function testFetchFailureIsRethrown(): void
    {
        $oauthProvider = m::mock(AbstractProvider::class);
        $oauthProvider->shouldReceive('getAccessToken')
            ->andThrow(new IdentityProviderException('bad credentials', 401, ''));

        $sut = new Provider($oauthProvider, 'a-scope', 'a-service');

        $this->expectException(IdentityProviderException::class);
        $sut->getToken();
    }

    private function token(string $value, ?int $expires): AccessTokenInterface
    {
        $token = m::mock(AccessTokenInterface::class);
        $token->shouldReceive('getToken')->andReturn($value);
        $token->shouldReceive('getExpires')->andReturn($expires);
        return $token;
    }
}
