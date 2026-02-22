<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Service;

use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProvider;
use Dvsa\Olcs\Cpms\Authenticate\CpmsIdentityProviderFactory;
use Dvsa\Olcs\Cpms\Client\HttpClient;
use Dvsa\Olcs\Cpms\Service\ApiService;
use Dvsa\OlcsTest\Cpms\Client\ClientOptionsTestTrait;
use Dvsa\OlcsTest\Cpms\Client\GuzzleTestTrait;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

class ApiServiceTest extends TestCase
{
    use ClientOptionsTestTrait;
    use GuzzleTestTrait;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var ApiService
     */
    private $sut;

    /**
     * @var CpmsIdentityProvider
     */
    private $identity;

    /**
     * @var false|string
     */
    private $accessTokenResponse;

    public function setUp(): void
    {
        $userId = '555';
        $clientId = 'a-client-id';
        $clientSecret = 'a-client-secret';
        $identityFactory = new CpmsIdentityProviderFactory($clientId, $clientSecret, $userId);
        $this->identity = $identityFactory->createCpmsIdentityProvider();

        $this->logger = new Logger('cpms_client_logger');
        $this->logger->pushHandler(new TestHandler());

        $this->httpClient = new HttpClient(
            $this->setUpMockClient(),
            $this->getClientOptions(),
            $this->logger
        );

        $this->sut = new ApiService(
            $this->httpClient,
            $this->identity,
            $this->logger
        );

        $this->accessTokenResponse = json_encode([
            "expires_in" => 3600,
            "token_type" => "Bearer",
            "access_token" => "LKAJDA01KDJKDK32AJNDK212AJ",
            "scope" => "QUERY_TXN",
            "sales_reference" => "LOCAL-76"
        ]);
    }

    public function testGet(): void
    {
        $getRequestResponseBody = json_encode(['payment_status' => 'success']);

        $this->appendToHandler(200, [], $this->accessTokenResponse);
        $this->appendToHandler(200, [], $getRequestResponseBody);

        $params = [
            'required_fields' => [
                'payment' => [
                    'payment_status'
                ],
            ],
            'payment_data' => [
                [
                    'sales_reference' => '1234455'
                ]
            ]
        ];

        $response = $this->sut->get('/get/payment-status/', 'CARD', $params);

        $this->assertEquals(['payment_status' => 'success'], $response);
        $this->assertEquals('GET', $this->getLastRequest()->getMethod());
        $this->assertEquals('api.cpms.domain/get/payment-status/', $this->getLastRequest()->getUri()->getPath());
        $this->assertEquals(
            'api.cpms.domain/get/payment-status/?required_fields%5Bpayment%5D%5B0%5D=payment_status&payment_data%5B0%5D%5Bsales_reference%5D=1234455',
            $this->getLastRequest()->getRequestTarget()
        );
        $this->assertEquals(
            ['application/vnd.dvsa-gov-uk.v2; charset=UTF-8'],
            $this->getLastRequest()->getHeader('Content-Type')
        );
        $this->assertEquals(['application/json'], $this->getLastRequest()->getHeader('Accept'));
        $this->assertEquals(['Bearer LKAJDA01KDJKDK32AJNDK212AJ'], $this->getLastRequest()->getHeader('Authorization'));
    }

    public function testPost(): void
    {
        $postRequestResponseBody = json_encode(['status' => 'success']);

        $this->appendToHandler(200, [], $this->accessTokenResponse);
        $this->appendToHandler(200, [], $postRequestResponseBody);

        $params = [
            'batch_number' => 'abc123',
            'total_amount' => 200,
            'payment_data' => []
        ];

        $response = $this->sut->post('/post/payment', 'CARD', $params);

        $this->assertEquals('POST', $this->getLastRequest()->getMethod());
        $this->assertEquals('api.cpms.domain/post/payment', $this->getLastRequest()->getRequestTarget());
        $this->assertEquals(json_encode($params), $this->getLastRequest()->getBody()->getContents());
        $this->assertEquals(
            ['application/vnd.dvsa-gov-uk.v2+json; charset=UTF-8'],
            $this->getLastRequest()->getHeader('Content-Type')
        );
        $this->assertEquals(['application/json'], $this->getLastRequest()->getHeader('Accept'));
        $this->assertEquals(['status' => 'success'], $response);
        $this->assertEquals(['Bearer LKAJDA01KDJKDK32AJNDK212AJ'], $this->getLastRequest()->getHeader('Authorization'));
    }

