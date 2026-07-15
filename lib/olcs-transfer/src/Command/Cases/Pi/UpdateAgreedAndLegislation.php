<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Pi;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/pi/single/agreed")
 * @Transfer\Method("PUT")
 */
class UpdateAgreedAndLegislation extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;
    use FieldType\CommentOptional;

    /**
     * @var string
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $agreedDate;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $agreedByTc;

    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {"tc_r_dhtru", "tc_r_dtc", "tc_r_htru", "tc_r_tc"}
     *      }
     * )
     */
    protected $agreedByTcRole;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $assignedCaseworker;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isEcmsCase = null;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $ecmsFirstReceivedDate;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     */
    protected $piTypes = [];

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $reasons = [];

    /**
     * Get Agreed Date
     *
     * @return string
     */
    public function getAgreedDate()
    {
        return $this->agreedDate;
    }

    /**
     * Get Agreed by Tc
     *
     * @return int
     */
    public function getAgreedByTc()
    {
        return $this->agreedByTc;
    }

    /**
     * Get Agreed by Tc Role
     *
     * @return string
     */
    public function getAgreedByTcRole()
    {
        return $this->agreedByTcRole;
    }

    /**
     * Get Assigned Caseworker
     *
     * @return int
     */
    public function getAssignedCaseworker()
    {
        return $this->assignedCaseworker;
    }

    /**
     * Get isEcmsCase
     *
     * @return string
     */
    public function getIsEcmsCase()
    {
        return $this->isEcmsCase;
    }

    /**
     * Get Ecms First Received Date
     *
     * @return string
     */
    public function getEcmsFirstReceivedDate()
    {
        return $this->ecmsFirstReceivedDate;
    }

    /**
     * Get Pi Types
     *
     * @return array
     */
    public function getPiTypes()
    {
        return $this->piTypes;
    }

    /**
     * Get Reasons
     *
     * @return array
     */
    public function getReasons()
    {
        return $this->reasons;
    }
}
