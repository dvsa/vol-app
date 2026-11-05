<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

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

        $number = $correspondenceCd->getPhoneContactNumber(PhoneContact::TYPE_SMS);
        if ($number === null || trim((string) $number) === '') {
            return null;
        }

        return (string) $number;
    }
}
