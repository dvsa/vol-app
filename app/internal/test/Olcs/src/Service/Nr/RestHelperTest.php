<?php

namespace OlcsTest\Service\Nr;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Service\Nr\RestHelper;
use Zend\Http\Client as RestClient;
use Zend\Http\Response;
use Zend\Uri\Http as HttpUri;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Json\Json;

/**
 * Class RestHelperTest
 * @package OlcsTest\Service\Nr
 */
class RestHelperTest extends TestCase
{
    /**
     * Tests sendErruResponse
     */
    public function testSendErruResponse()
    {
        $caseId = 29;
        $expectedResponse = new Response();

        $uriMock = m::mock(HttpUri::class);
        $uriMock->shouldReceive('setPath')->with('/msi/send/' . $caseId);

        $restClient = m::mock(RestClient::class);
        $restClient->shouldReceive('getUri')->andReturn($uriMock);
        $restClient->shouldReceive('send')->andReturn($expectedResponse);

        $sut = new RestHelper();
        $sut->setRestClient($restClient);

        $response = $sut->sendErruResponse($caseId);

        $this->assertEquals($expectedResponse, $response);
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateServiceNoConfig()
    {
        $mockSl = m::mock(ServiceLocatorInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn([]);

        $sut = new RestHelper();
        $sut->createService($mockSl);
    }

    /**
     * Tests createService
     */
    public function testCreateService()
    {
        $config = [
            'service_api_mapping' => [
                'endpoints' => [
                    'nr' => 'http://www.example.com'
                ]
            ]
        ];

        $mockSl = m::mock(ServiceLocatorInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($config);

        $sut = new RestHelper();
        $service = $sut->createService($mockSl);

        $this->assertInstanceOf(RestHelper::class, $service);
        $this->assertInstanceOf(RestClient::class, $service->getRestClient());
    }
}
