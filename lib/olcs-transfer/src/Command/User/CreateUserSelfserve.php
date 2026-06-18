<?php

/**
 * Create User Selfserve
 */

namespace Dvsa\Olcs\Transfer\Command\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\TranslateToWelshOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/user/selfserve")
 * @Transfer\Method("POST")
 */
final class CreateUserSelfserve extends AbstractCommand
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
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"admin", "user", "tc"}})
     */
    protected $permission;

    public function getLoginId()
    {
        return $this->loginId;
    }

    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    public function getPermission()
    {
        return $this->permission;
    }
}
