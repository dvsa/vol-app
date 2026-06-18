<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Hearing;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/stay")
 * @Transfer\Method("POST")
 */
class CreateStay extends AbstractCommand
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case = null;

    /**
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"stay_t_tc","stay_t_ut"}})
     */
    protected $stayType = null;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $requestDate = null;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $decisionDate = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"stay_s_granted","stay_s_refused"}})
     */
    protected $outcome = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":4000})
     */
    protected $notes = null;

    /**
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isWithdrawn = null;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $withdrawnDate = null;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $dvsaNotified = null;

    /**
     * @return mixed
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @return mixed
     */
    public function getDecisionDate()
    {
        return $this->decisionDate;
    }

    /**
     * @return mixed
     */
    public function getIsWithdrawn()
    {
        return $this->isWithdrawn;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return mixed
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * @return mixed
     */
    public function getRequestDate()
    {
        return $this->requestDate;
    }

    /**
     * @return mixed
     */
    public function getStayType()
    {
        return $this->stayType;
    }

    /**
     * @return mixed
     */
    public function getWithdrawnDate()
    {
        return $this->withdrawnDate;
    }

    /**
     * @return mixed
     */
    public function getDvsaNotified()
    {
        return $this->dvsaNotified;
    }
}
