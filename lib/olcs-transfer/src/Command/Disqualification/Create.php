<?php

/**
 * Create
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Disqualification;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/disqualification")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $organisation;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $person;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     */
    protected $isDisqualified;

    /**
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $startDate;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Optional
     */
    protected $period;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":4000})
     * @Transfer\Optional
     */
    public $notes;

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getIsDisqualified()
    {
        return $this->isDisqualified;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function getNotes()
    {
        return $this->notes;
    }
}
