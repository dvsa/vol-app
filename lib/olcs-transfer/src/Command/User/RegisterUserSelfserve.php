<?php

/**
 * Register User Selfserve
 */

namespace Dvsa\Olcs\Transfer\Command\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\TranslateToWelshOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Register User Selfserve
 *
 * This command is exposed on an anonymous, unauthenticated route. It must therefore only ever
 * link a new operator-admin user to an organisation the caller has demonstrably proven control
 * of: via a posted licence number (the temporary password is sent by letter to the licence
 * holder) or by creating a brand new organisation. It deliberately does NOT accept a raw
 * organisation id — that would let an anonymous caller attach themselves as admin to any existing
 * organisation (VOL-7370). The trusted, server-side consultant journey links to an existing
 * organisation via the internal RegisterUserSelfserveByOrganisation command (in the Api module)
 * instead, where the id originates from a just-created organisation rather than client input.
 *
 * NOTE: not declared `final` so the internal command above can extend it.
 *
 * @Transfer\RouteName("backend/user/selfserve/register")
 * @Transfer\Method("POST")
 */
class RegisterUserSelfserve extends AbstractCommand
{
    use TranslateToWelshOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Username")
     */
    protected $loginId;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\ContactDetails")
     */
    protected $contactDetails;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":18})
     * @Transfer\Optional
     */
    protected $licenceNumber = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":160})
     * @Transfer\Optional
     */
    protected $organisationName = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"org_t_p","org_t_pa","org_t_rc","org_t_llp","org_t_st"}})
     * @Transfer\Optional
     */
    protected $businessType;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $createdByConsultant = false;

    public function getLoginId()
    {
        return $this->loginId;
    }

    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    public function getLicenceNumber()
    {
        return $this->licenceNumber;
    }

    public function getOrganisationName()
    {
        return $this->organisationName;
    }

    public function getBusinessType()
    {
        return $this->businessType;
    }

    public function getCreatedByConsultant()
    {
        return $this->createdByConsultant;
    }
}
