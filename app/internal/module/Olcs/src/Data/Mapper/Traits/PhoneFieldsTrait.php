<?php

namespace Olcs\Data\Mapper\Traits;

use Common\RefData;

/**
 * Class PhoneFieldsTrait
 * @package Olcs\Data\Mapper
 */
trait PhoneFieldsTrait
{
    /**
     * Phone types
     *
     * @var array
     */
    protected static $phoneTypes = [
        'primary' => RefData::PHONE_TYPE_PRIMARY,
        'secondary' => RefData::PHONE_TYPE_SECONDARY,
    ];

    /**
     * Get fields from result
     *
     * @param array $phoneContacts
     * @return array
     */
    protected static function mapPhoneFieldsFromResult($phoneContacts)
    {
        $fields = [];

        $typeMap = array_flip(self::$phoneTypes);

        foreach ($phoneContacts as $phoneContact) {
            // map form type
            $type = $phoneContact['phoneContactType']['id'];
            $phoneType = $typeMap[$type] ?? '';

            if (!empty($phoneType)) {
                $fields['phone_' . $phoneType] = $phoneContact['phoneNumber'];
                $fields['phone_' . $phoneType . '_id'] = $phoneContact['id'];
                $fields['phone_' . $phoneType . '_version'] = $phoneContact['version'];
            }
        }

        return $fields;
    }

    /**
     * Get phone contacts from form
     *
     * @param array $fields
     * @return array
     */
    protected static function mapPhoneContactsFromForm($fields)
    {
        $phoneContacts = [];

        foreach (self::$phoneTypes as $phoneType => $phoneRefName) {
            if (!empty($fields['phone_' . $phoneType])) {
                $phoneContacts[] = [
                    'id' => $fields['phone_' . $phoneType . '_id'],
                    'version' => $fields['phone_' . $phoneType . '_version'],
                    'phoneNumber' => $fields['phone_' . $phoneType],
                    'phoneContactType' => $phoneRefName,
                ];
            }
        }

        return $phoneContacts;
    }
}
