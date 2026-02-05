<?php

namespace Dvsa\Olcs\DvsaAddressService\Client;

use Dvsa\Olcs\DvsaAddressService\Exception\ServiceException;
use Dvsa\Olcs\DvsaAddressService\Exception\ValidationException;
use Dvsa\Olcs\DvsaAddressService\Model\Address;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class DvsaAddressServiceClientTest extends MockeryTestCase
{
    protected DvsaAddressServiceClient $sut;
    protected Client | (m\MockInterface & m\LegacyMockInterface ) $httpClient;

    public function setUp(): void
    {
        $this->httpClient = m::mock(Client::class);
        $this->sut = new DvsaAddressServiceClient($this->httpClient);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function fetchByQueryReturnsArrayOfAddressModel(): void
    {
        $this->httpClient->shouldReceive('get')->andReturn($this->generateResponse(200, [
            [
                "address_line1" => "H M R C",
                "address_line2" => "1 UNITY SQUARE",
                "address_line3" => null,
                "address_line4" => null,
                "post_town" => "NOTTINGHAM",
                "postcode" => "NG2 1AW",
                "postcode_trim" => "NG21AW",
                "organisation" => "H M R C",
                "uprn" => "12345678900",
            ]
        ]));

        $result = $this->sut->lookupAddress('NG2 1AW');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Address::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function fetchByQueryReturnsArrayOfMultipleAddressModel(): void
    {
        $this->httpClient->shouldReceive('get')->andReturn($this->generateResponse(200, [
            [
                "address_line1" => "HMRC",
                "address_line2" => "1 UNITY SQUARE",
                "address_line3" => null,
                "address_line4" => null,
                "post_town" => "NOTTINGHAM",
                "postcode" => "NG2 1AW",
                "postcode_trim" => "NG21AW",
                "organisation" => "HMRC",
                "uprn" => "12345678900",
            ],
            [
                "address_line1" => "HM Land Registry",
                "address_line2" => "1 UNITY SQUARE",
                "address_line3" => null,
                "address_line4" => null,
                "post_town" => "NOTTINGHAM",
                "postcode" => "NG2 1AW",
                "postcode_trim" => "NG21AW",
                "organisation" => "HM Land Registry",
                "uprn" => "12345678901",
            ],
        ]));

        $result = $this->sut->lookupAddress('NG2 1AW');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(Address::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fetchByInvalidQueryThrowsValidationExceptionDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function fetchByInvalidQueryThrowsValidationException(int $httpStatusCode): void
    {
        $this->expectException(ValidationException::class);

        $exception = ClientException::create(
            new Request('get', ''),
            new Response($httpStatusCode)
        );

        $this->httpClient->shouldReceive('get')->andThrow($exception);

        $this->sut->lookupAddress('ABC123');
    }

    public static function fetchByInvalidQueryThrowsValidationExceptionDataProvider(): array
    {
        return [
            'API returns bad request' => [400],
            'API returns unprocessable entity' => [422],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function fetchByQueryConnectExceptionReturnsServiceException()
    {
        $this->expectException(ServiceException::class);

        $exception = new ConnectException(
            "Oops",
            new Request('get', '')
        );

        $this->httpClient->shouldReceive('get')->andThrow($exception);

        $this->sut->lookupAddress('ABC1234');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function fetchByQueryRequestExceptionReturnsServiceException()
    {
        $this->expectException(ServiceException::class);

        $exception = RequestException::create(
            new Request('get', '')
        );

        $this->httpClient->shouldReceive('get')->andThrow($exception);

        $this->sut->lookupAddress('ABC1234');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function fetchByQueryInvalidJsonReturnsServiceException()
    {
        $this->expectException(ServiceException::class);

        $exception = new \InvalidArgumentException();

        $this->httpClient->shouldReceive('get')->andThrow($exception);

        $this->sut->lookupAddress('ABC1234');
    }

    protected function generateResponse(int $status, array $body): Response
    {
        return new Response($status, [], json_encode($body));
    }
}
