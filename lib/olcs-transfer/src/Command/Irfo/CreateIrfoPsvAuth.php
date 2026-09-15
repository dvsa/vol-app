<?php

/**
 * Create IrfoPsvAuth
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irfo/psv-auth")
 * @Transfer\Method("POST")
 */
final class CreateIrfoPsvAuth extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $organisation;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irfoPsvAuthType;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $validityPeriod;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\IrfoPsvAuthNumber")
     * @Transfer\Optional
     */
    protected $irfoPsvAuthNumbers = [];

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     */
    protected $inForceDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     * @Transfer\Optional
     */
    protected $expiryDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Date")
     * @Transfer\Optional
     */
    protected $applicationSentDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":30})
     */
    protected $serviceRouteFrom;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":30})
     */
    protected $serviceRouteTo;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"psv_freq_daily","psv_freq_2_weekly","psv_freq_weekly","psv_freq_fortnight","psv_freq_monthly","psv_freq_other"}})
     */
    protected $journeyFrequency;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     * @Transfer\Optional
     */
    protected $countrys = [];

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $isFeeExemptApplication;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $isFeeExemptAnnual;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":255})
     * @Transfer\Optional
     */
    protected $exemptionDetails;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0, "inclusive":true})
     */
    protected $copiesRequired;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0, "inclusive":true})
     */
    protected $copiesRequiredTotal;

    /**
     * @return mixed
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return mixed
     */
    public function getIrfoPsvAuthType()
    {
        return $this->irfoPsvAuthType;
    }

    /**
     * @return mixed
     */
    public function getValidityPeriod()
    {
        return $this->validityPeriod;
    }

    /**
     * @return mixed
     */
    public function getIrfoPsvAuthNumbers()
    {
        return $this->irfoPsvAuthNumbers;
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
    public function getApplicationSentDate()
    {
        return $this->applicationSentDate;
    }

    /**
     * @return mixed
     */
    public function getServiceRouteFrom()
    {
        return $this->serviceRouteFrom;
    }

    /**
     * @return mixed
     */
    public function getServiceRouteTo()
    {
        return $this->serviceRouteTo;
    }

    /**
     * @return mixed
     */
    public function getJourneyFrequency()
    {
        return $this->journeyFrequency;
    }

    /**
     * @return mixed
     */
    public function getCountrys()
    {
        return $this->countrys;
    }

    /**
     * @return mixed
     */
    public function getIsFeeExemptApplication()
    {
        return $this->isFeeExemptApplication;
    }

    /**
     * @return mixed
     */
    public function getIsFeeExemptAnnual()
    {
        return $this->isFeeExemptAnnual;
    }

    /**
     * @return mixed
     */
    public function getExemptionDetails()
    {
        return $this->exemptionDetails;
    }

    /**
     * @return mixed
     */
    public function getCopiesRequired()
    {
        return $this->copiesRequired;
    }

    /**
     * @return mixed
     */
    public function getCopiesRequiredTotal()
    {
        return $this->copiesRequiredTotal;
    }
}
