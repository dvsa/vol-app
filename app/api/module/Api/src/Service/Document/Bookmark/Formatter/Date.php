<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * Date formatter
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class Date implements FormatterInterface
{
    public static function format(array $data)
    {
        return date("d/m/Y", strtotime((string) reset($data)));
    }
}
