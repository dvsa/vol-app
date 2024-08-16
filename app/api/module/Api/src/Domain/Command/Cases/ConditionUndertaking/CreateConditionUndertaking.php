<?php

/**
 * CreateConditionUndertaking.php
 */

namespace Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class CreateConditionUndertaking
 *
 * Create a condition and undertaking.
 *
 * @package Dvsa\Olcs\Transfer\Command\Cases\ConditionUndertaking
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class CreateConditionUndertaking extends AbstractCommand
{
    protected $case;

    protected $application;

    protected $licence;

    protected $operatingCentre;

    protected $conditionType;

    protected $addedVia;

    protected $action;

    protected $attachedTo;

    protected $isDraft = 'N';

    protected $isFulfilled;

    protected $s4;

    protected $notes;

    /**
     * @return mixed
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return mixed
     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * @return mixed
     */
    public function getConditionType()
    {
        return $this->conditionType;
    }

    /**
     * @return mixed
     */
    public function getAddedVia()
    {
        return $this->addedVia;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getAttachedTo()
    {
        return $this->attachedTo;
    }

    /**
     * @return mixed
     */
    public function getIsDraft()
    {
        return $this->isDraft;
    }

    /**
     * @return mixed
     */
    public function getIsFulfilled()
    {
        return $this->isFulfilled;
    }

    /**
     * @return mixed
     */
    public function getS4()
    {
        return $this->s4;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
