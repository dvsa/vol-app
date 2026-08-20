<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Client;

use Dvsa\Olcs\Cpms\Authenticate\GatewayTokenProviderInterface;
use Dvsa\Olcs\Cpms\Client\ClientOptions;
use Dvsa\Olcs\Cpms\Client\HttpClient;
use Dvsa\Olcs\Cpms\Client\HttpClientFactory;
use GuzzleHttp\Client;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Log\NullLogger;

final class HttpClientFactoryTest extends TestCase
{
    use ClientOptionsTestTrait;

    public function testCreateHttpClient(): void
    {
        $sut = new HttpClientFactory(
            $this->getClientOptions(),
            new NullLogger()
        );
        $client = $sut->createHttpClient();
        $this->assertInstanceOf(HttpClient::class, $client);
        $this->assertFalse($client->hasGatewayTokenProvider());
        $this->assertNull($this->getGuzzleConfig($client, 'proxy'));
    }

    public function testCreateHttpClientWithProxyAndTokenProvider(): void
    {
        $options = new ClientOptions(
            2,
            'client_credentials',
            15.0,
            'gw.cpms.domain',
            ['Accept' => 'application/json'],
            'http://proxy.local:3128'
        );

        $sut = new HttpClientFactory(
            $options,
            new NullLogger(),
            m::mock(GatewayTokenProviderInterface::class)
        );

        $client = $sut->createHttpClient();

        $this->assertTrue($client->hasGatewayTokenProvider());
        $this->assertSame('http://proxy.local:3128', $this->getGuzzleConfig($client, 'proxy'));
        $this->assertSame('http://proxy.local:3128', $options->getProxy());
    }

    private function getGuzzleConfig(HttpClient $client, string $key): mixed
    {
        $reflection = new \ReflectionProperty(HttpClient::class, 'client');
        /** @var Client $guzzle */
        $guzzle = $reflection->getValue($client);
        return $guzzle->getConfig($key);
    }
}
