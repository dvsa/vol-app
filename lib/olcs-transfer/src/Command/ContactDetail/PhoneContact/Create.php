<?php

namespace Dvsa\Olcs\Transfer\Command\ContactDetail\PhoneContact;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/contact-details/phone-contact")
 * @Transfer\Method("POST")
 */
class Create extends AbstractCommand
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "phone_t_primary", "phone_t_secondary"
     *          }
     *     }
     * )
     */
    public $phoneContactType;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max": 45})
     */
    protected $phoneNumber;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $contactDetailsId;

    /**
     * Get Phone Contact Type
     *
     * @return string
     */
    public function getPhoneContactType()
    {
        return $this->phoneContactType;
    }

    /**
     * Get Phone Number
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Get Contact Details Id
     *
     * @return int
     */
    public function getContactDetailsId()
    {
        return $this->contactDetailsId;
    }
}
