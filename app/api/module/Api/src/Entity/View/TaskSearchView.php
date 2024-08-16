<?php

/**
 * Task Search View
 *
 * @NOTE: This walks and talks like an entity but be warned, it is backed
 * by a view. As such it is is nicely readable and searchable, but writes
 * are a no go.
 *
 * You'll notice that the entity has no setters; this is intentional to
 * try and prevent accidental writes. It's marked as readOnly too to
 * prevent doctrine including it in any flushes
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Entity\View;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;

/**
 * Task Search View
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="task_search_view")
 */
class TaskSearchView implements BundleSerializableInterface
{
    use BundleSerializableTrait;

    /**
     * Id
     *
     * @var int
     *
     * NOTE: The ID annotation here is to allow doctrine to create the table (Even though we remove it later)
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     */
    protected $id;

    /**
     * Is closed
     *
     * @var int
     *
     * @ORM\Column(type="yesno", name="is_closed")
     */
    protected $isClosed = 0;

    /**
     * @var int
     * @ORM\Column(type="yesno", name="messaging")
     */
    protected $messaging = 0;

    /**
     * Is urgent
     *
     * @var int
     *
     * @ORM\Column(type="yesno", name="urgent")
     */
    protected $urgent = 0;

    /**
     * Action date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="action_date")
     */
    protected $actionDate;

    /**
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name_display")
     */
    protected $name;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description")
     */
    protected $description;

    /**
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="team_name")
     */
    protected $teamName;

    /**
     * Operator name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="op_name")
     */
    protected $opName;

    /**
     * IRFO operator name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="irfo_op_name")
     */
    protected $irfoOpName;

    /**
     * Family name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="family_name")
     */
    protected $familyName;
    /**
     * Category ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="category_id")
     */
    protected $category;

    /**
     * Category Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="category_name")
     */
    protected $categoryName;

    /**
     * Sub Category ID
     *
     * @var string
     *
     * @ORM\Column(type="string", name="task_sub_category_id")
     */
    protected $taskSubCategory;

    /**
     * Sub Category Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="task_sub_category_name")
     */
    protected $taskSubCategoryName;

    /**
     * Sub type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="task_sub_type")
     */
    protected $taskSubType;

    /**
     * Owner ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="assigned_to_user_id")
     */
    protected $assignedToUser;

    /**
     * Owner
     *
     * @var string
     *
     * @ORM\Column(type="string", name="user_name")
     */
    protected $ownerName;

    /**
     * Team ID
     *
     * @var string
     *
     * @ORM\Column(type="integer", name="assigned_to_team_id")
     */
    protected $assignedToTeam;

    /**
     * Licence ID
     *
     * This may not be the same as the link_id ,e.g. for application tasks
     * this will be the 'parent' licence id
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="lic_id")
     */
    protected $licenceId;

    /**
     * Licence number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="lic_no")
     */
    protected $licenceNo;

    /**
     * Application ID
     * @var int
     *
     * @ORM\Column(type="integer", name="application_id")
     */
    protected $applicationId;

    /**
     * Bus Reg ID
     * @var int
     *
     * @ORM\Column(type="integer", name="bus_reg_id")
     */
    protected $busRegId;

    /**
     * Case ID
     * @var int
     *
     * @ORM\Column(type="integer", name="case_id")
     */
    protected $caseId;

    /**
     * Transport Manager ID
     * @var int
     *
     * @ORM\Column(type="integer", name="tm_id")
     */
    protected $transportManagerId;

    /**
     * IRFO Organisation ID
     * @var int
     *
     * @ORM\Column(type="integer", name="irfo_organisation_id")
     */
    protected $irfoOrganisationId;

    /**
     * Submission ID
     * @var int
     *
     * @ORM\Column(type="integer", name="submission_id")
     */
    protected $submissionId;

    /**
     * Name
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="link_id")
     */
    protected $linkId;

    /**
     * Link display
     *
     * @var string
     *
     * @ORM\Column(type="string", name="link_display")
     */
    protected $linkDisplay;

    /**
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="link_type")
     */
    protected $linkType;

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the is closed
     *
     * @return int
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    public function getMessaging(): int
    {
        return $this->messaging;
    }

    /**
     * Get the urgent flag
     *
     * @return int
     */
    public function getUrgent()
    {
        return $this->urgent;
    }

    /**
     * Get the action date
     *
     * @return \DateTime
     */
    public function getActionDate()
    {
        return $this->actionDate;
    }

    /**
     * Get the team name
     *
     * @return string
     */
    public function getTeamName()
    {
        return $this->teamName;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the operator(?) name
     *
     * @return string
     */
    public function getOpName()
    {
        return $this->opName;
    }

    /**
     * Get the IRFO operator name
     *
     * @return string
     */
    public function getIrfoOpName()
    {
        return $this->irfoOpName;
    }

    /**
     * Get the family name
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Get the category ID
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get the category name
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Get the sub category ID
     *
     * @return int
     */
    public function getTaskSubCategory()
    {
        return $this->taskSubCategory;
    }

    /**
     * Get the sub category name
     *
     * @return string
     */
    public function getTaskSubCategoryName()
    {
        return $this->taskSubCategoryName;
    }

    /**
     * Get the sub type
     *
     * @return string
     */
    public function getTaskSubType()
    {
        return $this->taskSubType;
    }

    /**
     * Get the task's user ID
     *
     * @return int
     */
    public function getAssignedToUser()
    {
        return $this->assignedToUser;
    }

    /**
     * Get the task's user name
     *
     * @return string
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * Get the task's team ID
     *
     * @return int
     */
    public function getAssignedToTeam()
    {
        return $this->assignedToTeam;
    }

    /**
     * Get the licence id
     *
     * @return int
     */
    public function getLicenceId()
    {
        return $this->licenceId;
    }

    /**
     * Get the licence number
     *
     * @return string
     */
    public function getLicenceNo()
    {
        return $this->licenceNo;
    }

    /**
     * Get the application id
     *
     * @return int
     */
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /**
     * Get the submission id
     *
     * @return int
     */
    public function getSubmissionId()
    {
        return $this->submissionId;
    }

    /**
     * Get the bus reg id
     *
     * @return int
     */
    public function getBusRegId()
    {
        return $this->busRegId;
    }

    /**
     * Get the case id
     *
     * @return int
     */
    public function getCaseId()
    {
        return $this->caseId;
    }

    /**
     * Get the transport manager id
     *
     * @return int
     */
    public function getTransportManagerId()
    {
        return $this->transportManagerId;
    }

    /**
     * Get the IRFO organisation id
     *
     * @return int
     */
    public function getIrfoOrganisationId()
    {
        return $this->irfoOrganisationId;
    }

    /**
     * Get link id
     *
     * @return int
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * Get link type
     *
     * @return string
     */
    public function getLinkType()
    {
        return $this->linkType;
    }

    /**
     * Get link display
     *
     * @return string
     */
    public function getLinkDisplay()
    {
        return $this->linkDisplay;
    }
}
