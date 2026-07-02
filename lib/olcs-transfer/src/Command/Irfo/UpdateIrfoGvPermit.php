<?php

/**
 * Update IRFO GV Permit
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irfo/gv-permit/single")
 * @Transfer\Method("PUT")
 */
final class UpdateIrfoGvPermit extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irfoGvPermitType;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $yearRequired;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $inForceDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $expiryDate;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0, "inclusive":true})
     */
    protected $noOfCopies;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $isFeeExempt;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":255})
     * @Transfer\Optional
     */
    protected $exemptionDetails;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return mixed
     */
    public function getIrfoGvPermitType()
    {
        return $this->irfoGvPermitType;
    }

    /**
     * @return mixed
     */
    public function getYearRequired()
    {
        return $this->yearRequired;
    }

    /**
     * @return mixed
     */
    public function getInForceDate()
    {
        return $this->inForceDate;
    }

    /**
     * @return mixed
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @return mixed
     */
    public function getNoOfCopies()
    {
        return $this->noOfCopies;
    }

    /**
     * @return mixed
     */
    public function getIsFeeExempt()
    {
        return $this->isFeeExempt;
    }

    /**
     * @return mixed
     */
    public function getExemptionDetails()
    {
        return $this->exemptionDetails;
    }

    public function setId(mixed $id)
    {
        $this->id = $id;
    }

    public function setVersion(mixed $version)
    {
        $this->version = $version;
    }

    public function setIrfoGvPermitType(mixed $irfoGvPermitType)
    {
        $this->irfoGvPermitType = $irfoGvPermitType;
    }

    public function setYearRequired(mixed $yearRequired)
    {
        $this->yearRequired = $yearRequired;
    }

    public function setInForceDate(mixed $inForceDate)
    {
        $this->inForceDate = $inForceDate;
    }

    public function setNoOfCopies(mixed $noOfCopies)
    {
        $this->noOfCopies = $noOfCopies;
    }

    public function setIsFeeExempt(mixed $isFeeExempt)
    {
        $this->isFeeExempt = $isFeeExempt;
    }

    public function setExemptionDetails(mixed $exemptionDetails)
    {
        $this->exemptionDetails = $exemptionDetails;
    }
}
