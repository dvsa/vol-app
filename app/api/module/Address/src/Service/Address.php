<?php

namespace Dvsa\Olcs\Address\Service;

use Dvsa\Olcs\Api\Service\Exception;
use Dvsa\Olcs\DvsaAddressService\Client\Mapper\AddressMapper;
use Dvsa\Olcs\DvsaAddressService\Service\AddressInterface;

class Address implements AddressInterface
{
    public const ERR_INVALID_RESP_BY_QUERY = 'Legacy Address API returned a non-successful response OR address was not found by query';

    /**
     * Constructor
     *
     * @param Client $client Postcode Api Http Client
     */
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @return \Dvsa\Olcs\DvsaAddressService\Model\Address[]
     *
     * @deprecated Use \Dvsa\Olcs\DvsaAddressService\Service\DvsaAddressService::lookupAddress instead
     *
     */
    public function lookupAddress(string $query): array
    {
        $this->client->setUri('address/' . urlencode($query));
        $response = $this->client->send();

        if (!$response->isSuccess()) {
            throw new Exception(self::ERR_INVALID_RESP_BY_QUERY);
        }

        $content = $response->getBody();

        $json = json_decode($content, true);

        // If the response is not an array, convert it to an array of objects
        if (!empty($json) && !isset($json[0])) {
            $json = [$json];
        }

        // Convert 'administritive_area' to 'administrative_area'
        foreach ($json as $key => $address) {
            $json[$key]['administrative_area'] = $address['administritive_area'] ?? null;
        }

        return AddressMapper::mapAddressDataArrayToObjects($json);
    }
}
