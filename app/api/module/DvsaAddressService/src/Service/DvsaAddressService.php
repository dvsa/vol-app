<?php

namespace Dvsa\Olcs\DvsaAddressService\Service;

use Dvsa\Olcs\DvsaAddressService\Client\DvsaAddressServiceClient;
use Dvsa\Olcs\DvsaAddressService\Exception\ServiceException;
use Dvsa\Olcs\DvsaAddressService\Exception\ValidationException;
use Dvsa\Olcs\DvsaAddressService\Model\Address;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Log\LoggerInterface;

class DvsaAddressService implements AddressInterface
{
    public function __construct(protected LoggerInterface $logger, protected DvsaAddressServiceClient $dvsaAddressServiceClient)
    {
    }

    /**
     * @param string $query
     * @param bool $skipQueryValidation
     * @return Address[]
     * @throws GuzzleException
     * @throws ServiceException
     */
    public function lookupAddress(string $query): array
    {
        try {
            return $this->dvsaAddressServiceClient->lookupAddress($query);
        } catch (ValidationException $e) {
            $this->logger->debug('DVSA Address Service: Invalid query string: ' . $e->getMessage(), [
                'query' => $query,
                'exception' => $e
            ]);
            return [];
        } catch (ServiceException | GuzzleException $e) {
            $this->logger->err('DVSA Address Service: Error looking up address by query: ' . $e->getMessage(), [
                'query' => $query,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
