<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Statement;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/statement")
 * @Transfer\Method("POST")
 */
class CreateStatement extends AbstractCommand
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case = null;

    /**
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"statement_t_ni", "statement_t_43", "statement_t_9"}})
     */
    protected $statementType = null;


    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $assignedCaseworker = null;

    /**
     */
    protected $vrm = null;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\ContactDetails")
     */
    protected $requestorsContactDetails;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":40})
     */
    protected $requestorsBody = null;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $stoppedDate = null;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $requestedDate = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $issuedDate = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"cm_email", "cm_fax", "cm_letter", "cm_tel"}})
     */
    protected $contactType = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":5,"max":4000})
     */
    protected $authorisersDecision = null;

    /**
     * @return mixed
     */
    public function getAuthorisersDecision()
    {
        return $this->authorisersDecision;
    }

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
    public function getContactType()
    {
        return $this->contactType;
    }

    /**
     * Get the value of assignedCaseworker
     *
     * @return int
     */
    public function getAssignedCaseworker()
    {
        return $this->assignedCaseworker;
    }

    /**
     * @return mixed
     */
    public function getRequestedDate()
    {
        return $this->requestedDate;
    }

    /**
     * @return mixed
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
    }

    /**
     * @return mixed
     */
    public function getRequestorsBody()
    {
        return $this->requestorsBody;
    }

    /**
     * @return mixed
     */
    public function getRequestorsContactDetails()
    {
        return $this->requestorsContactDetails;
    }

    /**
     * @return mixed
     */
    public function getStatementType()
    {
        return $this->statementType;
    }

    /**
     * @return mixed
     */
    public function getStoppedDate()
    {
        return $this->stoppedDate;
    }

    /**
     * @return mixed
     */
    public function getVrm()
    {
        return $this->vrm;
    }
}
