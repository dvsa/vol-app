<?php

/**
 * Create ProposeToRevoke
 */

namespace Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/propose-to-revoke")
 * @Transfer\Method("POST")
 */
final class CreateProposeToRevoke extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $reasons = [];

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $assignedCaseworker = null;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $presidingTc;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $ptrAgreedDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     * @Transfer\Optional
     */
    protected $closedDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":5,"max":4000})
     * @Transfer\Optional
     */
    protected $comment;

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
    public function getReasons()
    {
        return $this->reasons;
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
    public function getPresidingTc()
    {
        return $this->presidingTc;
    }

    /**
     * @return mixed
     */
    public function getPtrAgreedDate()
    {
        return $this->ptrAgreedDate;
    }

    /**
     * @return mixed
     */
    public function getClosedDate()
    {
        return $this->closedDate;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }
}
