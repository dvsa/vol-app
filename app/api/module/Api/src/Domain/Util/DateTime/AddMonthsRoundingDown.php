<?php

namespace Dvsa\Olcs\Api\Domain\Util\DateTime;

/**
 * Add Months Rounding Down
 */
class AddMonthsRoundingDown
{
    /**
     * Calculates a date that is $months before/after $date with rounding down.
     *  2015-12-31 + 2 month => 2016-02-29
     *  2010-01-12 + 2 month => 2010-03-12
     *
     * @param \DateTime $date
     * @param integer $months The number of months to offset (can be a negative number)
     * @return \DateTime
     */
    public function calculateDate(\DateTime $date, int $months): \DateTime
    {
        $result = clone $date;

        $init = clone $date;
        $modifier = $months . ' months';
        $backModifier = -$months . ' months';

        $result->modify($modifier);
        $backToInit = clone $result;
        $backToInit->modify($backModifier);

        while ($init->format('m') != $backToInit->format('m')) {
            $result->modify('-1 day');
            $backToInit = clone $result;
            $backToInit->modify($backModifier);
        }
        return $result;
    }
}
