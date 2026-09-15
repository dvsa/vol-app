<?php

/**
 * Create Continuation
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Continuation;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/continuation/create")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 1, "max": 12})
     */
    public $month;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    public $year;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":0,"max":18})
     */
    protected $trafficArea;

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
     * Get a traffic area
     *
     * @return array
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }
}
