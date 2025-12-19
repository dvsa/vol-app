<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Doc;

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
 * AbstractDocument Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="document",
 *    indexes={
 *        @ORM\Index(name="fk_document_irhp_application_id_irhp_application_id", columns={"irhp_application_id"}),
 *        @ORM\Index(name="uk_document_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"}),
 *        @ORM\Index(name="ix_document_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_document_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_document_surrender_id", columns={"surrender_id"}),
 *        @ORM\Index(name="ix_document_submission_id", columns={"submission_id"}),
 *        @ORM\Index(name="ix_document_sub_category_id", columns={"sub_category_id"}),
 *        @ORM\Index(name="ix_document_statement_id", columns={"statement_id"}),
 *        @ORM\Index(name="ix_document_operating_centre_id", columns={"operating_centre_id"}),
 *        @ORM\Index(name="ix_document_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_document_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_document_irfo_organisation_id", columns={"irfo_organisation_id"}),
 *        @ORM\Index(name="ix_document_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_document_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_document_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_document_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_document_application_id", columns={"application_id"}),
 *        @ORM\Index(name="fk_document_messaging_message_id", columns={"messaging_message_id"}),
 *        @ORM\Index(name="fk_document_messaging_conversation_id", columns={"messaging_conversation_id"}),
 *        @ORM\Index(name="fk_document_continuation_detail_id_continuation_detail_id", columns={"continuation_detail_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_document_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"})
 *    }
 * )
 */
