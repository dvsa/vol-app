<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Single place that decides where a licence's SMS number comes from.
 */
class SmsNumber
{
    public static function forLicence(Licence $licence): ?string
    {
        $correspondenceCd = $licence->getCorrespondenceCd();
        if ($correspondenceCd === null) {
            return null;
        }

        $number = self::latestContact($correspondenceCd)?->getPhoneNumber();
        if ($number === null || trim((string) $number) === '') {
            return null;
        }

        return (string) $number;
    }

    /**
     * There should be one SMS row per contact, but older data can hold more. The most recently
     * modified one wins everywhere.
     */
    public static function latestContact(?ContactDetails $contactDetails): ?PhoneContact
    {
        return self::smsContacts($contactDetails)[0] ?? null;
    }

    /**
     * @return PhoneContact[] most recently modified first
     */
    public static function smsContacts(?ContactDetails $contactDetails): array
    {
        if ($contactDetails === null) {
            return [];
        }

        $rows = [];
        foreach ($contactDetails->getPhoneContacts() ?? [] as $phoneContact) {
            if ($phoneContact->getPhoneContactType()?->getId() === PhoneContact::TYPE_SMS) {
                $rows[] = $phoneContact;
            }
        }

        usort(
            $rows,
            static fn (PhoneContact $a, PhoneContact $b): int
                => [self::changedAt($b), (int) $b->getId()] <=> [self::changedAt($a), (int) $a->getId()]
        );

        return $rows;
    }

    private static function changedAt(PhoneContact $phoneContact): int
    {
        $at = $phoneContact->getLastModifiedOn() ?? $phoneContact->getCreatedOn();

        return $at instanceof \DateTimeInterface ? $at->getTimestamp() : 0;
    }
}
