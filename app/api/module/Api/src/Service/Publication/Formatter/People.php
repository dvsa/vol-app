<?php

namespace Dvsa\Olcs\Api\Service\Publication\Formatter;

use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Person\Person;

/**
 * Class FormatPeople
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class People
{
    public static function format(Organisation $organisation, $persons)
    {
        // if sole trader then no text
        if ($organisation->isSoleTrader()) {
            return null;
        }

        // if no persons no text
        if (empty($persons)) {
            return null;
        }

        $personPrefixes = [
            Organisation::ORG_TYPE_REGISTERED_COMPANY => 'Director(s): ',
            Organisation::ORG_TYPE_PARTNERSHIP => 'Partner(s): ',
            Organisation::ORG_TYPE_LLP => 'Partner(s): ',
            Organisation::ORG_TYPE_OTHER => '',
        ];
        $text = $personPrefixes[$organisation->getType()->getId()] ?? '';

        $people = [];
        foreach ($persons as $person) {
            /* @var $person Person */
            $people[] = $person->getFullName();
        }

        $text .= implode(', ', $people);

        return $text;
    }
}
