<?php

namespace Common\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\MapperInterface;

class AddressDetails implements MapperInterface
{
    private static $typeMap = [
        'phone_t_primary' => 'phone_primary',
        'phone_t_secondary' => 'phone_secondary',
    ];

    /**
     * Prepare api data for form
     *
     * @param array $data Api data
     */
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        $mappedData = [];

        if (!empty($data['correspondenceCd'])) {
            $mappedData = [
                'correspondence_address' => $data["correspondenceCd"]["address"],
                'correspondence' => [
                    'id' => $data["correspondenceCd"]["id"],
                    'version' => $data["correspondenceCd"]["version"],
                    'fao' => $data["correspondenceCd"]["fao"],
                ],
            ];
            $mappedData['contact'] = static::mapContactsFromResult($data);
        }

        return $mappedData;
    }

    /**
     * Prepare contacts data from API data to Form data
     *
     * @param array $data Api data
     */
    private static function mapContactsFromResult(array $data): array
    {
        $contacts = [];

        foreach ($data["correspondenceCd"]["phoneContacts"] as $phoneContact) {
            $phoneType = self::$typeMap[$phoneContact['phoneContactType']['id']] ?? '';

            $contacts += [
                $phoneType => $phoneContact['phoneNumber'],
                $phoneType . '_id' => $phoneContact['id'],
                $phoneType . '_version' => $phoneContact['version'],
            ];
        }

        $contacts['email'] = $data["correspondenceCd"]["emailAddress"];

        return $contacts;
    }
}
