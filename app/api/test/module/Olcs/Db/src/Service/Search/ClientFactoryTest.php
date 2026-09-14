<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Db\Service\Search;

use Dvsa\Olcs\Db\Service\Search\ClientFactory;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use OpenSearch\Client;
use Psr\Container\ContainerInterface;

final class ClientFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn(
            ['elastic_search' => ['host' => 'google.com', 'port' => 4034]]
        );

        $sut = new ClientFactory();
        $service = $sut->__invoke($mockSl, Client::class);

        $this->assertInstanceOf(Client::class, $service);
    }

    public function testInvokeWithException(): void
    {
        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn([]);

        $sut = new ClientFactory();
        $passed = false;
        try {
            $service = $sut->__invoke($mockSl, Client::class);
        } catch (InvalidServiceException $e) {
            if ($e->getMessage() === 'Elastic search config not found') {
                $passed = true;
            }
        }

        $this->assertTrue($passed, 'Expected exception not thrown or message didn\'t match');
    }

    public function testHttpClientOptionsBuildsBaseUriFromTransportHostAndPort(): void
    {
        $sut = new ClientFactory();

        $this->assertSame(
            ['base_uri' => 'https://searchv6.example.com:443'],
            $sut->getHttpClientOptions(['host' => 'searchv6.example.com', 'port' => '443', 'transport' => 'Https'])
        );
    }

    public function testHttpClientOptionsDefaultsToHttpOnPort9200(): void
    {
        $sut = new ClientFactory();

        $this->assertSame(
            ['base_uri' => 'http://localhost:9200'],
            $sut->getHttpClientOptions([])
        );
    }

    public function testHttpClientOptionsPassesCurlOptionsThrough(): void
    {
        $sut = new ClientFactory();

        $options = $sut->getHttpClientOptions(
            ['host' => 'h', 'curl' => [CURLOPT_SSL_VERIFYHOST => false]]
        );

        $this->assertSame([CURLOPT_SSL_VERIFYHOST => false], $options['curl']);
    }

    public function testHttpClientOptionsAddsRequestLoggingMiddlewareWhenLogIsSet(): void
    {
        $sut = new ClientFactory();

        $this->assertArrayNotHasKey('middleware', $sut->getHttpClientOptions(['host' => 'h']));

        $options = $sut->getHttpClientOptions(['host' => 'h', 'log' => true]);
        $this->assertCount(1, $options['middleware']);
        $this->assertIsCallable($options['middleware'][0]);
    }
}
