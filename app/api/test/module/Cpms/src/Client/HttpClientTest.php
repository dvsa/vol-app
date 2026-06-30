<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Client;

use Dvsa\Olcs\Cpms\Client\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Logging\Test\RecordingLogger;
use Psr\Log\LogLevel;

class HttpClientTest extends TestCase
{
    use GuzzleTestTrait;
    use ClientOptionsTestTrait;

    /**
     * @var HttpClient
     */
    private $sut;


    /**
     * @var RecordingLogger
     */
    private $logger;


    public function setUp(): void
    {
        $this->logger = new RecordingLogger();

        $this->sut = new HttpClient(
            $this->setUpMockClient(),
            $this->getClientOptions(),
            $this->logger
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function testGet(): void
    {
        $queryData = [
            'required_fields' => [
                'payment' => [
                    'payment_status'
                ]
            ]
        ];

        $encodedResponseBody = json_encode(['exampleGetReponseKey' => 'exampleGetResponseValue']);

        $this->appendToHandler(200, [], $encodedResponseBody);

        $response = $this->sut->get('/get-endpoint', $queryData);

        $this->assertEquals(['exampleGetReponseKey' => 'exampleGetResponseValue'], $response);
        $this->assertEquals('GET', $this->getLastRequest()->getMethod());
        $this->assertEquals('api.cpms.domain/get-endpoint', $this->getLastRequest()->getUri()->getPath());
        $this->assertEquals(
            'api.cpms.domain/get-endpoint?required_fields%5Bpayment%5D%5B0%5D=payment_status',
            $this->getLastRequest()->getRequestTarget()
        );
        $this->assertEquals(
            ['application/vnd.dvsa-gov-uk.v2; charset=UTF-8'],
            $this->getLastRequest()->getHeader('Content-Type')
        );
        $this->assertEquals(['application/json'], $this->getLastRequest()->getHeader('Accept'));
    }

    public function testPost(): void
    {
        $requestBody = ['postRequestBodyKeyExample' => 'postRequestBodyValueExample’'];
        $encodedResponseBody = json_encode(['examplePostReponseKey' => 'examplePostResponseValue']);

        $this->appendToHandler(200, [], $encodedResponseBody);

        $response = $this->sut->post('/post-endpoint', $requestBody);

        $this->assertEquals('POST', $this->getLastRequest()->getMethod());
        $this->assertEquals('api.cpms.domain/post-endpoint', $this->getLastRequest()->getRequestTarget());
        $this->assertEquals(
            ['application/vnd.dvsa-gov-uk.v2+json; charset=UTF-8'],
            $this->getLastRequest()->getHeader('Content-Type')
        );
        $this->assertEquals(['application/json'], $this->getLastRequest()->getHeader('Accept'));
        $this->assertEquals(['examplePostReponseKey' => 'examplePostResponseValue'], $response);
        // Check unsupported unicode chrs are sanitized from the payload
        $this->assertEquals($this->getLastRequest()->getBody()->getContents(), '{"postRequestBodyKeyExample":"postRequestBodyValueExample"}');
    }

    public function testPut(): void
    {
        $requestBody = ['putRequestBodyKeyExample' => 'putRequestBodyValueExample’'];
        $encodedResponseBody = json_encode(['examplePutReponseKey' => 'examplePutResponseValue']);

        $this->appendToHandler(200, [], $encodedResponseBody);

        $response = $this->sut->put('/put-endpoint', $requestBody);

        $this->assertEquals('PUT', $this->getLastRequest()->getMethod());
        $this->assertEquals('api.cpms.domain/put-endpoint', $this->getLastRequest()->getRequestTarget());
        $this->assertEquals(
            ['application/vnd.dvsa-gov-uk.v2+json; charset=UTF-8'],
            $this->getLastRequest()->getHeader('Content-Type')
        );
        $this->assertEquals(['application/json'], $this->getLastRequest()->getHeader('Accept'));
        $this->assertEquals(['examplePutReponseKey' => 'examplePutResponseValue'], $response);
        // Check unsupported unicode chrs are sanitized from the payload
        $this->assertEquals($this->getLastRequest()->getBody()->getContents(), '{"putRequestBodyKeyExample":"putRequestBodyValueExample"}');
    }

    public function testResetHeaders(): void
    {
        $this->setUp();
        $clientOptions = $this->sut->getClientOptions();
        $clientOptions->setHeaders(['Authorization' => 'Bearer AKSNKDJNAJNBQJ121321NMM']);

        $this->sut->resetHeaders();

        $this->assertEquals([], $clientOptions->getHeaders());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestLogResponseOnSuccess')]
    public function testLogResponseOnSuccess(mixed $statusCode): void
    {
        $requestBody = ['postRequestBodyKeyExample' => 'postRequestBodyValueExample'];
        $encodedResponseBody = json_encode(['access_token' => 'someAccessToken']);

        $this->appendToHandler($statusCode, [], $encodedResponseBody);

        $this->sut->put('/post-endpoint', $requestBody);

        $expectedDebugLogMessage = "Request URI: api.cpms.domain/post-endpoint\n Response code: $statusCode\n Response body: {\"access_token\":\"****\"}";
        $expectedInfoLogMessage = "Request URI: api.cpms.domain/post-endpoint\n Response code: $statusCode";

        $this->assertTrue($this->logger->hasRecordsAtLevel(LogLevel::DEBUG));
        $this->assertTrue($this->logger->hasRecordsAtLevel(LogLevel::INFO));
        $this->assertFalse($this->logger->hasRecordsAtLevel(LogLevel::ERROR));
        $this->assertSame($expectedInfoLogMessage, $this->logger->records[0]['message']);
        $this->assertSame($expectedDebugLogMessage, $this->logger->records[1]['message']);
    }

    public static function dpTestLogResponseOnSuccess(): array
    {
        return [
            [
                'statusCode' => 200
            ],
            [
                'statusCode' => 300
            ]
        ];
    }

    public function testEmptyIfNoResponseBody(): void
    {
        $this->appendToHandler();
        $actual = $this->sut->get('/endpoint', []);
        $this->assertEmpty($actual);
    }

    public function testBadlyFormedJsonResponseReturnsAsString(): void
    {
        $badJson = "{'test':'";
        //testing bad json error
        json_decode($badJson);
        $this->assertNotNull(json_last_error());

        $this->appendToHandler(200, [], $badJson);
        $actual = $this->sut->get('/endpoint', []);
        $this->assertEquals($badJson, $actual);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestLogResponseOnFailure')]
    public function testLogResponseOnFailure(mixed $dpData): void
    {
        $statusCode = $dpData['statusCode'];
        $encodedResponseBody = $dpData['responseBody'];

        $this->logger = new RecordingLogger();

        $mockClient = m::mock(Client::class);

        $this->sut = new HttpClient(
            $mockClient,
            $this->getClientOptions(),
            $this->logger
        );

        $response = new Response(
            $statusCode,
            [],
            $encodedResponseBody,
            '1.1',
            null
        );

        $mockClient->shouldReceive('post')->andReturn($response);

        $requestBody = ['postRequestBodyKeyExample' => 'postRequestBodyValueExample'];
        $result = $this->sut->post('/post-fail-endpoint', $requestBody);

        $expectedErrorLogMessage = "Request URI: api.cpms.domain/post-fail-endpoint\n Response code: $statusCode";
        $this->assertSame($expectedErrorLogMessage, $this->logger->records[0]['message']);
        $this->assertFalse($this->logger->hasRecordsAtLevel(LogLevel::DEBUG));
        $this->assertFalse($this->logger->hasRecordsAtLevel(LogLevel::INFO));
        $this->assertTrue($this->logger->hasRecordsAtLevel(LogLevel::ERROR));
        $this->assertEquals($dpData['expectedResponse'], $result);
    }

    public static function dpTestLogResponseOnFailure(): array
    {
        return [
            'client_error' => [
                [
                    'statusCode' => 400,
                    'responseBody' => json_encode(['error' => 'client error']),
                    'expectedResponse' => ['error' => 'client error']
                ]
            ],
            'server_error' => [
                [
                    'statusCode' => 500,
                    'responseBody' => json_encode(['error' => 'server error']),
                    'expectedResponse' => ['error' => 'server error']
                ]
            ],
            'server_error_empty_response' => [
                [
                    'statusCode' => 500,
                    'responseBody' => '',
                    'expectedResponse' => ''
                ]
            ]
        ];
    }
}
