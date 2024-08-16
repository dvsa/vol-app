<?php

/**
 * Create Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Task;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateTask extends AbstractCommand
{
    protected $category;

    protected $subCategory;

    protected $description;

    protected $actionDate;

    protected $assignedToUser;

    protected $assignedToTeam;

    protected $isClosed = false;

    /**
     * @var string|boolean 'Y', 'N', or false (false should be seen as being unset which will default to 'N')
     */
    protected $urgent = false;

    protected $messaging = false;

    protected $application;

    protected $licence;

    protected $busReg;

    protected $case;

    protected $submission;

    protected $transportManager;

    protected $surrender;

    protected $irfoOrganisation;

    protected $irhpApplication;

    protected $assignedByUser;

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return mixed
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getActionDate()
    {
        return $this->actionDate;
    }

    /**
     * @return mixed
     */
    public function getAssignedToUser()
    {
        return $this->assignedToUser;
    }

    /**
     * @return mixed
     */
    public function getAssignedToTeam()
    {
        return $this->assignedToTeam;
    }

    /**
     * @return boolean
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * @return string|boolean 'Y', 'N', or false (false should be seen as being unset which will default to 'N')
     */
    public function getUrgent()
    {
        return $this->urgent;
    }

    public function getMessaging(): bool
    {
        return $this->messaging;
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
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @return int
     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * @return int
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @return int
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @return int
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * @return int
     */
    public function getIrfoOrganisation()
    {
        return $this->irfoOrganisation;
    }

    /**
     * @return int
     */
    public function getIrhpApplication()
    {
        return $this->irhpApplication;
    }

    /**
     * @return mixed
     */
    public function getAssignedByUser()
    {
        return $this->assignedByUser;
    }

    /**
     * @return mixed
     */
    public function getSurrender()
    {
        return $this->surrender;
    }
}
