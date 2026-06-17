<?php

/**
 * Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/overview")
 * @Transfer\Method("PUT")
 */
final class Overview extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $version;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"B", "C", "D", "F", "G", "H", "K", "M", "N"}})
     */
    protected $leadTcArea;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $receivedDate;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $targetCompletionDate;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\ApplicationTracking")
     */
    protected $tracking;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     */
    protected $overrideOppositionDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     */
    protected $applicationReferredToPi;

    /**
     * Gets the value of id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the value of version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Gets the value of leadTcArea.
     *
     * @return mixed
     */
    public function getLeadTcArea()
    {
        return $this->leadTcArea;
    }

    /**
     * Gets the value of receivedDate.
     *
     * @return mixed
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    /**
     * Gets the value of targetCompletionDate.
     *
     * @return mixed
     */
    public function getTargetCompletionDate()
    {
        return $this->targetCompletionDate;
    }

    /**
     * Gets the value of tracking.
     *
     * @return mixed
     */
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     *
     * @return string Y|N
     */
    public function getOverrideOppositionDate()
    {
        return $this->overrideOppositionDate;
    }

    /**
     * @return string Y|N
     */
    public function getApplicationReferredToPi()
    {
        return $this->applicationReferredToPi;
    }
}
