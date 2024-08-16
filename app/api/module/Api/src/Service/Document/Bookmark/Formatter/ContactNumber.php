<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;

/**
 * ContactNumber
 */
class ContactNumber implements FormatterInterface
{
    /**
     * Get a single contact number
     *
     * @param array $phoneContacts Array of phone contacts
     *
     * @return false|string
     */
    public static function format(array $phoneContacts)
    {
        $phoneNumber = '';

        foreach ($phoneContacts as $phone) {
            if ($phone['phoneContactType']['id'] === PhoneContact::TYPE_PRIMARY) {
                $phoneNumber = $phone['phoneNumber'];
            }
            // if secondary number exists and no primary
            if ($phone['phoneContactType']['id'] === PhoneContact::TYPE_SECONDARY && $phoneNumber === '') {
                $phoneNumber = $phone['phoneNumber'];
            }
        }
        return $phoneNumber;
    }
}
