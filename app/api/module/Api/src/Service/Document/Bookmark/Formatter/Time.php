<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Time formatter
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class Time implements FormatterInterface
{
    /**
     * Format a time
     *
     * @param array $data Should contain one index which is a string datetime
     *
     * @return null|string
     */
    public static function format(array $data)
    {
        try {
            $dateTime = new DateTime(reset($data));
        } catch (\Exception) {
            // If cannot parse the datatime then return null
            return null;
        }

        // Change timezone to London
        $dateTime->setTimezone(new \DateTimeZone('Europe/London'));

        return $dateTime->format('H:i');
    }
}
