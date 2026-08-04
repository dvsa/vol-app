<?php

/**
 * Update User
 */

namespace Dvsa\Olcs\Transfer\Command\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\TranslateToWelshOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/user/internal/single")
 * @Transfer\Method("PUT")
 */
final class UpdateUser extends AbstractCommand
{
    use TranslateToWelshOptional;

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
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"internal","local-authority","partner","operator","transport-manager"}})
     */
    protected $userType;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $team = null;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $application = null;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $transportManager = null;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $localAuthority = null;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $partnerContactDetails = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":18})
     * @Transfer\Optional
     */
    protected $licenceNumber = null;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *         "haystack": {
     *             "system-admin",
     *             "internal-limited-read-only",
     *             "internal-read-only",
     *             "internal-case-worker",
     *             "internal-admin",
     *             "internal-irhp-admin",
     *             "operator-tc",
     *             "operator-admin",
     *             "operator-user",
     *             "operator-tm",
     *             "partner-admin",
     *             "partner-user",
     *             "local-authority-admin",
     *             "local-authority-user"
     *         }
     *     }
     * )
     */
    protected $roles = [];

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\ContactDetails")
     */
    protected $contactDetails;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $accountDisabled = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"post", "email"}})
     * @Transfer\Optional
     */
    protected $resetPassword = null;

    public function getId()
    {
        return $this->id;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getUserType()
    {
        return $this->userType;
    }

    public function getTeam()
    {
        return $this->team;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getTransportManager()
    {
        return $this->transportManager;
    }

    public function getLocalAuthority()
    {
        return $this->localAuthority;
    }

    public function getPartnerContactDetails()
    {
        return $this->partnerContactDetails;
    }

    public function getLicenceNumber()
    {
        return $this->licenceNumber;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    public function getAccountDisabled()
    {
        return $this->accountDisabled;
    }

    public function getResetPassword()
    {
        return $this->resetPassword;
    }
}