    public function testPut(): void
    {
        $postRequestResponseBody = json_encode(['status' => 'success']);

        $this->appendToHandler(200, [], $this->accessTokenResponse);
        $this->appendToHandler(200, [], $postRequestResponseBody);

        $params = [
            'batch_number' => 'aaa123',
            'overpayment_amount' => 100
        ];

        $response = $this->sut->put('/put/payment', 'CARD', $params);

        $this->assertEquals('PUT', $this->getLastRequest()->getMethod());
        $this->assertEquals('api.cpms.domain/put/payment', $this->getLastRequest()->getRequestTarget());
        $this->assertEquals(json_encode($params), $this->getLastRequest()->getBody()->getContents());
        $this->assertEquals(
            ['application/vnd.dvsa-gov-uk.v2+json; charset=UTF-8'],
            $this->getLastRequest()->getHeader('Content-Type')
        );
        $this->assertEquals(['application/json'], $this->getLastRequest()->getHeader('Accept'));
        $this->assertEquals(['status' => 'success'], $response);
        $this->assertEquals(['Bearer LKAJDA01KDJKDK32AJNDK212AJ'], $this->getLastRequest()->getHeader('Authorization'));
    }

    public function testGetCpmsAccessTokenSuccessful(): void
    {
        $this->appendToHandler(200, [], $this->accessTokenResponse);
        $this->appendToHandler(200, [], json_encode(['status' => 'successful']));

        $params = [
            'batch_number' => 'abc123',
            'total_amount' => 200,
            'payment_data' => []
        ];

        $response = $this->sut->get('/get/payment-status', 'CARD', $params);

        $this->assertEquals(['status' => 'successful'], $response);
    }

    public function testGetCpmsAccessTokenFailureWithSuccessfulResponse(): void
    {
        $this->appendToHandler(200, [], json_encode(['code' => '105', 'message' => 'failed']));

        $params = [
            'batch_number' => 'abc123',
            'total_amount' => 200,
            'payment_data' => []
        ];

        $response = $this->sut->get('/get/payment-status', 'CARD', $params);

        $this->assertEquals(['message' => 'failed', 'code' => 105], $response);
    }

    public function testGetCpmsAccessTokenFailureWithBadResponse(): void
    {

        $this->appendToHandler(400, [], json_encode(['code' => '105', 'message' => 'cannot process payment']));

        $params = [
            'batch_number' => 'abc123',
            'total_amount' => 200,
            'payment_data' => []
        ];

        $response = $this->sut->get('/get/payment-status', 'CARD', $params);

        $this->assertEquals('cannot process payment', $response);
    }

    public function testGetCpmsAccessTokenFailureWithNonGuzzleException(): void
    {
        $mockHttpClient = m::mock(HttpClient::class);
        $mockHttpClient->shouldReceive('getClientOptions')->andReturn($this->getClientOptions());
        $mockHttpClient->shouldReceive('post')->andThrow(\Exception::class, 'This is a non-guzzle exception');

        $sut = new ApiService(
            $mockHttpClient,
            $this->identity,
            $this->logger
        );

        $params = [
            'batch_number' => 'abc123',
            'total_amount' => 200,
            'payment_data' => []
        ];

        $response = $sut->get('/get/payment-status', 'CARD', $params);
        $this->assertEquals('This is a non-guzzle exception', $response);
    }

    public function testReturnErrorMessageWhenResponse503(): void
    {
        $this->appendToHandler(503, [], json_encode(['code' => '105', 'message' => null]));

        $params = [
            'batch_number' => 'abc123',
            'total_amount' => 200,
            'payment_data' => []
        ];

        $response = $this->sut->get('/get/payment-status', 'CARD', $params);
        $this->assertStringContainsString("503 Service Unavailable", $response);
    }

    public function testEmptyResponse(): void
    {
         $this->appendToHandler(200, [], $this->accessTokenResponse);
         $this->appendToHandler(200, [], '');

        $params = [
            'batch_number' => 'abc123',
            'total_amount' => 200,
            'payment_data' => []
        ];

        $response = $this->sut->get('/get/payment-status', 'CARD', $params);

        $this->assertEquals("CPMS error empty body", $response);
    }



    public function testReturnErrorMessageWhenResponseTimeout(): void
    {
        $this->mockHandler->append(new RequestException("Error: Request time out", new Request('GET', '/some-uri')));

        $params = [
            'batch_number' => 'abc123',
            'total_amount' => 200,
            'payment_data' => []
        ];

        $response = $this->sut->get('/get/payment-status', 'CARD', $params);

        $this->assertEquals(
            'Error: Request time out',
            $response
        );
    }
}
