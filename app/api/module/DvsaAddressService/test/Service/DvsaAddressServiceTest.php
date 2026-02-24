<?php

namespace Dvsa\Olcs\DvsaAddressService\Service;

use Dvsa\Olcs\DvsaAddressService\Client\DvsaAddressServiceClient;

class DvsaAddressServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testLookupAddress()
    {
        $logger = $this->createStub(\Laminas\Log\LoggerInterface::class);
        $dvsaAddressServiceClient = $this->createMock(DvsaAddressServiceClient::class);
        $dvsaAddressService = new DvsaAddressService($logger, $dvsaAddressServiceClient);

        $query = 'query';

        $dvsaAddressServiceClient->expects($this->once())
            ->method('lookupAddress')
            ->with($query)
            ->willReturn([]);

        $this->assertEquals([], $dvsaAddressService->lookupAddress($query));
    }

    public function testLookupAddressReturnsEmptyArrayWhenValidationExceptionIsThrown()
    {
        $logger = $this->createMock(\Laminas\Log\LoggerInterface::class);
        $dvsaAddressServiceClient = $this->createMock(DvsaAddressServiceClient::class);
        $dvsaAddressService = new DvsaAddressService($logger, $dvsaAddressServiceClient);

        $query = 'query';

        $dvsaAddressServiceClient->expects($this->once())
            ->method('lookupAddress')
            ->with($query)
            ->willThrowException(new \Dvsa\Olcs\DvsaAddressService\Exception\ValidationException('Validation exception'));

        $logger->expects($this->once())
            ->method('debug')
            ->with('DVSA Address Service: Invalid query string: Validation exception', [
                'query' => $query,
                'exception' => new \Dvsa\Olcs\DvsaAddressService\Exception\ValidationException('Validation exception')
            ]);

        $this->assertEquals([], $dvsaAddressService->lookupAddress($query));
    }

    public function testLookupAddressThrowsServiceExceptionWhenServiceExceptionIsThrown()
    {
        $logger = $this->createMock(\Laminas\Log\LoggerInterface::class);
        $dvsaAddressServiceClient = $this->createMock(DvsaAddressServiceClient::class);
        $dvsaAddressService = new DvsaAddressService($logger, $dvsaAddressServiceClient);

        $query = 'query';

        $dvsaAddressServiceClient->expects($this->once())
            ->method('lookupAddress')
            ->with($query)
            ->willThrowException(new \Dvsa\Olcs\DvsaAddressService\Exception\ServiceException('Service exception'));

        $logger->expects($this->once())
            ->method('err')
            ->with('DVSA Address Service: Error looking up address by query: Service exception', [
                'query' => $query,
                'exception' => new \Dvsa\Olcs\DvsaAddressService\Exception\ServiceException('Service exception'),
                'trace' => (new \Dvsa\Olcs\DvsaAddressService\Exception\ServiceException('Service exception'))->getTraceAsString()
            ]);

        $this->expectException(\Dvsa\Olcs\DvsaAddressService\Exception\ServiceException::class);
        $this->expectExceptionMessage('Service exception');

        $dvsaAddressService->lookupAddress($query);
    }
}
