<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\DvlaSearch;

use Dvsa\Olcs\Api\Service\DvlaSearch\DvlaSearchService;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\BadRequestException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\BadResponseException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\NotFoundException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\ServiceException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\VehicleUnavailableException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Model\DvlaVehicle;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;

class DvlaSearchServiceTest extends MockeryTestCase
{
    /**
     * @var DvlaSearchService
     */
    protected $sut;

    /**
     * @var MockHandler
     */
    protected $mockHandler;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var GuzzleHttpClient
     */
    protected $httpClient;

    public function setUp(): void
    {
        $this->mockHandler = new MockHandler();

        $handlerStack = HandlerStack::create($this->mockHandler);
        $this->httpClient = new GuzzleHttpClient(['handler' => $handlerStack]);

        $this->logger = m::mock(LoggerInterface::class);
        $this->sut = new DvlaSearchService($this->httpClient, $this->logger);

        parent::setUp();
    }

    public function testSuccessfulDvlaVehicleAcquisition(): void
    {
        $this->mockHandler->append(
            new Response(200, [], $this->generateJsonVehicleResponse())
        );

        $this->logger
            ->shouldReceive('debug')
            ->once();

        $vehicle = $this->sut->getVehicle("ABC123");

        $this->assertEquals(
            'vehicle',
            $this->mockHandler->getLastRequest()->getUri()->getPath()
        );
        $this->assertEquals(
            'identifier=ABC123',
            $this->mockHandler->getLastRequest()->getUri()->getQuery()
        );
        $this->assertInstanceOf(DvlaVehicle::class, $vehicle);
    }

    public function testDvlaApiErrorsCauseVehicleUnavailableException(): void
    {
        $this->expectException(VehicleUnavailableException::class);

        $this->mockHandler->append(
            new Response(204, [], '')
        );

        $this->logger
            ->shouldReceive('debug')
            ->once();

        $this->sut->getVehicle("ABC123");
    }

    public function testDvlaBrokerApiBadRequestIsCaught(): void
    {
        $this->expectException(BadRequestException::class);

        $this->mockHandler->append(
            new Response(400, [], '')
        );

        $this->logger
            ->shouldReceive('error')
            ->once();

        $this->sut->getVehicle("ABC123");
    }

    public function testDvlaBrokerApiForbiddenIsCaught(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->mockHandler->append(
            new Response(403, [], '')
        );

        $this->logger
            ->shouldReceive('error')
            ->once();

        $this->sut->getVehicle("ABC123");
    }

    public function testDvlaBrokerApiNotFoundIsCaught(): void
    {
        $this->expectException(NotFoundException::class);

        $this->mockHandler->append(
            new Response(404, [], '')
        );

        $this->logger
            ->shouldReceive('error')
            ->once();

        $this->sut->getVehicle("ABC123");
    }

    public function testDvlaBrokerApiServerErrorIsCaught(): void
    {
        $this->expectException(ServiceException::class);

        $this->mockHandler->append(
            new Response(500, [], '')
        );

        $this->logger
            ->shouldReceive('error')
            ->once();

        $this->sut->getVehicle("ABC123");
    }

    public function testDvlaBrokerApiReturnsInvalidJsonIsCaught(): void
    {
        $this->expectException(BadResponseException::class);

        $this->mockHandler->append(
            new Response(200, [], '-S0meRandomNonJSONCharacters-')
        );

        $this->logger
            ->shouldReceive('debug')
            ->once();

        $this->sut->getVehicle("ABC123");
    }

    public function testNoLoggerIsDefined(): void
    {
        $this->expectException(BadResponseException::class);

        $this->mockHandler->append(
            new Response(200, [], '-S0meRandomNonJSONCharacters-')
        );

        $this->sut = new DvlaSearchService($this->httpClient);
        $this->sut->getVehicle("ABC123");
    }

    private function generateJsonVehicleResponse(): string
    {
        return "{" .
            "\"timeStamp\":\"19/08/2020 16:37:30\"," .
            "\"vehicle\":{" .
                "\"co2Emissions\":98," .
                "\"colour\":\"BLACK\"," .
                "\"engineCapacity\":1499," .
                "\"fuelType\":\"DIESEL\"," .
                "\"make\":\"FORD\"," .
                "\"markedForExport\":false," .
                "\"monthOfFirstRegistration\":\"2015-07\"," .
                "\"motExpiryDate\":\"2021-01-06\"," .
                "\"motStatus\":\"Valid\"," .
                "\"registrationNumber\":\"ABC123\"," .
                "\"revenueWeight\":1900," .
                "\"taxDueDate\":\"2021-06-01\"," .
                "\"artEndDate\":null," .
                "\"taxStatus\":\"Taxed\"," .
                "\"typeApproval\":\"M1\"," .
                "\"wheelplan\":\"2 AXLE RIGID BODY\"," .
                "\"yearOfManufacture\":2015," .
                "\"euroStatus\":null," .
                "\"dateOfLastV5CIssued\":\"2019-07-02\"," .
                "\"monthOfFirstDvlaRegistration\":null," .
                "\"realDrivingEmissions\":null" .
            "}" .
        "}";
    }
}
