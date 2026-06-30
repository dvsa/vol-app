<?php

/**
 * Get checklist reminders
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\ContinuationDetail;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/continuation-detail/checklist-reminders")
 */
final class ChecklistReminders extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 1, "max": 12})
     * @Transfer\Optional
     */
    public $month;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Optional
     */
    public $year;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Optional
     */
    protected $ids = [];

    /**
     * Get a month
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Get a year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Get an ids
     *
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