abstract class AbstractDocument implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to traffic_area
     *
     * @var \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea", fetch="LAZY")
     * @ORM\JoinColumn(name="traffic_area_id", referencedColumnName="id", nullable=true)
     */
    protected $trafficArea;

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
     * @ORM\JoinColumn(name="sub_category_id", referencedColumnName="id", nullable=true)
     */
    protected $subCategory;

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
     * FK to related case (cases table)
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id", nullable=true)
     */
    protected $case;

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
     * Foreign Key to operating_centre
     *
     * @var \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre", fetch="LAZY")
     * @ORM\JoinColumn(name="operating_centre_id", referencedColumnName="id", nullable=true)
     */
    protected $operatingCentre;

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
     * FK to organisation.  Only populated for international road fright operator organisations
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
     * Foreign Key to statement
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Statement
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Statement", fetch="LAZY")
     * @ORM\JoinColumn(name="statement_id", referencedColumnName="id", nullable=true)
     */
    protected $statement;

    /**
     * ContinuationDetail
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail", fetch="LAZY")
     * @ORM\JoinColumn(name="continuation_detail_id", referencedColumnName="id", nullable=true)
     */
    protected $continuationDetail;

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
     * MessagingConversation
     *
     * @var \Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation", fetch="LAZY")
     * @ORM\JoinColumn(name="messaging_conversation_id", referencedColumnName="id", nullable=true)
     */
    protected $messagingConversation;

    /**
     * MessagingMessage
     *
     * @var \Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage", fetch="LAZY")
     * @ORM\JoinColumn(name="messaging_message_id", referencedColumnName="id", nullable=true)
     */
    protected $messagingMessage;

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
     * Depending upon document store used could be filepath or unique id of stored document
     *
     * @var string
     *
     * @ORM\Column(type="string", name="document_store_id", length=1000, nullable=false)
     */
    protected $identifier = '';

    /**
     * Brief description of the document.  Sometimes user entered and sometimes set by application based on context of doc creation.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     */
    protected $description;

    /**
     * Normally file created date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="issued_date", nullable=true)
     */
    protected $issuedDate;

    /**
     * filename on disk
     *
     * @var string
     *
     * @ORM\Column(type="string", name="filename", length=1000, nullable=true)
     */
    protected $filename;

    /**
     * Metadata
     *
     * @var string
     *
     * @ORM\Column(type="string", name="metadata", length=4000, nullable=true)
     */
    protected $metadata;

    /**
     * Flag true if doc was created/uploaded by non dvsa self service user
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_external", nullable=false, options={"default": 0})
     */
    protected $isExternal = 0;

    /**
     * Was created by scanning a paper document. Used in search filter as there are many scans and removing them helps.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_scan", nullable=false, options={"default": 0})
     */
    protected $isScan = 0;

    /**
     * size in bytes
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="size", nullable=true)
     */
    protected $size;

    /**
     * Is post submission upload
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_post_submission_upload", nullable=true, options={"default": 0})
     */
    protected $isPostSubmissionUpload = 0;

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
     * used to differntiate source of data during ETL when one OLCS table relates to many OLBS. Can be dropped when fully live
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;

    /**
     * ContinuationDetails
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail", mappedBy="checklistDocument")
     */
    protected $continuationDetails;

    /**
     * Templates
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Doc\DocTemplate", mappedBy="document")
     */
    protected $templates;

    /**
     * EbsrSubmission
     *
     * @var \Dvsa\Olcs\Api\Entity\EbsrSubmission
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission", mappedBy="document", cascade={"persist"})
     */
    protected $ebsrSubmission;

    /**
     * RequestErru
     *
     * @var \Dvsa\Olcs\Api\Entity\ErruRequest
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Si\ErruRequest", mappedBy="requestDocument", cascade={"persist"})
     */
    protected $requestErru;

    /**
     * ResponseErru
     *
     * @var \Dvsa\Olcs\Api\Entity\ErruRequest
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Si\ErruRequest", mappedBy="responseDocument", cascade={"persist"})
     */
    protected $responseErru;

    /**
     * SlaTargetDate
     *
     * @var \Dvsa\Olcs\Api\Entity\SlaTargetDate
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\SlaTargetDate", mappedBy="document", cascade={"persist"})
     */
    protected $slaTargetDate;

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
        $this->continuationDetails = new ArrayCollection();
        $this->templates = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Document
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
     * Set the traffic area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea $trafficArea new value being set
     *
     * @return Document
     */
    public function setTrafficArea($trafficArea)
    {
        $this->trafficArea = $trafficArea;

        return $this;
    }

    /**
     * Get the traffic area
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * Set the category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category $category new value being set
     *
     * @return Document
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
     * @return Document
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
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return Document
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
     * @return Document
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
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case new value being set
     *
     * @return Document
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
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager new value being set
     *
     * @return Document
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
     * @return Document
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
     * Set the operating centre
     *
     * @param \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre $operatingCentre new value being set
     *
     * @return Document
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
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg new value being set
     *
     * @return Document
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
     * Set the irfo organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $irfoOrganisation new value being set
     *
     * @return Document
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
     * @return Document
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
     * Set the statement
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Statement $statement new value being set
     *
     * @return Document
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;

        return $this;
    }

    /**
     * Get the statement
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Statement     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * Set the continuation detail
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail $continuationDetail new value being set
     *
     * @return Document
     */
    public function setContinuationDetail($continuationDetail)
    {
        $this->continuationDetail = $continuationDetail;

        return $this;
    }

    /**
     * Get the continuation detail
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail     */
    public function getContinuationDetail()
    {
        return $this->continuationDetail;
    }

    /**
     * Set the irhp application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication $irhpApplication new value being set
     *
     * @return Document
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
     * Set the messaging conversation
     *
     * @param \Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation $messagingConversation new value being set
     *
     * @return Document
     */
    public function setMessagingConversation($messagingConversation)
    {
        $this->messagingConversation = $messagingConversation;

        return $this;
    }

    /**
     * Get the messaging conversation
     *
     * @return \Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation     */
    public function getMessagingConversation()
    {
        return $this->messagingConversation;
    }

    /**
     * Set the messaging message
     *
     * @param \Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage $messagingMessage new value being set
     *
     * @return Document
     */
    public function setMessagingMessage($messagingMessage)
    {
        $this->messagingMessage = $messagingMessage;

        return $this;
    }

    /**
     * Get the messaging message
     *
     * @return \Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage     */
    public function getMessagingMessage()
    {
        return $this->messagingMessage;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Document
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
     * @return Document
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
     * Set the identifier
     *
     * @param string $identifier new value being set
     *
     * @return Document
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get the identifier
     *
     * @return string     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return Document
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
     * Set the issued date
     *
     * @param \DateTime $issuedDate new value being set
     *
     * @return Document
     */
    public function setIssuedDate($issuedDate)
    {
        $this->issuedDate = $issuedDate;

        return $this;
    }

    /**
     * Get the issued date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getIssuedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->issuedDate);
        }

        return $this->issuedDate;
    }

    /**
     * Set the filename
     *
     * @param string $filename new value being set
     *
     * @return Document
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get the filename
     *
     * @return string     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set the metadata
     *
     * @param string $metadata new value being set
     *
     * @return Document
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get the metadata
     *
     * @return string     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set the is external
     *
     * @param bool $isExternal new value being set
     *
     * @return Document
     */
    public function setIsExternal($isExternal)
    {
        $this->isExternal = $isExternal;

        return $this;
    }

    /**
     * Get the is external
     *
     * @return bool     */
    public function getIsExternal()
    {
        return $this->isExternal;
    }

    /**
     * Set the is scan
     *
     * @param bool $isScan new value being set
     *
     * @return Document
     */
    public function setIsScan($isScan)
    {
        $this->isScan = $isScan;

        return $this;
    }

    /**
     * Get the is scan
     *
     * @return bool     */
    public function getIsScan()
    {
        return $this->isScan;
    }

    /**
     * Set the size
     *
     * @param int $size new value being set
     *
     * @return Document
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get the size
     *
     * @return int     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the is post submission upload
     *
     * @param bool $isPostSubmissionUpload new value being set
     *
     * @return Document
     */
    public function setIsPostSubmissionUpload($isPostSubmissionUpload)
    {
        $this->isPostSubmissionUpload = $isPostSubmissionUpload;

        return $this;
    }

    /**
     * Get the is post submission upload
     *
     * @return bool     */
    public function getIsPostSubmissionUpload()
    {
        return $this->isPostSubmissionUpload;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Document
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
     * @return Document
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
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return Document
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Set the continuation details
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $continuationDetails collection being set as the value
     *
     * @return Document
     */
    public function setContinuationDetails($continuationDetails)
    {
        $this->continuationDetails = $continuationDetails;

        return $this;
    }

    /**
     * Get the continuation details
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getContinuationDetails()
    {
        return $this->continuationDetails;
    }

    /**
     * Add a continuation details
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $continuationDetails collection being added
     *
     * @return Document
     */
    public function addContinuationDetails($continuationDetails)
    {
        if ($continuationDetails instanceof ArrayCollection) {
            $this->continuationDetails = new ArrayCollection(
                array_merge(
                    $this->continuationDetails->toArray(),
                    $continuationDetails->toArray()
                )
            );
        } elseif (!$this->continuationDetails->contains($continuationDetails)) {
            $this->continuationDetails->add($continuationDetails);
        }

        return $this;
    }

    /**
     * Remove a continuation details
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $continuationDetails collection being removed
     *
     * @return Document
     */
    public function removeContinuationDetails($continuationDetails)
    {
        if ($this->continuationDetails->contains($continuationDetails)) {
            $this->continuationDetails->removeElement($continuationDetails);
        }

        return $this;
    }

    /**
     * Set the templates
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $templates collection being set as the value
     *
     * @return Document
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;

        return $this;
    }

    /**
     * Get the templates
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Add a templates
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $templates collection being added
     *
     * @return Document
     */
    public function addTemplates($templates)
    {
        if ($templates instanceof ArrayCollection) {
            $this->templates = new ArrayCollection(
                array_merge(
                    $this->templates->toArray(),
                    $templates->toArray()
                )
            );
        } elseif (!$this->templates->contains($templates)) {
            $this->templates->add($templates);
        }

        return $this;
    }

    /**
     * Remove a templates
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $templates collection being removed
     *
     * @return Document
     */
    public function removeTemplates($templates)
    {
        if ($this->templates->contains($templates)) {
            $this->templates->removeElement($templates);
        }

        return $this;
    }

    /**
     * Set the ebsr submission
     *
     * @param \Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission $ebsrSubmission entity being set as the value
     *
     * @return Document
     */
    public function setEbsrSubmission($ebsrSubmission)
    {
        $this->ebsrSubmission = $ebsrSubmission;

        return $this;
    }

    /**
     * Get the ebsr submission
     *
     * @return \Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission     */
    public function getEbsrSubmission()
    {
        return $this->ebsrSubmission;
    }

    /**
     * Set the request erru
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\ErruRequest $requestErru entity being set as the value
     *
     * @return Document
     */
    public function setRequestErru($requestErru)
    {
        $this->requestErru = $requestErru;

        return $this;
    }

    /**
     * Get the request erru
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\ErruRequest     */
    public function getRequestErru()
    {
        return $this->requestErru;
    }

    /**
     * Set the response erru
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\ErruRequest $responseErru entity being set as the value
     *
     * @return Document
     */
    public function setResponseErru($responseErru)
    {
        $this->responseErru = $responseErru;

        return $this;
    }

    /**
     * Get the response erru
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\ErruRequest     */
    public function getResponseErru()
    {
        return $this->responseErru;
    }

    /**
     * Set the sla target date
     *
     * @param \Dvsa\Olcs\Api\Entity\System\SlaTargetDate $slaTargetDate entity being set as the value
     *
     * @return Document
     */
    public function setSlaTargetDate($slaTargetDate)
    {
        $this->slaTargetDate = $slaTargetDate;

        return $this;
    }

    /**
     * Get the sla target date
     *
     * @return \Dvsa\Olcs\Api\Entity\System\SlaTargetDate     */
    public function getSlaTargetDate()
    {
        return $this->slaTargetDate;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}