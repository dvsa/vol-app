<?php

namespace Dvsa\OlcsTest\Api\Service\Nysiis;

use Dvsa\Olcs\Api\Domain\Exception\NysiisException;
use Dvsa\Olcs\Api\Service\Nysiis\NysiisRestClient;
use Laminas\Http\Client as RestClient;
use Laminas\Http\Request as HttpRequest;
use Laminas\Http\Response as HttpResponse;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;

/**
 * Class NysiisRestClientTest
 * @package Dvsa\OlcsTest\Api\Service\Nysiis
 */
class NysiisRestClientTest extends MockeryTestCase
{
    public function setUp(): void
    {
        $logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($logWriter);
        Logger::setLogger($logger);

        parent::setUp();
    }

    /**
     * tests makeRequest
     *
     * @param $outputJson
     *
     * @dataProvider makeRequestProvider
     */
    public function testMakeRequest($outputJson)
    {
        $volFirstName = 'vol first name';
        $volFamilyName = 'vol family name';
        $nysiisFirstName = 'nysiis first name';
        $nysiisFamilyName = 'nysiis family name';

        $inputJson = '{"volFirstName":"' . $volFirstName . '","volFamilyName":"' . $volFamilyName . '"}';

        $returnedArray = [
            'nysiisFirstName' => $nysiisFirstName,
            'nysiisFamilyName' => $nysiisFamilyName
        ];

        $restResponse = new HttpResponse();
        $restResponse->setStatusCode(200);
        $restResponse->setContent($outputJson);

        $restClient = $this->basicRestClient($inputJson);
        $restClient->shouldReceive('send')->once()->andReturn($restResponse);

        $sut = new NysiisRestClient($restClient);
        $this->assertEquals($returnedArray, $sut->makeRequest($volFirstName, $volFamilyName));
    }

    /**
     * data provider for makeRequest
     */
    public function makeRequestProvider()
    {
        return [
            ['38{"nysiisFirstName":"nysiis first name","nysiisFamilyName":"nysiis family name"}0'],
            ['3a{"nysiisFirstName":"nysiis first name","nysiisFamilyName":"nysiis family name"}0'],
            ['{"nysiisFirstName":"nysiis first name","nysiisFamilyName":"nysiis family name"}']
        ];
    }

    /**
     * @param $response
     * @param $errorMessage
     *
     * @dataProvider invalidResponseProvider
     */
    public function testMakeRequestInvalidResponse($response, $errorMessage)
    {
        $volFirstName = 'vol first name';
        $volFamilyName = 'vol family name';

        $inputJson = '{"volFirstName":"' . $volFirstName . '","volFamilyName":"' . $volFamilyName . '"}';

        $restClient = $this->basicRestClient($inputJson);
        $restClient->shouldReceive('send')->once()->andReturn($response);

        $this->expectException(NysiisException::class);

        $sut = new NysiisRestClient($restClient);
        $sut->makeRequest($volFirstName, $volFamilyName);
    }

    /**
     * @return array
     */
    public function invalidResponseProvider()
    {
        $invalidResponse1 = new HttpResponse();
        $invalidResponse1->setStatusCode(400);

        $invalidResponse2 = new HttpResponse();
        $invalidResponse2->setStatusCode(200);
        $invalidResponse2->setContent('{bad json{');

        return [
            [$invalidResponse1, NysiisRestClient::NYSIIS_RESPONSE_INCORRECT],
            [$invalidResponse2, 'Nysiis REST service failure: Decoding failed: Syntax error'],
            [null, NysiisRestClient::NYSIIS_RESPONSE_INCORRECT],
            ['string response', NysiisRestClient::NYSIIS_RESPONSE_INCORRECT]
        ];
    }

    /**
     * @param $inputJson
     * @return m\MockInterface
     */
    public function basicRestClient($inputJson)
    {
        $restClient = m::mock(RestClient::class);
        $restClient->shouldReceive('setEncType')->with('application/json; charset=UTF-8')->once();
        $restClient->shouldReceive('getRequest->setMethod')->with(HttpRequest::METHOD_POST)->once();
        $restClient->shouldReceive('getRequest->setContent')->with($inputJson)->once();

        return $restClient;
    }
}
