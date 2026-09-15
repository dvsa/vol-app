<?php

namespace Dvsa\Olcs\Transfer\Command\System;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/sla-target-date")
 * @Transfer\Method("POST")
 */
class CreateSlaTargetDate extends AbstractCommand
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $entityId = null;

    /**
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"document"}})
     */
    protected $entityType = null;

    /**
     * @var string
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $agreedDate;

    /**
     * @Transfer\Optional
     * @var string
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $targetDate;

    /**
     * @Transfer\Optional
     * @var string
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $sentDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     */
    protected $underDelegation;

    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":5,"max":4000})
     */
    protected $notes;

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return mixed
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return string
     */
    public function getAgreedDate()
    {
        return $this->agreedDate;
    }

    /**
     * @return string
     */
    public function getTargetDate()
    {
        return $this->targetDate;
    }

    /**
     * @return string
     */
    public function getSentDate()
    {
        return $this->sentDate;
    }

    /**
     * @return mixed
     */
    public function getUnderDelegation()
    {
        return $this->underDelegation;
    }

    /**
     * @return String
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
