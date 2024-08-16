<?php

namespace Dvsa\Olcs\Api\Service\Nr\Filter\Format;

use Laminas\Filter\AbstractFilter as AbstractFilter;

/**
 * Class SiDates
 * @package Dvsa\Olcs\Api\Service\Nr\Filter\Format
 * @template-extends AbstractFilter<array>
 */
class SiDates extends AbstractFilter
{
    /**
     * Returns the result of filtering $value
     *
     * @param  array $value
     * @return array
     */
    public function filter($value)
    {
        foreach ($value['imposedErrus'] as $key => $dates) {
            $value['imposedErrus'][$key]['startDate'] =
                isset($dates['startDate']) ? $this->createDate($dates['startDate']) : null;

            $value['imposedErrus'][$key]['endDate'] =
                isset($dates['endDate']) ? $this->createDate($dates['endDate']) : null;

            $value['imposedErrus'][$key]['finalDecisionDate'] = $this->createDate($dates['finalDecisionDate']);
        }

        $value['checkDate'] = $this->createDate($value['checkDate']);
        $value['infringementDate'] = $this->createDate($value['infringementDate']);

        return $value;
    }

    /**
     * creates a date and resets the time fields to zero
     *
     * @param string $date
     * @return \DateTime|null
     */
    private function createDate($date)
    {
        $dateTime = new \DateTime($date);
        $dateTime->setTime(0, 0, 0);

        return $dateTime;
    }
}
