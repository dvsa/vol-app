<?php

namespace Dvsa\OlcsTest\Api\Service\Nr;

use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use Dvsa\Olcs\Api\Service\Nr\InrClient;
use Dvsa\Olcs\Api\Service\Nr\InrClientFactory;
use Dvsa\Olcs\Api\Service\Nr\InrClientInterface;
use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Laminas\Http\Client as RestClient;
use Laminas\Http\Client\Adapter\Curl;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

class InrClientFactoryTest extends TestCase
{
    public function testCreateServiceNoConfig()
    {
        $this->expectException(\RuntimeException::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn([]);

        $sut = new InrClientFactory();
        $sut->__invoke($mockSl, InrClient::class);
    }

    public function testCreateService()
    {
        $oauth2 = 'options';
        $config = [
            'nr' => [
                'inr_service' => [
                    'uri' => 'http://testServiceAddress',
                    'adapter' => Curl::class,
                    'options' => []
                ],
                'oauth2' => $oauth2

            ]
        ];

        $restClient = m::mock(RestClient::class)
            ->shouldReceive('getHeaders')->andReturn(['Authorization' => 'Bearer token']);

        $mockTokenProvider = m::mock(Provider::class)
            ->expects('getToken')->withNoArgs()->andReturn('token');


        $mockSl = m::mock(ServiceLocatorInterface::class);
        var_dump($mockSl);
        $mockSl->shouldReceive('get')->with('config')->andReturn($config);
        $mockSl->shouldReceive('get')->with(RestClient::class)->andReturn($restClient);
        $mockSl->shouldReceive('build')->with(Provider::class, $oauth2)->andReturn($mockTokenProvider);

        $sut = new InrClientFactory();

        /** @var InrClient $service */
        $service = $sut->__invoke($mockSl, InrClient::class);

        $restClient = $service->getRestClient();
        $wrapper = $restClient->getAdapter();
        $curl = $wrapper->getAdapter();


        $this->assertInstanceOf(InrClientInterface::class, $service);
        $this->assertInstanceOf(RestClient::class, $restClient);
        $this->assertInstanceOf(ClientAdapterLoggingWrapper::class, $wrapper);
        $this->assertInstanceOf(Curl::class, $curl);
        $this->assertEquals('Bearer token', $restClient->getHeader('Authorization'));

    }
}
