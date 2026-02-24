<?php

namespace Dvsa\Olcs\DvsaAddressService\Client\Mapper;

use Dvsa\Olcs\DvsaAddressService\Model\Address;

class AddressMapper
{
    /**
     * Maps an array of address data to an array of Address objects.
     *
     * @param array $data Array of address data.
     * @return Address[] Array of Address objects.
     */
    public static function mapAddressDataArrayToObjects(array $data): array
    {
        return array_map(
            fn($address) => self::mapSingleAddressDataToObject($address),
            $data
        );
    }

    /**
     * Maps a single address data array to an Address object.
     *
     * @param array $data Single address data array.
     * @return Address An Address object.
     */
    public static function mapSingleAddressDataToObject(array $data): Address
    {
        return new Address(
            $data['address_line1'] ?? null,
            $data['address_line2'] ?? null,
            $data['address_line3'] ?? null,
            $data['address_line4'] ?? null,
            $data['post_town'] ?? null,
            $data['postcode'] ?? null,
            $data['postcode_trim'] ?? null,
            $data['organisation_name'] ?? null,
            $data['uprn'] ?? null,
            $data['administrative_area'] ?? null
        );
    }

    /**
     * Converts an array of Address objects into their array representations.
     *
     * @param Address[] $addresses Array of Address objects.
     * @return array Array of address data arrays.
     */
    public static function convertAddressObjectsToArrayRepresentation(array $addresses): array
    {
        return array_map(fn($addressObject) => $addressObject->toArray(), $addresses);
    }
}
