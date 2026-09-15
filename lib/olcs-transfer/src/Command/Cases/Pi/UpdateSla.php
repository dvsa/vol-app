<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Pi;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/pi/single/sla")
 * @Transfer\Method("PUT")
 */
class UpdateSla extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {"piwo_verbal", "piwo_reason", "piwo_decision"}
     *      }
     * )
     */
    protected $writtenOutcome;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $callUpLetterDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $briefToTcDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $tcWrittenReasonDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $writtenReasonLetterDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $tcWrittenDecisionDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $decisionLetterSentDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $writtenDecisionLetterDate;

    /**
     * @return String
     */
    public function getWrittenOutcome()
    {
        return $this->writtenOutcome;
    }

    /**
     * @return mixed
     */
    public function getCallUpLetterDate()
    {
        return $this->callUpLetterDate;
    }

    /**
     * @return mixed
     */
    public function getBriefToTcDate()
    {
        return $this->briefToTcDate;
    }

    /**
     * @return mixed
     */
    public function getTcWrittenReasonDate()
    {
        return $this->tcWrittenReasonDate;
    }

    /**
     * @return mixed
     */
    public function getWrittenReasonLetterDate()
    {
        return $this->writtenReasonLetterDate;
    }

    /**
     * @return mixed
     */
    public function getTcWrittenDecisionDate()
    {
        return $this->tcWrittenDecisionDate;
    }

    /**
     * @return mixed
     */
    public function getDecisionLetterSentDate()
    {
        return $this->decisionLetterSentDate;
    }

    /**
     * @return mixed
     */
    public function getWrittenDecisionLetterDate()
    {
        return $this->writtenDecisionLetterDate;
    }
}
