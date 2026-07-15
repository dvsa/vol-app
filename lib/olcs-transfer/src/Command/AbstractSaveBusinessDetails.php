<?php

namespace Dvsa\Olcs\Transfer\Command;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Save (Create/Update) Business Details
 *
 * @author Dmitry Golubev <dmitrijs.golubev@valtech.co.uk>
 */
abstract class AbstractSaveBusinessDetails extends AbstractCommand
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
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     */
    protected $name;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min": 1, "max": 255})
     */
    protected $natureOfBusiness;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $companyOrLlpNo;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\RegisteredAddress")
     * @Transfer\Optional
     */
    protected $registeredAddress;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     * @Transfer\Optional
     */
    protected $tradingNames = [];

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $partial;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $allowEmail;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getTradingNames()
    {
        return $this->tradingNames;
    }

    /**
     * @return mixed
     */
    public function getNatureOfBusiness()
    {
        return $this->natureOfBusiness;
    }

    /**
     * @return mixed
     */
    public function getCompanyOrLlpNo()
    {
        return $this->companyOrLlpNo;
    }

    /**
     * @return mixed
     */
    public function getRegisteredAddress()
    {
        return $this->registeredAddress;
    }

    /**
     * @return mixed
     */
    public function getPartial()
    {
        return $this->partial;
    }

    /**
     * @return mixed
     */
    public function getAllowEmail()
    {
        return $this->allowEmail;
    }
}
