<?php

/**
 * Date Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Service;

use Dvsa\Olcs\Api\Domain\Util\DateTime\AddDays;

/**
 * Date Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Date
{
    public function getDate($format = 'Y-m-d')
    {
        return date($format);
    }

    public function getDateObject($time = "now")
    {
        return new \DateTime($time);
    }

    /**
     * Convert DateSelect style array data to a DateTime object
     * @return \DateTime
     */
    public function getDateObjectFromArray(array $date)
    {
        $obj = new \DateTime();
        $obj->setDate($date['year'], $date['month'], $date['day']);
        return $obj;
    }

    public function calculateDate($date, $days)
    {
        $calculator = new AddDays();

        return $calculator->calculateDate($date, $days);
    }
}
