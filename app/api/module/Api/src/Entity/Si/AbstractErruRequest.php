<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Si;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractErruRequest Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="erru_request",
 *    indexes={
 *        @ORM\Index(name="fk_erru_request_community_licence_status_ref_data_id", columns={"community_licence_status"}),
 *        @ORM\Index(name="ix_erru_request_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_erru_request_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_erru_request_member_state_code", columns={"member_state_code"}),
 *        @ORM\Index(name="ix_erru_request_msi_type", columns={"msi_type"}),
 *        @ORM\Index(name="ix_erru_request_response_user_id", columns={"response_user_id"}),
 *        @ORM\Index(name="uk_erru_request_case_id", columns={"case_id"}),
 *        @ORM\Index(name="uk_erru_request_request_document_id", columns={"request_document_id"}),
 *        @ORM\Index(name="uk_erru_request_response_document_id", columns={"response_document_id"}),
 *        @ORM\Index(name="uk_erru_request_workflow_id", columns={"workflow_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_erru_request_case_id", columns={"case_id"}),
 *        @ORM\UniqueConstraint(name="uk_erru_request_request_document_id", columns={"request_document_id"}),
 *        @ORM\UniqueConstraint(name="uk_erru_request_response_document_id", columns={"response_document_id"}),
 *        @ORM\UniqueConstraint(name="uk_erru_request_workflow_id", columns={"workflow_id"})
 *    }
 * )
 */
