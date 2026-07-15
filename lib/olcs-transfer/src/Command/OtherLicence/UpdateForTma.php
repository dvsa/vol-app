<?php

namespace Dvsa\Olcs\Transfer\Command\OtherLicence;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/other-licence/single/tma")
 * @Transfer\Method("PUT")
 */
final class UpdateForTma extends AbstractCommand
{
    use Traits\Identity;
    use Traits\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    public $licNo;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {"ol_role_applicant","ol_role_lic_holder","ol_role_tm"},
     *     },
     * )
     * @Transfer\Optional
     */
    public $role;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":255})
     * @Transfer\Optional
     */
    public $operatingCentres;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    public $totalAuthVehicles;

    /**
     * @Transfer\Validator("Laminas\I18n\Validator\IsFloat")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Validator("Laminas\Validator\LessThan", options={"max": 99.9,"inclusive":true})
     * @Transfer\Optional
     */
    public $hoursPerWeek;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    public $holderName;

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get Licence No
     *
     * @return string
     */
    public function getLicNo()
    {
        return $this->licNo;
    }

    /**
     * Get Role
     *
     * @return string RefData
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Get Operation Centers
     *
     * @return string
     */
    public function getOperatingCentres()
    {
        return $this->operatingCentres;
    }

    /**
     * Get Total Auth Vehicles
     *
     * @return int
     */
    public function getTotalAuthVehicles()
    {
        return $this->totalAuthVehicles;
    }

    /**
     * Get Hours per week
     *
     * @return int
     */
    public function getHoursPerWeek()
    {
        return $this->hoursPerWeek;
    }

    /**
     * Get Holder name
     *
     * @return string
     */
    public function getHolderName()
    {
        return $this->holderName;
    }
}
