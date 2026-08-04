<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\ConditionUndertaking;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/condition-undertaking")
 * @Transfer\Method("POST")
 */
class CreateConditionUndertaking extends AbstractCommand
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $licence = null;

    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $application = null;

    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case = null;

    /**
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"cdt_con","cdt_und"}})
     */
    protected $conditionType = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":5,"max":8000})
     */
    protected $notes = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y","N"}})
     */
    protected $isFulfilled = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",options={"haystack": {"cat_lic","cat_oc"}})
     */
    protected $attachedTo = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $operatingCentre = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"cav_case","cav_lic","cav_app"}})
     */
    protected $addedVia = null;

    /**
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return mixed
     */
    public function getAttachedTo()
    {
        return $this->attachedTo;
    }

    /**
     * @return int
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @return string
     */
    public function getConditionType()
    {
        return $this->conditionType;
    }

    /**
     * @return mixed
     */
    public function getIsFulfilled()
    {
        return $this->isFulfilled;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return int
     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * @return mixed
     */
    public function getAddedVia()
    {
        return $this->addedVia;
    }
}
