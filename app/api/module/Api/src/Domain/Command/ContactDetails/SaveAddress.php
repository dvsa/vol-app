<?php

/**
 * Save Address
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\ContactDetails;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Save Address
 *
 * @NOTE This command Creates if there is no ID, updates if there is
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SaveAddress extends AbstractCommand
{
    protected $id;

    protected $version;

    public $addressLine1;

    public $addressLine2;

    public $addressLine3;

    public $addressLine4;

    public $town;

    public $postcode;

    public $countryCode;

    public $contactType;

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

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return mixed
     */
    public function getContactType()
    {
        return $this->contactType;
    }
}
