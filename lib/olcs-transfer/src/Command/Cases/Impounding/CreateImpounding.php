<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Impounding;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/impounding")
 * @Transfer\Method("POST")
 */
class CreateImpounding extends AbstractCommand
{
    use FieldType\Publish;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case = null;

    /**
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"impt_hearing","impt_paper"}})
     */
    protected $impoundingType = null;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $applicationReceiptDate = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":20})
     */
    protected $vrm = null;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     */
    protected $impoundingLegislationTypes = [];

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\DateTimeFormatter")
     * @Transfer\Validator("Date", options={"format": \DateTime::ISO8601})
     */
    protected $hearingDate = null;

    /**
     * @Transfer\Optional()
     */
    protected $venue = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $venueOther = null;

    /**
     * @Transfer\Optional()
     */
    protected $presidingTc = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"impo_not","impo_returned","impo_wd"}})
     */
    protected $outcome = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $outcomeSentDate = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":5,"max":4000})
     */
    protected $notes = null;

    /**
     * @return $this
     */
    public function setCase(mixed $case)
    {
        $this->case = $case;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @return $this
     */
    public function setApplicationReceiptDate(mixed $applicationReceiptDate)
    {
        $this->applicationReceiptDate = $applicationReceiptDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplicationReceiptDate()
    {
        return $this->applicationReceiptDate;
    }

    /**
     * @return $this
     */
    public function setHearingDate(mixed $hearingDate)
    {
        $this->hearingDate = $hearingDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHearingDate()
    {
        return $this->hearingDate;
    }

    /**
     * @return $this
     */
    public function setImpoundingLegislationTypes(mixed $impoundingLegislationTypes)
    {
        $this->impoundingLegislationTypes = $impoundingLegislationTypes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImpoundingLegislationTypes()
    {
        return $this->impoundingLegislationTypes;
    }

    /**
     * @return $this
     */
    public function setImpoundingType(mixed $impoundingType)
    {
        $this->impoundingType = $impoundingType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImpoundingType()
    {
        return $this->impoundingType;
    }

    /**
     * @return $this
     */
    public function setNotes(mixed $notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return $this
     */
    public function setOutcome(mixed $outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * @return $this
     */
    public function setOutcomeSentDate(mixed $outcomeSentDate)
    {
        $this->outcomeSentDate = $outcomeSentDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOutcomeSentDate()
    {
        return $this->outcomeSentDate;
    }

    /**
     * @return $this
     */
    public function setVenue(mixed $venue)
    {
        $this->venue = $venue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * @return $this
     */
    public function setVenueOther(mixed $venueOther)
    {
        $this->venueOther = $venueOther;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVenueOther()
    {
        return $this->venueOther;
    }

    /**
     * @return $this
     */
    public function setPresidingTc(mixed $presidingTc)
    {
        $this->presidingTc = $presidingTc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPresidingTc()
    {
        return $this->presidingTc;
    }

    /**
     * @return $this
     */
    public function setVrm(mixed $vrm)
    {
        $this->vrm = $vrm;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVrm()
    {
        return $this->vrm;
    }
}
