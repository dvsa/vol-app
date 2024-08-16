<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * Name formatter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Name implements FormatterInterface
{
    public static function format(array $data)
    {
        return $data['forename'] . ' ' . $data['familyName'];
    }
}
