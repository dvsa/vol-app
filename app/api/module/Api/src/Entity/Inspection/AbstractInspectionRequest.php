<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Inspection;

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
 * AbstractInspectionRequest Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="inspection_request",
 *    indexes={
 *        @ORM\Index(name="ix_inspection_request_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_inspection_request_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_inspection_request_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_inspection_request_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_inspection_request_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_inspection_request_operating_centre_id", columns={"operating_centre_id"}),
 *        @ORM\Index(name="ix_inspection_request_report_type", columns={"report_type"}),
 *        @ORM\Index(name="ix_inspection_request_request_type", columns={"request_type"}),
 *        @ORM\Index(name="ix_inspection_request_requestor_user_id", columns={"requestor_user_id"}),
 *        @ORM\Index(name="ix_inspection_request_result_type", columns={"result_type"}),
 *        @ORM\Index(name="ix_inspection_request_task_id", columns={"task_id"}),
 *        @ORM\Index(name="uk_inspection_request_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_inspection_request_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractInspectionRequest implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id")
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
     * Foreign Key to operating_centre
     *
     * @var \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre", fetch="LAZY")
     * @ORM\JoinColumn(name="operating_centre_id", referencedColumnName="id", nullable=true)
     */
    protected $operatingCentre;

    /**
     * RequestorUser
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="requestor_user_id", referencedColumnName="id")
     */
    protected $requestorUser;

    /**
     * Foreign Key to task
     *
     * @var \Dvsa\Olcs\Api\Entity\Task\Task
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Task\Task", fetch="LAZY")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
     */
    protected $task;

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
     * RequestType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="request_type", referencedColumnName="id")
     */
    protected $requestType;

    /**
     * ResultType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="result_type", referencedColumnName="id")
     */
    protected $resultType;

    /**
     * ReportType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="report_type", referencedColumnName="id", nullable=true)
     */
    protected $reportType;

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
     * Requestor notes
     *
     * @var string
     *
     * @ORM\Column(type="text", name="requestor_notes", nullable=true)
     */
    protected $requestorNotes;

    /**
     * Inspector notes
     *
     * @var string
     *
     * @ORM\Column(type="text", name="inspector_notes", nullable=true)
     */
    protected $inspectorNotes;

    /**
     * Due date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="due_date", nullable=true)
     */
    protected $dueDate;

    /**
     * From date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="from_date", nullable=true)
     */
    protected $fromDate;

    /**
     * To date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="to_date", nullable=true)
     */
    protected $toDate;

    /**
     * Request date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="request_date", nullable=true)
     */
    protected $requestDate;

    /**
     * Return date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="return_date", nullable=true)
     */
    protected $returnDate;

    /**
     * Deferred date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="deferred_date", nullable=true)
     */
    protected $deferredDate;

    /**
     * Inspector name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="inspector_name", length=70, nullable=true)
     */
    protected $inspectorName;

    /**
     * Trailers examined no
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="trailers_examined_no", nullable=true)
     */
    protected $trailersExaminedNo;

    /**
     * Vehicles examined no
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="vehicles_examined_no", nullable=true)
     */
    protected $vehiclesExaminedNo;

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
     * @return InspectionRequest
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
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return InspectionRequest
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
     * @return InspectionRequest
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
     * Set the operating centre
     *
     * @param \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre $operatingCentre new value being set
     *
     * @return InspectionRequest
     */
    public function setOperatingCentre($operatingCentre)
    {
        $this->operatingCentre = $operatingCentre;

        return $this;
    }

    /**
     * Get the operating centre
     *
     * @return \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * Set the requestor user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $requestorUser new value being set
     *
     * @return InspectionRequest
     */
    public function setRequestorUser($requestorUser)
    {
        $this->requestorUser = $requestorUser;

        return $this;
    }

    /**
     * Get the requestor user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getRequestorUser()
    {
        return $this->requestorUser;
    }

    /**
     * Set the task
     *
     * @param \Dvsa\Olcs\Api\Entity\Task\Task $task new value being set
     *
     * @return InspectionRequest
     */
    public function setTask($task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get the task
     *
     * @return \Dvsa\Olcs\Api\Entity\Task\Task     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case new value being set
     *
     * @return InspectionRequest
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
     * Set the request type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $requestType new value being set
     *
     * @return InspectionRequest
     */
    public function setRequestType($requestType)
    {
        $this->requestType = $requestType;

        return $this;
    }

    /**
     * Get the request type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * Set the result type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $resultType new value being set
     *
     * @return InspectionRequest
     */
    public function setResultType($resultType)
    {
        $this->resultType = $resultType;

        return $this;
    }

    /**
     * Get the result type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getResultType()
    {
        return $this->resultType;
    }

    /**
     * Set the report type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $reportType new value being set
     *
     * @return InspectionRequest
     */
    public function setReportType($reportType)
    {
        $this->reportType = $reportType;

        return $this;
    }

    /**
     * Get the report type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getReportType()
    {
        return $this->reportType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return InspectionRequest
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
     * @return InspectionRequest
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
     * Set the requestor notes
     *
     * @param string $requestorNotes new value being set
     *
     * @return InspectionRequest
     */
    public function setRequestorNotes($requestorNotes)
    {
        $this->requestorNotes = $requestorNotes;

        return $this;
    }

    /**
     * Get the requestor notes
     *
     * @return string     */
    public function getRequestorNotes()
    {
        return $this->requestorNotes;
    }

    /**
     * Set the inspector notes
     *
     * @param string $inspectorNotes new value being set
     *
     * @return InspectionRequest
     */
    public function setInspectorNotes($inspectorNotes)
    {
        $this->inspectorNotes = $inspectorNotes;

        return $this;
    }

    /**
     * Get the inspector notes
     *
     * @return string     */
    public function getInspectorNotes()
    {
        return $this->inspectorNotes;
    }

    /**
     * Set the due date
     *
     * @param \DateTime $dueDate new value being set
     *
     * @return InspectionRequest
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get the due date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDueDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->dueDate);
        }

        return $this->dueDate;
    }

    /**
     * Set the from date
     *
     * @param \DateTime $fromDate new value being set
     *
     * @return InspectionRequest
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * Get the from date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getFromDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->fromDate);
        }

        return $this->fromDate;
    }

    /**
     * Set the to date
     *
     * @param \DateTime $toDate new value being set
     *
     * @return InspectionRequest
     */
    public function setToDate($toDate)
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * Get the to date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getToDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->toDate);
        }

        return $this->toDate;
    }

    /**
     * Set the request date
     *
     * @param \DateTime $requestDate new value being set
     *
     * @return InspectionRequest
     */
    public function setRequestDate($requestDate)
    {
        $this->requestDate = $requestDate;

        return $this;
    }

    /**
     * Get the request date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRequestDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->requestDate);
        }

        return $this->requestDate;
    }

    /**
     * Set the return date
     *
     * @param \DateTime $returnDate new value being set
     *
     * @return InspectionRequest
     */
    public function setReturnDate($returnDate)
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    /**
     * Get the return date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getReturnDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->returnDate);
        }

        return $this->returnDate;
    }

    /**
     * Set the deferred date
     *
     * @param \DateTime $deferredDate new value being set
     *
     * @return InspectionRequest
     */
    public function setDeferredDate($deferredDate)
    {
        $this->deferredDate = $deferredDate;

        return $this;
    }

    /**
     * Get the deferred date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDeferredDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->deferredDate);
        }

        return $this->deferredDate;
    }

    /**
     * Set the inspector name
     *
     * @param string $inspectorName new value being set
     *
     * @return InspectionRequest
     */
    public function setInspectorName($inspectorName)
    {
        $this->inspectorName = $inspectorName;

        return $this;
    }

    /**
     * Get the inspector name
     *
     * @return string     */
    public function getInspectorName()
    {
        return $this->inspectorName;
    }

    /**
     * Set the trailers examined no
     *
     * @param int $trailersExaminedNo new value being set
     *
     * @return InspectionRequest
     */
    public function setTrailersExaminedNo($trailersExaminedNo)
    {
        $this->trailersExaminedNo = $trailersExaminedNo;

        return $this;
    }

    /**
     * Get the trailers examined no
     *
     * @return int     */
    public function getTrailersExaminedNo()
    {
        return $this->trailersExaminedNo;
    }

    /**
     * Set the vehicles examined no
     *
     * @param int $vehiclesExaminedNo new value being set
     *
     * @return InspectionRequest
     */
    public function setVehiclesExaminedNo($vehiclesExaminedNo)
    {
        $this->vehiclesExaminedNo = $vehiclesExaminedNo;

        return $this;
    }

    /**
     * Get the vehicles examined no
     *
     * @return int     */
    public function getVehiclesExaminedNo()
    {
        return $this->vehiclesExaminedNo;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return InspectionRequest
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
     * @return InspectionRequest
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