abstract class AbstractErruRequest implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

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
     * Foreign Key to cases
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    protected $case;

    /**
     * Foreign Key to document for the incoming erru xml
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="request_document_id", referencedColumnName="id", nullable=true)
     */
    protected $requestDocument;

    /**
     * Foreign Key to document for the msi response xml
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="response_document_id", referencedColumnName="id", nullable=true)
     */
    protected $responseDocument;

    /**
     * Two letter EU member state code
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\Country
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\Country", fetch="LAZY")
     * @ORM\JoinColumn(name="member_state_code", referencedColumnName="id")
     */
    protected $memberStateCode;

    /**
     * Most Serious Incident type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="msi_type", referencedColumnName="id")
     */
    protected $msiType;

    /**
     * CommunityLicenceStatus
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="community_licence_status", referencedColumnName="id", nullable=true)
     */
    protected $communityLicenceStatus;

    /**
     * ResponseUser
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="response_user_id", referencedColumnName="id", nullable=true)
     */
    protected $responseUser;

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
     * European authority that created/requested the case
     *
     * @var string
     *
     * @ORM\Column(type="string", name="originating_authority", length=50, nullable=false)
     */
    protected $originatingAuthority = '';

    /**
     * Transport undertaking name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="transport_undertaking_name", length=100, nullable=false)
     */
    protected $transportUndertakingName = '';

    /**
     * Vehicle registration mark
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vrm", length=15, nullable=false)
     */
    protected $vrm = '';

    /**
     * ERRU business case GUID
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notification_number", length=36, nullable=true)
     */
    protected $notificationNumber;

    /**
     * ERRU workflow GUID
     *
     * @var string
     *
     * @ORM\Column(type="string", name="workflow_id", length=36, nullable=false)
     */
    protected $workflowId = '';

    /**
     * Community licence number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="community_licence_number", length=32, nullable=false, options={"default": "unknown"})
     */
    protected $communityLicenceNumber = 'unknown';

    /**
     * Tot auth vehicles
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="tot_auth_vehicles", nullable=false, options={"default": 0})
     */
    protected $totAuthVehicles = 0;

    /**
     * Response time
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="response_time", nullable=true)
     */
    protected $responseTime;

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
     * @return ErruRequest
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
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case new value being set
     *
     * @return ErruRequest
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
     * Set the request document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $requestDocument new value being set
     *
     * @return ErruRequest
     */
    public function setRequestDocument($requestDocument)
    {
        $this->requestDocument = $requestDocument;

        return $this;
    }

    /**
     * Get the request document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document     */
    public function getRequestDocument()
    {
        return $this->requestDocument;
    }

    /**
     * Set the response document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $responseDocument new value being set
     *
     * @return ErruRequest
     */
    public function setResponseDocument($responseDocument)
    {
        $this->responseDocument = $responseDocument;

        return $this;
    }

    /**
     * Get the response document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document     */
    public function getResponseDocument()
    {
        return $this->responseDocument;
    }

    /**
     * Set the member state code
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\Country $memberStateCode new value being set
     *
     * @return ErruRequest
     */
    public function setMemberStateCode($memberStateCode)
    {
        $this->memberStateCode = $memberStateCode;

        return $this;
    }

    /**
     * Get the member state code
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\Country     */
    public function getMemberStateCode()
    {
        return $this->memberStateCode;
    }

    /**
     * Set the msi type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $msiType new value being set
     *
     * @return ErruRequest
     */
    public function setMsiType($msiType)
    {
        $this->msiType = $msiType;

        return $this;
    }

    /**
     * Get the msi type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getMsiType()
    {
        return $this->msiType;
    }

    /**
     * Set the community licence status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $communityLicenceStatus new value being set
     *
     * @return ErruRequest
     */
    public function setCommunityLicenceStatus($communityLicenceStatus)
    {
        $this->communityLicenceStatus = $communityLicenceStatus;

        return $this;
    }

    /**
     * Get the community licence status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getCommunityLicenceStatus()
    {
        return $this->communityLicenceStatus;
    }

    /**
     * Set the response user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $responseUser new value being set
     *
     * @return ErruRequest
     */
    public function setResponseUser($responseUser)
    {
        $this->responseUser = $responseUser;

        return $this;
    }

    /**
     * Get the response user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getResponseUser()
    {
        return $this->responseUser;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return ErruRequest
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
     * @return ErruRequest
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
     * Set the originating authority
     *
     * @param string $originatingAuthority new value being set
     *
     * @return ErruRequest
     */
    public function setOriginatingAuthority($originatingAuthority)
    {
        $this->originatingAuthority = $originatingAuthority;

        return $this;
    }

    /**
     * Get the originating authority
     *
     * @return string     */
    public function getOriginatingAuthority()
    {
        return $this->originatingAuthority;
    }

    /**
     * Set the transport undertaking name
     *
     * @param string $transportUndertakingName new value being set
     *
     * @return ErruRequest
     */
    public function setTransportUndertakingName($transportUndertakingName)
    {
        $this->transportUndertakingName = $transportUndertakingName;

        return $this;
    }

    /**
     * Get the transport undertaking name
     *
     * @return string     */
    public function getTransportUndertakingName()
    {
        return $this->transportUndertakingName;
    }

    /**
     * Set the vrm
     *
     * @param string $vrm new value being set
     *
     * @return ErruRequest
     */
    public function setVrm($vrm)
    {
        $this->vrm = $vrm;

        return $this;
    }

    /**
     * Get the vrm
     *
     * @return string     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * Set the notification number
     *
     * @param string $notificationNumber new value being set
     *
     * @return ErruRequest
     */
    public function setNotificationNumber($notificationNumber)
    {
        $this->notificationNumber = $notificationNumber;

        return $this;
    }

    /**
     * Get the notification number
     *
     * @return string     */
    public function getNotificationNumber()
    {
        return $this->notificationNumber;
    }

    /**
     * Set the workflow id
     *
     * @param string $workflowId new value being set
     *
     * @return ErruRequest
     */
    public function setWorkflowId($workflowId)
    {
        $this->workflowId = $workflowId;

        return $this;
    }

    /**
     * Get the workflow id
     *
     * @return string     */
    public function getWorkflowId()
    {
        return $this->workflowId;
    }

    /**
     * Set the community licence number
     *
     * @param string $communityLicenceNumber new value being set
     *
     * @return ErruRequest
     */
    public function setCommunityLicenceNumber($communityLicenceNumber)
    {
        $this->communityLicenceNumber = $communityLicenceNumber;

        return $this;
    }

    /**
     * Get the community licence number
     *
     * @return string     */
    public function getCommunityLicenceNumber()
    {
        return $this->communityLicenceNumber;
    }

    /**
     * Set the tot auth vehicles
     *
     * @param int $totAuthVehicles new value being set
     *
     * @return ErruRequest
     */
    public function setTotAuthVehicles($totAuthVehicles)
    {
        $this->totAuthVehicles = $totAuthVehicles;

        return $this;
    }

    /**
     * Get the tot auth vehicles
     *
     * @return int     */
    public function getTotAuthVehicles()
    {
        return $this->totAuthVehicles;
    }

    /**
     * Set the response time
     *
     * @param \DateTime $responseTime new value being set
     *
     * @return ErruRequest
     */
    public function setResponseTime($responseTime)
    {
        $this->responseTime = $responseTime;

        return $this;
    }

    /**
     * Get the response time
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getResponseTime($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->responseTime);
        }

        return $this->responseTime;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ErruRequest
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
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}