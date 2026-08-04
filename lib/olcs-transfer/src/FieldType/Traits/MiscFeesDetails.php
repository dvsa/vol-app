<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Comment
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait MiscFeesDetails
{
    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $customerReference;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $customerName;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\AddressOptional")
     */
    protected $address;

    /**
     * Get customer reference
     *
     * @return string
     */
    public function getCustomerReference()
    {
        return $this->customerReference;
    }

    /**
     * Get customer name
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * Get address
     *
     * @return array
     */
    public function getAddress()
    {
        return $this->address;
    }
}
