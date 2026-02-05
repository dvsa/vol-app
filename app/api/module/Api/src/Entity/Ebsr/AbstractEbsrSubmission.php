<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractEbsrSubmission Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="ebsr_submission",
 *    indexes={
 *        @ORM\Index(name="ix_ebsr_submission_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_ebsr_submission_ebsr_submission_status_id", columns={"ebsr_submission_status_id"}),
 *        @ORM\Index(name="ix_ebsr_submission_ebsr_submission_type_id", columns={"ebsr_submission_type_id"}),
 *        @ORM\Index(name="ix_ebsr_submission_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="uk_ebsr_submission_document_id", columns={"document_id"}),
 *        @ORM\Index(name="uk_ebsr_submission_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_ebsr_submission_document_id", columns={"document_id"}),
 *        @ORM\UniqueConstraint(name="uk_ebsr_submission_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractEbsrSubmission implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
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
     * EbsrSubmissionStatus
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="ebsr_submission_status_id", referencedColumnName="id")
     */
    protected $ebsrSubmissionStatus;

    /**
     * EbsrSubmissionType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="ebsr_submission_type_id", referencedColumnName="id")
     */
    protected $ebsrSubmissionType;

    /**
     * Foreign Key to document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=true)
     */
    protected $document;

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
     * Foreign Key to organisation
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Organisation", fetch="LAZY")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    protected $organisation;

    /**
     * Submitted date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="submitted_date", nullable=true)
     */
    protected $submittedDate;

    /**
     * Licence no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="licence_no", length=9, nullable=true)
     */
    protected $licenceNo;

    /**
     * Organisation email address
     *
     * @var string
     *
     * @ORM\Column(type="string", name="organisation_email_address", length=255, nullable=true)
     */
    protected $organisationEmailAddress;

    /**
     * Application classification
     *
     * @var string
     *
     * @ORM\Column(type="string", name="application_classification", length=32, nullable=true)
     */
    protected $applicationClassification;

    /**
     * Variation no
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="variation_no", nullable=true)
     */
    protected $variationNo;

    /**
     * Registration no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="registration_no", length=4, nullable=true)
     */
    protected $registrationNo;

    /**
     * Validation start
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="validation_start", nullable=true)
     */
    protected $validationStart;

    /**
     * Validation end
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="validation_end", nullable=true)
     */
    protected $validationEnd;

    /**
     * Publish start
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="publish_start", nullable=true)
     */
    protected $publishStart;

    /**
     * Publish end
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="publish_end", nullable=true)
     */
    protected $publishEnd;

    /**
     * Process start
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="process_start", nullable=true)
     */
    protected $processStart;

    /**
     * Process end
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="process_end", nullable=true)
     */
    protected $processEnd;

    /**
     * Ebsr submission result
     *
     * @var string
     *
     * @ORM\Column(type="string", name="ebsr_submission_result", length=10000, nullable=true)
     */
    protected $ebsrSubmissionResult;

    /**
     * Distribute start
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="distribute_start", nullable=true)
     */
    protected $distributeStart;

    /**
     * Distribute end
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="distribute_end", nullable=true)
     */
    protected $distributeEnd;

    /**
     * Distribute expire
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="distribute_expire", nullable=true)
     */
    protected $distributeExpire;

    /**
     * isFromFtp
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_from_ftp", nullable=false, options={"default": 0})
     */
    protected $isFromFtp = 0;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Txc version
     *
     * @var string
     *
     * @ORM\Column(type="string", name="txc_version", length=10, nullable=true)
     */
    protected $txcVersion;

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
     * @return EbsrSubmission
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
     * Set the ebsr submission status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $ebsrSubmissionStatus new value being set
     *
     * @return EbsrSubmission
     */
    public function setEbsrSubmissionStatus($ebsrSubmissionStatus)
    {
        $this->ebsrSubmissionStatus = $ebsrSubmissionStatus;

        return $this;
    }

    /**
     * Get the ebsr submission status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getEbsrSubmissionStatus()
    {
        return $this->ebsrSubmissionStatus;
    }

    /**
     * Set the ebsr submission type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $ebsrSubmissionType new value being set
     *
     * @return EbsrSubmission
     */
    public function setEbsrSubmissionType($ebsrSubmissionType)
    {
        $this->ebsrSubmissionType = $ebsrSubmissionType;

        return $this;
    }

    /**
     * Get the ebsr submission type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getEbsrSubmissionType()
    {
        return $this->ebsrSubmissionType;
    }

    /**
     * Set the document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $document new value being set
     *
     * @return EbsrSubmission
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get the document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg new value being set
     *
     * @return EbsrSubmission
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
     * Set the organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation new value being set
     *
     * @return EbsrSubmission
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get the organisation
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set the submitted date
     *
     * @param \DateTime $submittedDate new value being set
     *
     * @return EbsrSubmission
     */
    public function setSubmittedDate($submittedDate)
    {
        $this->submittedDate = $submittedDate;

        return $this;
    }

    /**
     * Get the submitted date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getSubmittedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->submittedDate);
        }

        return $this->submittedDate;
    }

    /**
     * Set the licence no
     *
     * @param string $licenceNo new value being set
     *
     * @return EbsrSubmission
     */
    public function setLicenceNo($licenceNo)
    {
        $this->licenceNo = $licenceNo;

        return $this;
    }

    /**
     * Get the licence no
     *
     * @return string     */
    public function getLicenceNo()
    {
        return $this->licenceNo;
    }

    /**
     * Set the organisation email address
     *
     * @param string $organisationEmailAddress new value being set
     *
     * @return EbsrSubmission
     */
    public function setOrganisationEmailAddress($organisationEmailAddress)
    {
        $this->organisationEmailAddress = $organisationEmailAddress;

        return $this;
    }

    /**
     * Get the organisation email address
     *
     * @return string     */
    public function getOrganisationEmailAddress()
    {
        return $this->organisationEmailAddress;
    }

    /**
     * Set the application classification
     *
     * @param string $applicationClassification new value being set
     *
     * @return EbsrSubmission
     */
    public function setApplicationClassification($applicationClassification)
    {
        $this->applicationClassification = $applicationClassification;

        return $this;
    }

    /**
     * Get the application classification
     *
     * @return string     */
    public function getApplicationClassification()
    {
        return $this->applicationClassification;
    }

    /**
     * Set the variation no
     *
     * @param int $variationNo new value being set
     *
     * @return EbsrSubmission
     */
    public function setVariationNo($variationNo)
    {
        $this->variationNo = $variationNo;

        return $this;
    }

    /**
     * Get the variation no
     *
     * @return int     */
    public function getVariationNo()
    {
        return $this->variationNo;
    }

    /**
     * Set the registration no
     *
     * @param string $registrationNo new value being set
     *
     * @return EbsrSubmission
     */
    public function setRegistrationNo($registrationNo)
    {
        $this->registrationNo = $registrationNo;

        return $this;
    }

    /**
     * Get the registration no
     *
     * @return string     */
    public function getRegistrationNo()
    {
        return $this->registrationNo;
    }

    /**
     * Set the validation start
     *
     * @param \DateTime $validationStart new value being set
     *
     * @return EbsrSubmission
     */
    public function setValidationStart($validationStart)
    {
        $this->validationStart = $validationStart;

        return $this;
    }

    /**
     * Get the validation start
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getValidationStart($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->validationStart);
        }

        return $this->validationStart;
    }

    /**
     * Set the validation end
     *
     * @param \DateTime $validationEnd new value being set
     *
     * @return EbsrSubmission
     */
    public function setValidationEnd($validationEnd)
    {
        $this->validationEnd = $validationEnd;

        return $this;
    }

    /**
     * Get the validation end
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getValidationEnd($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->validationEnd);
        }

        return $this->validationEnd;
    }

    /**
     * Set the publish start
     *
     * @param \DateTime $publishStart new value being set
     *
     * @return EbsrSubmission
     */
    public function setPublishStart($publishStart)
    {
        $this->publishStart = $publishStart;

        return $this;
    }

    /**
     * Get the publish start
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPublishStart($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->publishStart);
        }

        return $this->publishStart;
    }

    /**
     * Set the publish end
     *
     * @param \DateTime $publishEnd new value being set
     *
     * @return EbsrSubmission
     */
    public function setPublishEnd($publishEnd)
    {
        $this->publishEnd = $publishEnd;

        return $this;
    }

    /**
     * Get the publish end
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPublishEnd($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->publishEnd);
        }

        return $this->publishEnd;
    }

    /**
     * Set the process start
     *
     * @param \DateTime $processStart new value being set
     *
     * @return EbsrSubmission
     */
    public function setProcessStart($processStart)
    {
        $this->processStart = $processStart;

        return $this;
    }

    /**
     * Get the process start
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getProcessStart($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->processStart);
        }

        return $this->processStart;
    }

    /**
     * Set the process end
     *
     * @param \DateTime $processEnd new value being set
     *
     * @return EbsrSubmission
     */
    public function setProcessEnd($processEnd)
    {
        $this->processEnd = $processEnd;

        return $this;
    }

    /**
     * Get the process end
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getProcessEnd($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->processEnd);
        }

        return $this->processEnd;
    }

    /**
     * Set the ebsr submission result
     *
     * @param string $ebsrSubmissionResult new value being set
     *
     * @return EbsrSubmission
     */
    public function setEbsrSubmissionResult($ebsrSubmissionResult)
    {
        $this->ebsrSubmissionResult = $ebsrSubmissionResult;

        return $this;
    }

    /**
     * Get the ebsr submission result
     *
     * @return string     */
    public function getEbsrSubmissionResult()
    {
        return $this->ebsrSubmissionResult;
    }

    /**
     * Set the distribute start
     *
     * @param \DateTime $distributeStart new value being set
     *
     * @return EbsrSubmission
     */
    public function setDistributeStart($distributeStart)
    {
        $this->distributeStart = $distributeStart;

        return $this;
    }

    /**
     * Get the distribute start
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDistributeStart($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->distributeStart);
        }

        return $this->distributeStart;
    }

    /**
     * Set the distribute end
     *
     * @param \DateTime $distributeEnd new value being set
     *
     * @return EbsrSubmission
     */
    public function setDistributeEnd($distributeEnd)
    {
        $this->distributeEnd = $distributeEnd;

        return $this;
    }

    /**
     * Get the distribute end
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDistributeEnd($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->distributeEnd);
        }

        return $this->distributeEnd;
    }

    /**
     * Set the distribute expire
     *
     * @param \DateTime $distributeExpire new value being set
     *
     * @return EbsrSubmission
     */
    public function setDistributeExpire($distributeExpire)
    {
        $this->distributeExpire = $distributeExpire;

        return $this;
    }

    /**
     * Get the distribute expire
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDistributeExpire($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->distributeExpire);
        }

        return $this->distributeExpire;
    }

    /**
     * Set the is from ftp
     *
     * @param string $isFromFtp new value being set
     *
     * @return EbsrSubmission
     */
    public function setIsFromFtp($isFromFtp)
    {
        $this->isFromFtp = $isFromFtp;

        return $this;
    }

    /**
     * Get the is from ftp
     *
     * @return string     */
    public function getIsFromFtp()
    {
        return $this->isFromFtp;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return EbsrSubmission
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
     * Set the txc version
     *
     * @param string $txcVersion new value being set
     *
     * @return EbsrSubmission
     */
    public function setTxcVersion($txcVersion)
    {
        $this->txcVersion = $txcVersion;

        return $this;
    }

    /**
     * Get the txc version
     *
     * @return string     */
    public function getTxcVersion()
    {
        return $this->txcVersion;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return EbsrSubmission
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
