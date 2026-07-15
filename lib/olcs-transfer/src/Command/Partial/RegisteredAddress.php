<?php

namespace Dvsa\Olcs\Transfer\Command\Partial;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Registered Address partial
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RegisteredAddress
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    public $addressLine1;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    public $addressLine2;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    public $addressLine3;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    public $addressLine4;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    public $town;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Postcode")
     */
    public $postcode;

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
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @return mixed
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @return mixed
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * @return mixed
     */
    public function getAddressLine4()
    {
        return $this->addressLine4;
    }

    /**
     * @return mixed
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }
}
