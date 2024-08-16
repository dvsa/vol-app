<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

/**
 * ProcessDate Trait
 */
trait ProcessDateTrait
{
    /**
     * Processes date
     *
     * @param string $date     Date
     * @param string $format   Format
     * @param bool   $zeroTime Zero time
     *
     * @return \DateTime|null
     */
    public static function processDate($date, $format = 'Y-m-d', $zeroTime = true)
    {
        if (empty($date)) {
            return null;
        }

        $dateTime = \DateTime::createFromFormat($format, $date);

        if (!$dateTime instanceof \DateTime) {
            return null;
        }

        if ($zeroTime) {
            $dateTime->setTime(0, 0, 0);
        }

        return $dateTime;
    }

    /**
     * Converts (if not already) to a DateTime object
     *
     * @param mixed $datetime Datetime to convert
     *
     * @return \DateTime|null
     */
    public function asDateTime(mixed $datetime)
    {
        if ($datetime instanceof \DateTime) {
            return $datetime;
        }
        if (empty($datetime)) {
            return null;
        }

        return new \DateTime($datetime);
    }
}
