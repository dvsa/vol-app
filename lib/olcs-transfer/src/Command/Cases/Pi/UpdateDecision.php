<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Pi;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/pi/single/decision")
 * @Transfer\Method("PUT")
 */
class UpdateDecision extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;
    use FieldType\TrafficAreasOptional;
    use FieldType\PubTypeOptional;
    use FieldType\Witnesses;
    use FieldType\Publish;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $decidedByTc;

    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {"tc_r_dhtru", "tc_r_dtc", "tc_r_htru", "tc_r_tc"}
     *      }
     * )
     */
    protected $decidedByTcRole;

    /**
     * @var array
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected array $decisions = [];

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $licenceRevokedAtPi;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $licenceSuspendedAtPi;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $licenceCurtailedAtPi;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $decisionDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $notificationDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":5,"max":4000})
     */
    protected $decisionNotes;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":32})
     * @Transfer\Optional
     */
    protected $tmDecisions = [];

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $tmCalledWithOperator;

    /**
     * @return int
     */
    public function getDecidedByTc()
    {
        return $this->decidedByTc;
    }

    /**
     * @return String
     */
    public function getDecidedByTcRole()
    {
        return $this->decidedByTcRole;
    }

    /**
     * @return array
     */
    public function getDecisions(): array
    {
        return $this->decisions;
    }

    /**
     * @return mixed
     */
    public function getLicenceRevokedAtPi()
    {
        return $this->licenceRevokedAtPi;
    }

    /**
     * @return mixed
     */
    public function getLicenceSuspendedAtPi()
    {
        return $this->licenceSuspendedAtPi;
    }

    /**
     * @return mixed
     */
    public function getLicenceCurtailedAtPi()
    {
        return $this->licenceCurtailedAtPi;
    }

    /**
     * @return mixed
     */
    public function getDecisionDate()
    {
        return $this->decisionDate;
    }

    /**
     * @return mixed
     */
    public function getNotificationDate()
    {
        return $this->notificationDate;
    }

    /**
     * @return mixed
     */
    public function getDecisionNotes()
    {
        return $this->decisionNotes;
    }

    /**
     * @return array
     */
    public function getTmDecisions()
    {
        return $this->tmDecisions;
    }

    /**
     * @return string
     */
    public function getTmCalledWithOperator()
    {
        return $this->tmCalledWithOperator;
    }
}
