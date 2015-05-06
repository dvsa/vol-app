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
     * Tests tmReputeUrl
     * @dataProvider fetchTmReputeUrlProvider
     */
    public function testFetchTmReputeUrl($nrResponseData, $expectedResponse)
    {
        $tmId = 3;

        $nrResponseJson = m::mock(Response::class);
        $nrResponseJson->shouldReceive('getContent')->andReturn(Json::encode($nrResponseData));

        $uriMock = m::mock(HttpUri::class);
        $uriMock->shouldReceive('setPath')->with('/repute/url/' . $tmId);

        $restClient = m::mock(RestClient::class);
        $restClient->shouldReceive('getUri')->andReturn($uriMock);
        $restClient->shouldReceive('send')->andReturn($nrResponseJson);

        $sut = new RestHelper();
        $sut->setRestClient($restClient);

        $response = $sut->fetchTmReputeUrl($tmId);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * data provider for testFetchTmReputeUrl
     */
    public function fetchTmReputeUrlProvider()
    {
        return [
            [
                [
                    'Response' => [
                        'Data' => [
                            'url' => 'http://www.example.com'
                        ]
                    ],
                ],
                'http://www.example.com'
            ],
            [
                [
                    'Response' => [
                        'Data' => []
                    ],
                ],
                null
            ]
        ];
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
