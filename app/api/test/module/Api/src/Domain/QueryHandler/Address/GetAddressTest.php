<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Address;

use Dvsa\Olcs\Api\Domain\QueryHandler\Address\GetAddress;
use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Dvsa\Olcs\DvsaAddressService\Client\Mapper\AddressMapper;
use Dvsa\Olcs\DvsaAddressService\Model\Address;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\Address\GetAddress as Qry;
use Mockery as m;

class GetAddressTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockedSmServices[AddressHelperService::class] = m::mock(AddressHelperService::class);

        $this->sut = new GetAddress($this->mockedSmServices[AddressHelperService::class]);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['uprn' => 123]);

        $addressModel = new Address(
            'address line 1',
            'address line 2',
            'address line 3',
            'address line 4',
            'post town',
            'postcode',
            'postcode trim',
            'organisation',
            'uprn',
            'administrative area',
        );

        $this->mockedSmServices[AddressHelperService::class]
            ->shouldReceive('lookupAddress')
            ->with('123')
            ->once()
            ->andReturn([$addressModel])
            ->getMock();

        $expected = [
            'result' => AddressMapper::convertAddressObjectsToArrayRepresentation([$addressModel]),
            'count' => 1
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }
}
