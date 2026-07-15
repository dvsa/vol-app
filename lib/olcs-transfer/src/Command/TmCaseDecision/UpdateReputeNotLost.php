<?php

/**
 * Update ReputeNotLost
 */

namespace Dvsa\Olcs\Transfer\Command\TmCaseDecision;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/tm-case-decision/single/repute-not-lost")
 * @Transfer\Method("PUT")
 */
final class UpdateReputeNotLost extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isMsi;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $decisionDate;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $notifiedDate = null;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":500})
     */
    protected $reputeNotLostReason = null;

    /**
     * @return string
     */
    public function getIsMsi()
    {
        return $this->isMsi;
    }

    /**
     * @return string
     */
    public function getDecisionDate()
    {
        return $this->decisionDate;
    }

    /**
     * @return string
     */
    public function getNotifiedDate()
    {
        return $this->notifiedDate;
    }

    /**
     * @return string
     */
    public function getReputeNotLostReason()
    {
        return $this->reputeNotLostReason;
    }
}
