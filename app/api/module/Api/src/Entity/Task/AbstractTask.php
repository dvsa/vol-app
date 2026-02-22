<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Task;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractTask Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="task",
 *    indexes={
 *        @ORM\Index(name="ix_task_assigned_by_user_id", columns={"assigned_by_user_id"}),
 *        @ORM\Index(name="uk_task_olbs_key", columns={"olbs_key"}),
 *        @ORM\Index(name="ix_task_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_task_surrender_id", columns={"surrender_id"}),
 *        @ORM\Index(name="ix_task_submission_id", columns={"submission_id"}),
 *        @ORM\Index(name="ix_task_sub_category_id", columns={"sub_category_id"}),
 *        @ORM\Index(name="ix_task_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_task_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_task_irhp_application_id", columns={"irhp_application_id"}),
 *        @ORM\Index(name="ix_task_irfo_organisation_id", columns={"irfo_organisation_id"}),
 *        @ORM\Index(name="ix_task_description_category_id_sub_category_id", columns={"description", "category_id", "sub_category_id"}),
 *        @ORM\Index(name="ix_task_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_task_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_task_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_task_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_task_assigned_to_user_id", columns={"assigned_to_user_id"}),
 *        @ORM\Index(name="ix_task_assigned_to_team_id", columns={"assigned_to_team_id"}),
 *        @ORM\Index(name="ix_task_application_id", columns={"application_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_task_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractTask implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Foreign Key to category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\Category
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\Category", fetch="LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * Foreign Key to sub_category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\SubCategory", fetch="LAZY")
     * @ORM\JoinColumn(name="sub_category_id", referencedColumnName="id")
     */
    protected $subCategory;

    /**
     * AssignedToUser
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="assigned_to_user_id", referencedColumnName="id", nullable=true)
     */
    protected $assignedToUser;

    /**
     * AssignedToTeam
     *
     * @var \Dvsa\Olcs\Api\Entity\User\Team
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\Team", fetch="LAZY")
     * @ORM\JoinColumn(name="assigned_to_team_id", referencedColumnName="id", nullable=true)
     */
    protected $assignedToTeam;

    /**
     * AssignedByUser
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="assigned_by_user_id", referencedColumnName="id", nullable=true)
     */
    protected $assignedByUser;

    /**
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id", nullable=true)
     */
    protected $case;

    /**
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=true)
     */
    protected $licence;

    /**
     * Foreign Key to application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\Application", fetch="LAZY")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=true)
     */
    protected $application;

    /**
     * Foreign Key to bus_reg
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg", fetch="LAZY")
     * @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id", nullable=true)
     */
    protected $busReg;

    /**
     * Foreign Key to transport_manager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id", nullable=true)
     */
    protected $transportManager;

    /**
     * Foreign key to surrender
     *
     * @var \Dvsa\Olcs\Api\Entity\Surrender
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Surrender", fetch="LAZY")
     * @ORM\JoinColumn(name="surrender_id", referencedColumnName="id", nullable=true)
     */
    protected $surrender;

    /**
     * IrfoOrganisation
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Organisation", fetch="LAZY")
     * @ORM\JoinColumn(name="irfo_organisation_id", referencedColumnName="id", nullable=true)
     */
    protected $irfoOrganisation;

    /**
     * Foreign Key to submission
     *
     * @var \Dvsa\Olcs\Api\Entity\Submission\Submission
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Submission\Submission", fetch="LAZY")
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id", nullable=true)
     */
    protected $submission;

    /**
     * IrhpApplication
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpApplication", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_application_id", referencedColumnName="id", nullable=true)
     */
    protected $irhpApplication;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    protected $lastModifiedBy;

    /**
     * isClosed
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_closed", nullable=false, options={"default": 0})
     */
    protected $isClosed = 0;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     */
    protected $description;

    /**
     * Action date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="action_date", nullable=true)
     */
    protected $actionDate;

    /**
     * urgent
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="urgent", nullable=false, options={"default": 0})
     */
    protected $urgent = 0;

    /**
     * Messaging
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="messaging", nullable=false, options={"default": 0})
     */
    protected $messaging = 0;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Used to map FKs during ETL. Can be dropped safely when OLBS decommissioned
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Initialise the collections
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise collections
     */
    public function initCollections(): void
    {
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Task
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category $category new value being set
     *
     * @return Task
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\Category     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the sub category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\SubCategory $subCategory new value being set
     *
     * @return Task
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get the sub category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\SubCategory     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Set the assigned to user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $assignedToUser new value being set
     *
     * @return Task
     */
    public function setAssignedToUser($assignedToUser)
    {
        $this->assignedToUser = $assignedToUser;

        return $this;
    }

    /**
     * Get the assigned to user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getAssignedToUser()
    {
        return $this->assignedToUser;
    }

    /**
     * Set the assigned to team
     *
     * @param \Dvsa\Olcs\Api\Entity\User\Team $assignedToTeam new value being set
     *
     * @return Task
     */
    public function setAssignedToTeam($assignedToTeam)
    {
        $this->assignedToTeam = $assignedToTeam;

        return $this;
    }

    /**
     * Get the assigned to team
     *
     * @return \Dvsa\Olcs\Api\Entity\User\Team     */
    public function getAssignedToTeam()
    {
        return $this->assignedToTeam;
    }

    /**
     * Set the assigned by user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $assignedByUser new value being set
     *
     * @return Task
     */
    public function setAssignedByUser($assignedByUser)
    {
        $this->assignedByUser = $assignedByUser;

        return $this;
    }

    /**
     * Get the assigned by user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getAssignedByUser()
    {
        return $this->assignedByUser;
    }

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case new value being set
     *
     * @return Task
     */
    public function setCase($case)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * Get the case
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return Task
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application new value being set
     *
     * @return Task
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get the application
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\Application     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg new value being set
     *
     * @return Task
     */
    public function setBusReg($busReg)
    {
        $this->busReg = $busReg;

        return $this;
    }

    /**
     * Get the bus reg
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusReg     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager new value being set
     *
     * @return Task
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;

        return $this;
    }

    /**
     * Get the transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the surrender
     *
     * @param \Dvsa\Olcs\Api\Entity\Surrender $surrender new value being set
     *
     * @return Task
     */
    public function setSurrender($surrender)
    {
        $this->surrender = $surrender;

        return $this;
    }

    /**
     * Get the surrender
     *
     * @return \Dvsa\Olcs\Api\Entity\Surrender     */
    public function getSurrender()
    {
        return $this->surrender;
    }

    /**
     * Set the irfo organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $irfoOrganisation new value being set
     *
     * @return Task
     */
    public function setIrfoOrganisation($irfoOrganisation)
    {
        $this->irfoOrganisation = $irfoOrganisation;

        return $this;
    }

    /**
     * Get the irfo organisation
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation     */
    public function getIrfoOrganisation()
    {
        return $this->irfoOrganisation;
    }

    /**
     * Set the submission
     *
     * @param \Dvsa\Olcs\Api\Entity\Submission\Submission $submission new value being set
     *
     * @return Task
     */
    public function setSubmission($submission)
    {
        $this->submission = $submission;

        return $this;
    }

    /**
     * Get the submission
     *
     * @return \Dvsa\Olcs\Api\Entity\Submission\Submission     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * Set the irhp application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication $irhpApplication new value being set
     *
     * @return Task
     */
    public function setIrhpApplication($irhpApplication)
    {
        $this->irhpApplication = $irhpApplication;

        return $this;
    }

    /**
     * Get the irhp application
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication     */
    public function getIrhpApplication()
    {
        return $this->irhpApplication;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Task
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return Task
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the is closed
     *
     * @param string $isClosed new value being set
     *
     * @return Task
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Get the is closed
     *
     * @return string     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return Task
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the action date
     *
     * @param \DateTime $actionDate new value being set
     *
     * @return Task
     */
    public function setActionDate($actionDate)
    {
        $this->actionDate = $actionDate;

        return $this;
    }

    /**
     * Get the action date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getActionDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->actionDate);
        }

        return $this->actionDate;
    }

    /**
     * Set the urgent
     *
     * @param string $urgent new value being set
     *
     * @return Task
     */
    public function setUrgent($urgent)
    {
        $this->urgent = $urgent;

        return $this;
    }

    /**
     * Get the urgent
     *
     * @return string     */
    public function getUrgent()
    {
        return $this->urgent;
    }

    /**
     * Set the messaging
     *
     * @param bool $messaging new value being set
     *
     * @return Task
     */
    public function setMessaging($messaging)
    {
        $this->messaging = $messaging;

        return $this;
    }

    /**
     * Get the messaging
     *
     * @return bool     */
    public function getMessaging()
    {
        return $this->messaging;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Task
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return Task
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
