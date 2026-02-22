<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Address\Service;

use Dvsa\Olcs\Address\Service\Address;
use Dvsa\Olcs\Address\Service\Client;
use Dvsa\Olcs\Api\Domain\Repository\AdminAreaTrafficArea;
use Dvsa\Olcs\Api\Domain\Repository\PostcodeEnforcementArea;
use Dvsa\Olcs\Api\Service\Exception;
use Dvsa\Olcs\DvsaAddressService\Client\Mapper\AddressMapper;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Response;

/**
 * @covers Dvsa\Olcs\Address\Service\Address
 */
class AddressTest extends MockeryTestCase
{
    /**  @var Client */
    protected $client;

    /**  @var Address */
    protected $sut;

    public function setUp(): void
    {
        $this->client = m::mock(Client::class);

        $this->sut = new Address($this->client);
    }

    public function testFetchByPostcodeWithResults(): void
    {
        $postcode = 'AB1 1AB';

        $this->mockClientLookup($postcode, '{"address_line1": "bar"}');

        $result = $this->sut->lookupAddress($postcode);
        $objAsArray = AddressMapper::convertAddressObjectsToArrayRepresentation($result);

        $this->assertCount(1, $objAsArray);
        $this->assertArrayHasKey('address_line1', $objAsArray[0]);
        $this->assertEquals('bar', $objAsArray[0]['address_line1']);
    }

    public function testFetchByPostcodeWithoutResults(): void
    {
        //  expect
        $this->expectException(Exception::class);

        //  call
        $postcode = 'AB1 1AB';

        $this->mockClientLookup($postcode, '', Response::STATUS_CODE_404);

        $this->sut->lookupAddress($postcode);
    }

    protected function mockClientLookup(mixed $postcode, mixed $content = null, int $statusCode = 200): void
    {
        /** @var Response $response */
        $response = m::mock(Response::class)->makePartial();
        $response->setStatusCode($statusCode);
        $response->setContent($content);

        $this->client->shouldReceive('setUri')
            ->once()
            ->with('address/' . urlencode((string) $postcode))
            ->shouldReceive('send')
            ->once()
            ->andReturn($response);
    }
}
