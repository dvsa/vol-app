<?php

namespace Dvsa\Olcs\Utils\Helper;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class DateTimeHelper
{
    /**
     * Format date time by format with corrent timezone
     *
     * @param string $dbDateTime Date (from db usually)
     * @param string $outFormat  Format
     *
     * @return null|string
     */
    public static function format($dbDateTime, $outFormat = 'd/m/Y H:i:s')
    {
        try {
            $date = new \DateTime($dbDateTime, new \DateTimeZone('UTC'));
        } catch (\Exception $e) {
            return null;
        }

        return $date->setTimeZone(new \DateTimeZone(date_default_timezone_get()))
            ->format($outFormat);
    }
}
