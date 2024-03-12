<?php

namespace Olcs\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Class SlaIndicator
 *
 * @package Olcs\View\Helper
 *
 * @author  Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class SlaIndicator extends AbstractHelper
{
    /**
     * invoke function
     *
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Generate Date item for read only section
     *
     * @param string $label         label
     * @param array  $queryResult   query result
     * @param string $dateFieldName date field name
     *
     * @return array
     */
    public function generateDateItem($label, $queryResult, $dateFieldName)
    {
        return [
            'label' => $label,
            'date' => $queryResult[$dateFieldName],
            'suffix' => $this->hasTargetBeenMet(
                $queryResult[$dateFieldName],
                $queryResult[$dateFieldName . 'Target'] ?? null
            ),
        ];
    }

    /**
     * Generate SLA HTML
     *
     * @param string|null $date       date
     * @param string|null $targetDate target date
     *
     * @return string
     */
    public function hasTargetBeenMet($date = null, $targetDate = null)
    {
        if (is_null($date) || is_null($targetDate)) {
            return '<span class="status grey">Inactive</span>';
        }

        $retVal = $this->doHasTargetBeenMet($date, $targetDate);

        if (!$retVal) {
            return '<span class="status red">Fail</span>';
        }

        return '<span class="status green">Pass</span>';
    }

    /**
     * check if target date has been met
     *
     * @param string|null $date       date
     * @param string|null $targetDate targetDate
     *
     * @return bool
     */
    private function doHasTargetBeenMet($date = null, $targetDate = null)
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime($date)));
        $targetDateTime = \DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime($targetDate)));

        if ($dateTime <= $targetDateTime) {
            return true;
        }

        return false;
    }
}
