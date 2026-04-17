<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Service\Ebsr\TransExchangeClient;
use Laminas\Http\Headers;
use Laminas\Http\Client as RestClient;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Logging\Log\Logger;
use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Filter\ParseXmlString;
use Olcs\XmlTools\Validator\Xsd;

/**
 * Class TransExchangeClientTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr
 */
class TransExchangeClientTest extends TestCase
{
    public function setUp(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);
    }

    /**
     * Tests making a request
     */
    public function testMakeRequest(): void
    {
        $requestBody = 'body';
        $responseContent = '<success></success>';
        $responseStringContent = 'string<success></success>string';

        $result = [
            'success' => true
        ];

        $domDocument = new \DOMDocument();

        $mockRequest = m::mock(Request::class);
        $mockRequest->shouldReceive('setContent')->with($requestBody);
        $mockRequest->shouldReceive('getHeaders')->andReturn(
            m::mock(Headers::class)->shouldReceive('addHeaders')->with(
                [
                    'X-Correlation-Id' => 'TEST_CORRELATION_ID'
                ]
            )->getMock()
        );
        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('getContent')->andReturn($responseContent);
        $mockResponse->shouldReceive('toString')->andReturn($responseStringContent);
        $mockClient = m::mock(RestClient::class);
        $mockClient->shouldReceive('getRequest')->andReturn($mockRequest);
        $mockClient->shouldReceive('send')->andReturn($mockResponse);
        $mockParser = m::mock(ParseXmlString::class);
        $mockParser->shouldReceive('filter')->once()->with($responseContent)->andReturn($domDocument);
        $mockXsd = m::mock(Xsd::class);
        $mockXsd->shouldReceive('isValid')->once()->with($domDocument)->andReturn(true);
        $mockFilter = m::mock(MapXmlFile::class);
        $mockFilter->shouldReceive('filter')->once()->with($domDocument)->andReturn($result);
        $sut = new TransExchangeClient($mockClient, $mockFilter, $mockParser, $mockXsd, 'TEST_CORRELATION_ID');
        $this->assertEquals($result, $sut->makeRequest($requestBody));
    }

    /**
     * Tests exception thrown when request not valid
     */
    public function testMakeRequestThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\TransxchangeException::class);
        $this->expectExceptionMessage('TransXchange response did not validate against the schema: message 1, message 2');

        $requestBody = 'body';
        $responseContent = '<success></success>';
        $responseStringContent = 'string<success></success>string';

        $result = [
            'success' => true
        ];

        $errorMessages = [
            'message 1',
            'message 2'
        ];

        $domDocument = new \DOMDocument();

        $mockRequest = m::mock(Request::class);
        $mockRequest->shouldReceive('setContent')->with($requestBody);
        $mockRequest->shouldReceive('getHeaders')->andReturn(
            m::mock(Headers::class)->shouldReceive('addHeaders')->with(
                [
                    'X-Correlation-Id' => 'TEST_CORRELATION_ID'
                ]
            )->getMock()
        );
        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('getContent')->andReturn($responseContent);
        $mockResponse->shouldReceive('toString')->andReturn($responseStringContent);
        $mockClient = m::mock(RestClient::class);
        $mockClient->shouldReceive('getRequest')->andReturn($mockRequest);
        $mockClient->shouldReceive('send')->andReturn($mockResponse);
        $mockParser = m::mock(ParseXmlString::class);
        $mockParser->shouldReceive('filter')->once()->with($responseContent)->andReturn($domDocument);
        $mockXsd = m::mock(Xsd::class);
        $mockXsd->shouldReceive('isValid')->once()->with($domDocument)->andReturn(false);
        $mockXsd->shouldReceive('getMessages')->once()->andReturn($errorMessages);
        $mockFilter = m::mock(MapXmlFile::class);
        $sut = new TransExchangeClient($mockClient, $mockFilter, $mockParser, $mockXsd, "TEST_CORRELATION_ID");
        $this->assertEquals($result, $sut->makeRequest($requestBody));
    }
}
