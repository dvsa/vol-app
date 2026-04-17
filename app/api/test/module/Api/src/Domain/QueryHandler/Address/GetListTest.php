<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Address;

use Dvsa\Olcs\Api\Domain\QueryHandler\Address\GetList;
use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Dvsa\Olcs\DvsaAddressService\Client\Mapper\AddressMapper;
use Dvsa\Olcs\DvsaAddressService\Model\Address;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\Address\GetList as Qry;
use Mockery as m;

class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockedSmServices[AddressHelperService::class] = m::mock(AddressHelperService::class);

        $this->sut = new GetList($this->mockedSmServices[AddressHelperService::class]);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['postcode' => 'ABC']);

        $lookupAddressResponse = [
            new Address(
                'address 1',
                'address line 2',
                'address line 3',
                'address line 4',
                'post town',
                'postcode',
                'postcode trim',
                'organisation',
                'uprn',
                'administrative area',
            ),
            new Address(
                'address 2',
                'address line 2',
                'address line 3',
                'address line 4',
                'post town',
                'postcode',
                'postcode trim',
                'organisation',
                'uprn',
                'administrative area',
            ),
        ];

        $this->mockedSmServices[AddressHelperService::class]
            ->shouldReceive('lookupAddress')
            ->with('ABC')
            ->once()
            ->andReturn($lookupAddressResponse)
            ->getMock();

        $expected = [
            'result' => AddressMapper::convertAddressObjectsToArrayRepresentation($lookupAddressResponse),
            'count' => 2
        ];

        $this->assertCount(2, $expected['result']);
        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }
}
