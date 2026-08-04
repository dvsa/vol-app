<?php

namespace Dvsa\Olcs\Transfer\Command\Task;

use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusRegOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\SurrenderOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManagerOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\SubmissionOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/task")
 * @Transfer\Method("POST")
 */
final class CreateTask extends AbstractCommand
{
    use LicenceOptional;
    use ApplicationOptional;
    use TransportManagerOptional;
    use SubmissionOptional;
    use BusRegOptional;
    use CasesOptional;
    use IrhpApplicationOptional;
    use SurrenderOptional;


    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"max":255})
     */
    protected $description;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $actionDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $urgent;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $messaging;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $category;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $subCategory;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $assignedToUser;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $assignedByUser;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $assignedToTeam;

    /**
     * @Transfer\Optional
     */
    protected $irfoOrganisation = null;

    /**
     * @Transfer\Optional
     */
    protected $isClosed = false;

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
    public function getUrgent()
    {
        return $this->urgent;
    }

    /**
     * @return mixed
     */
    public function getMessaging()
    {
        return $this->messaging;
    }

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
    public function getAssignedToUser()
    {
        return $this->assignedToUser;
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
    public function getAssignedToTeam()
    {
        return $this->assignedToTeam;
    }

    /**
     * @return mixed
     */
    public function getIrfoOrganisation()
    {
        return $this->irfoOrganisation;
    }

    /**
     * @return mixed
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }
}
