<?php

namespace Dvsa\Olcs\Api\Entity\Si;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ErruRequest Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="erru_request",
 *    indexes={
 *        @ORM\Index(name="ix_erru_request_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_erru_request_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_erru_request_member_state_code", columns={"member_state_code"}),
 *        @ORM\Index(name="ix_erru_request_msi_type", columns={"msi_type"}),
 *        @ORM\Index(name="ix_erru_request_response_user_id", columns={"response_user_id"})
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
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\OneToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases",
     *     fetch="LAZY",
     *     inversedBy="erruRequest"
     * )
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id", nullable=false)
     */
    protected $case;

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
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

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
     * Member state code
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\Country
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\Country", fetch="LAZY")
     * @ORM\JoinColumn(name="member_state_code", referencedColumnName="id", nullable=false)
     */
    protected $memberStateCode;

    /**
     * Msi type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="msi_type", referencedColumnName="id", nullable=false)
     */
    protected $msiType;

    /**
     * Notification number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notification_number", length=36, nullable=true)
     */
    protected $notificationNumber;

    /**
     * Originating authority
     *
     * @var string
     *
     * @ORM\Column(type="string", name="originating_authority", length=50, nullable=false)
     */
    protected $originatingAuthority;

    /**
     * Request document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\OneToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document",
     *     fetch="LAZY",
     *     inversedBy="requestErru"
     * )
     * @ORM\JoinColumn(name="request_document_id", referencedColumnName="id", nullable=true)
     */
    protected $requestDocument;

    /**
     * Response document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\OneToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document",
     *     fetch="LAZY",
     *     inversedBy="responseErru"
     * )
     * @ORM\JoinColumn(name="response_document_id", referencedColumnName="id", nullable=true)
     */
    protected $responseDocument;

    /**
     * Response time
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="response_time", nullable=true)
     */
    protected $responseTime;

    /**
     * Response user
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="response_user_id", referencedColumnName="id", nullable=true)
     */
    protected $responseUser;

    /**
     * Transport undertaking name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="transport_undertaking_name", length=100, nullable=false)
     */
    protected $transportUndertakingName;

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
     * Vrm
     *
     * @var string
     *
     * @ORM\Column(type="string", name="vrm", length=15, nullable=false)
     */
    protected $vrm;

    /**
     * Workflow id
     *
     * @var string
     *
     * @ORM\Column(type="string", name="workflow_id", length=36, nullable=false)
     */
    protected $workflowId;

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the member state code
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\Country $memberStateCode entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\Country
     */
    public function getMemberStateCode()
    {
        return $this->memberStateCode;
    }

    /**
     * Set the msi type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $msiType entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getMsiType()
    {
        return $this->msiType;
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
     * @return string
     */
    public function getNotificationNumber()
    {
        return $this->notificationNumber;
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
     * @return string
     */
    public function getOriginatingAuthority()
    {
        return $this->originatingAuthority;
    }

    /**
     * Set the request document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $requestDocument entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    public function getRequestDocument()
    {
        return $this->requestDocument;
    }

    /**
     * Set the response document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $responseDocument entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    public function getResponseDocument()
    {
        return $this->responseDocument;
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
     * @return \DateTime|string

     */
    public function getResponseTime($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->responseTime);
        }

        return $this->responseTime;
    }

    /**
     * Set the response user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $responseUser entity being set as the value
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
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getResponseUser()
    {
        return $this->responseUser;
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
     * @return string
     */
    public function getTransportUndertakingName()
    {
        return $this->transportUndertakingName;
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
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
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
     * @return string
     */
    public function getVrm()
    {
        return $this->vrm;
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
     * @return string
     */
    public function getWorkflowId()
    {
        return $this->workflowId;
    }
}
