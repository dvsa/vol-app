<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Permits;

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
 * AbstractIrhpApplication Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irhp_application",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_application_international_journeys", columns={"international_journeys"}),
 *        @ORM\Index(name="fk_irhp_application_sectors_id", columns={"sectors_id"}),
 *        @ORM\Index(name="ix_irhp_application_cancellation_date", columns={"cancellation_date"}),
 *        @ORM\Index(name="ix_irhp_application_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irhp_application_irhp_permit_type_id", columns={"irhp_permit_type_id"}),
 *        @ORM\Index(name="ix_irhp_application_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_irhp_application_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_irhp_application_source", columns={"source"}),
 *        @ORM\Index(name="ix_irhp_application_status", columns={"status"}),
 *        @ORM\Index(name="ix_irhp_application_withdraw_reason", columns={"withdraw_reason"}),
 *        @ORM\Index(name="ix_irhp_application_withdrawn_date", columns={"withdrawn_date"})
 *    }
 * )
 */
abstract class AbstractIrhpApplication implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id")
     */
    protected $licence;

    /**
     * InternationalJourneys
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="international_journeys", referencedColumnName="id", nullable=true)
     */
    protected $internationalJourneys;

    /**
     * Source
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="source", referencedColumnName="id")
     */
    protected $source;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id")
     */
    protected $status;

    /**
     * Sectors
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\Sectors
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\Sectors", fetch="LAZY")
     * @ORM\JoinColumn(name="sectors_id", referencedColumnName="id", nullable=true)
     */
    protected $sectors;

    /**
     * IrhpPermitType
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_permit_type_id", referencedColumnName="id")
     */
    protected $irhpPermitType;

    /**
     * WithdrawReason
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="withdraw_reason", referencedColumnName="id", nullable=true)
     */
    protected $withdrawReason;

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
     * In scope
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="in_scope", nullable=true, options={"default": 0})
     */
    protected $inScope = 0;

    /**
     * Checked answers
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="checked_answers", nullable=false, options={"default": 0})
     */
    protected $checkedAnswers = 0;

    /**
     * Declaration
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="declaration", nullable=false, options={"default": 0})
     */
    protected $declaration = 0;

    /**
     * Date received
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="date_received", nullable=false)
     */
    protected $dateReceived;

    /**
     * Cancellation date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="cancellation_date", nullable=true)
     */
    protected $cancellationDate;

    /**
     * Withdrawn date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="withdrawn_date", nullable=true)
     */
    protected $withdrawnDate;

    /**
     * Expiry date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="expiry_date", nullable=true)
     */
    protected $expiryDate;

    /**
     * Checked
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="checked", nullable=true)
     */
    protected $checked;

    /**
     * Cor certificate number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="cor_certificate_number", length=12, nullable=true)
     */
    protected $corCertificateNumber;

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
     * Countrys
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\Country", inversedBy="irhpApplications", fetch="LAZY")
     * @ORM\JoinTable(name="irhp_application_country_link",
     *     joinColumns={
     *         @ORM\JoinColumn(name="irhp_application_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $countrys;

    /**
     * Answers
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Generic\Answer", mappedBy="irhpApplication", indexBy="question_text_id")
     */
    protected $answers;

    /**
     * Documents
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", mappedBy="irhpApplication")
     */
    protected $documents;

    /**
     * Fees
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Fee\Fee", mappedBy="irhpApplication")
     */
    protected $fees;

    /**
     * IrhpPermitApplications
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication", mappedBy="irhpApplication")
     */
    protected $irhpPermitApplications;

    /**
     * IrhpPermitRequests
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRequest", mappedBy="irhpApplication")
     */
    protected $irhpPermitRequests;

    /**
     * Notes
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Note\Note", mappedBy="irhpApplication", cascade={"persist"})
     */
    protected $notes;

    /**
     * Tasks
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Task\Task", mappedBy="irhpApplication")
     */
    protected $tasks;

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
        $this->countrys = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->fees = new ArrayCollection();
        $this->irhpPermitApplications = new ArrayCollection();
        $this->irhpPermitRequests = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return IrhpApplication
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
     * @return IrhpApplication
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
     * Set the international journeys
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $internationalJourneys new value being set
     *
     * @return IrhpApplication
     */
    public function setInternationalJourneys($internationalJourneys)
    {
        $this->internationalJourneys = $internationalJourneys;

        return $this;
    }

    /**
     * Get the international journeys
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getInternationalJourneys()
    {
        return $this->internationalJourneys;
    }

    /**
     * Set the source
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $source new value being set
     *
     * @return IrhpApplication
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get the source
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status new value being set
     *
     * @return IrhpApplication
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the sectors
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\Sectors $sectors new value being set
     *
     * @return IrhpApplication
     */
    public function setSectors($sectors)
    {
        $this->sectors = $sectors;

        return $this;
    }

    /**
     * Get the sectors
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\Sectors     */
    public function getSectors()
    {
        return $this->sectors;
    }

    /**
     * Set the irhp permit type
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType $irhpPermitType new value being set
     *
     * @return IrhpApplication
     */
    public function setIrhpPermitType($irhpPermitType)
    {
        $this->irhpPermitType = $irhpPermitType;

        return $this;
    }

    /**
     * Get the irhp permit type
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType     */
    public function getIrhpPermitType()
    {
        return $this->irhpPermitType;
    }

    /**
     * Set the withdraw reason
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $withdrawReason new value being set
     *
     * @return IrhpApplication
     */
    public function setWithdrawReason($withdrawReason)
    {
        $this->withdrawReason = $withdrawReason;

        return $this;
    }

    /**
     * Get the withdraw reason
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getWithdrawReason()
    {
        return $this->withdrawReason;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return IrhpApplication
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
     * @return IrhpApplication
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
     * Set the in scope
     *
     * @param bool $inScope new value being set
     *
     * @return IrhpApplication
     */
    public function setInScope($inScope)
    {
        $this->inScope = $inScope;

        return $this;
    }

    /**
     * Get the in scope
     *
     * @return bool     */
    public function getInScope()
    {
        return $this->inScope;
    }

    /**
     * Set the checked answers
     *
     * @param bool $checkedAnswers new value being set
     *
     * @return IrhpApplication
     */
    public function setCheckedAnswers($checkedAnswers)
    {
        $this->checkedAnswers = $checkedAnswers;

        return $this;
    }

    /**
     * Get the checked answers
     *
     * @return bool     */
    public function getCheckedAnswers()
    {
        return $this->checkedAnswers;
    }

    /**
     * Set the declaration
     *
     * @param bool $declaration new value being set
     *
     * @return IrhpApplication
     */
    public function setDeclaration($declaration)
    {
        $this->declaration = $declaration;

        return $this;
    }

    /**
     * Get the declaration
     *
     * @return bool     */
    public function getDeclaration()
    {
        return $this->declaration;
    }

    /**
     * Set the date received
     *
     * @param \DateTime $dateReceived new value being set
     *
     * @return IrhpApplication
     */
    public function setDateReceived($dateReceived)
    {
        $this->dateReceived = $dateReceived;

        return $this;
    }

    /**
     * Get the date received
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getDateReceived($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->dateReceived);
        }

        return $this->dateReceived;
    }

    /**
     * Set the cancellation date
     *
     * @param \DateTime $cancellationDate new value being set
     *
     * @return IrhpApplication
     */
    public function setCancellationDate($cancellationDate)
    {
        $this->cancellationDate = $cancellationDate;

        return $this;
    }

    /**
     * Get the cancellation date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getCancellationDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->cancellationDate);
        }

        return $this->cancellationDate;
    }

    /**
     * Set the withdrawn date
     *
     * @param \DateTime $withdrawnDate new value being set
     *
     * @return IrhpApplication
     */
    public function setWithdrawnDate($withdrawnDate)
    {
        $this->withdrawnDate = $withdrawnDate;

        return $this;
    }

    /**
     * Get the withdrawn date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getWithdrawnDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->withdrawnDate);
        }

        return $this->withdrawnDate;
    }

    /**
     * Set the expiry date
     *
     * @param \DateTime $expiryDate new value being set
     *
     * @return IrhpApplication
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get the expiry date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getExpiryDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->expiryDate);
        }

        return $this->expiryDate;
    }

    /**
     * Set the checked
     *
     * @param bool $checked new value being set
     *
     * @return IrhpApplication
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Get the checked
     *
     * @return bool     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * Set the cor certificate number
     *
     * @param string $corCertificateNumber new value being set
     *
     * @return IrhpApplication
     */
    public function setCorCertificateNumber($corCertificateNumber)
    {
        $this->corCertificateNumber = $corCertificateNumber;

        return $this;
    }

    /**
     * Get the cor certificate number
     *
     * @return string     */
    public function getCorCertificateNumber()
    {
        return $this->corCertificateNumber;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrhpApplication
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
     * Set the countrys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $countrys collection being set as the value
     *
     * @return IrhpApplication
     */
    public function setCountrys($countrys)
    {
        $this->countrys = $countrys;

        return $this;
    }

    /**
     * Get the countrys
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCountrys()
    {
        return $this->countrys;
    }

    /**
     * Add a countrys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $countrys collection being added
     *
     * @return IrhpApplication
     */
    public function addCountrys($countrys)
    {
        if ($countrys instanceof ArrayCollection) {
            $this->countrys = new ArrayCollection(
                array_merge(
                    $this->countrys->toArray(),
                    $countrys->toArray()
                )
            );
        } elseif (!$this->countrys->contains($countrys)) {
            $this->countrys->add($countrys);
        }

        return $this;
    }

    /**
     * Remove a countrys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $countrys collection being removed
     *
     * @return IrhpApplication
     */
    public function removeCountrys($countrys)
    {
        if ($this->countrys->contains($countrys)) {
            $this->countrys->removeElement($countrys);
        }

        return $this;
    }

    /**
     * Set the answers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $answers collection being set as the value
     *
     * @return IrhpApplication
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;

        return $this;
    }

    /**
     * Get the answers
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Add a answers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $answers collection being added
     *
     * @return IrhpApplication
     */
    public function addAnswers($answers)
    {
        if ($answers instanceof ArrayCollection) {
            $this->answers = new ArrayCollection(
                array_merge(
                    $this->answers->toArray(),
                    $answers->toArray()
                )
            );
        } elseif (!$this->answers->contains($answers)) {
            $this->answers->add($answers);
        }

        return $this;
    }

    /**
     * Remove a answers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $answers collection being removed
     *
     * @return IrhpApplication
     */
    public function removeAnswers($answers)
    {
        if ($this->answers->contains($answers)) {
            $this->answers->removeElement($answers);
        }

        return $this;
    }

    /**
     * Set the documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being set as the value
     *
     * @return IrhpApplication
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get the documents
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $documents collection being added
     *
     * @return IrhpApplication
     */
    public function addDocuments($documents)
    {
        if ($documents instanceof ArrayCollection) {
            $this->documents = new ArrayCollection(
                array_merge(
                    $this->documents->toArray(),
                    $documents->toArray()
                )
            );
        } elseif (!$this->documents->contains($documents)) {
            $this->documents->add($documents);
        }

        return $this;
    }

    /**
     * Remove a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being removed
     *
     * @return IrhpApplication
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Set the fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $fees collection being set as the value
     *
     * @return IrhpApplication
     */
    public function setFees($fees)
    {
        $this->fees = $fees;

        return $this;
    }

    /**
     * Get the fees
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Add a fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $fees collection being added
     *
     * @return IrhpApplication
     */
    public function addFees($fees)
    {
        if ($fees instanceof ArrayCollection) {
            $this->fees = new ArrayCollection(
                array_merge(
                    $this->fees->toArray(),
                    $fees->toArray()
                )
            );
        } elseif (!$this->fees->contains($fees)) {
            $this->fees->add($fees);
        }

        return $this;
    }

    /**
     * Remove a fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $fees collection being removed
     *
     * @return IrhpApplication
     */
    public function removeFees($fees)
    {
        if ($this->fees->contains($fees)) {
            $this->fees->removeElement($fees);
        }

        return $this;
    }

    /**
     * Set the irhp permit applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitApplications collection being set as the value
     *
     * @return IrhpApplication
     */
    public function setIrhpPermitApplications($irhpPermitApplications)
    {
        $this->irhpPermitApplications = $irhpPermitApplications;

        return $this;
    }

    /**
     * Get the irhp permit applications
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitApplications()
    {
        return $this->irhpPermitApplications;
    }

    /**
     * Add a irhp permit applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitApplications collection being added
     *
     * @return IrhpApplication
     */
    public function addIrhpPermitApplications($irhpPermitApplications)
    {
        if ($irhpPermitApplications instanceof ArrayCollection) {
            $this->irhpPermitApplications = new ArrayCollection(
                array_merge(
                    $this->irhpPermitApplications->toArray(),
                    $irhpPermitApplications->toArray()
                )
            );
        } elseif (!$this->irhpPermitApplications->contains($irhpPermitApplications)) {
            $this->irhpPermitApplications->add($irhpPermitApplications);
        }

        return $this;
    }

    /**
     * Remove a irhp permit applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitApplications collection being removed
     *
     * @return IrhpApplication
     */
    public function removeIrhpPermitApplications($irhpPermitApplications)
    {
        if ($this->irhpPermitApplications->contains($irhpPermitApplications)) {
            $this->irhpPermitApplications->removeElement($irhpPermitApplications);
        }

        return $this;
    }

    /**
     * Set the irhp permit requests
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRequests collection being set as the value
     *
     * @return IrhpApplication
     */
    public function setIrhpPermitRequests($irhpPermitRequests)
    {
        $this->irhpPermitRequests = $irhpPermitRequests;

        return $this;
    }

    /**
     * Get the irhp permit requests
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitRequests()
    {
        return $this->irhpPermitRequests;
    }

    /**
     * Add a irhp permit requests
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitRequests collection being added
     *
     * @return IrhpApplication
     */
    public function addIrhpPermitRequests($irhpPermitRequests)
    {
        if ($irhpPermitRequests instanceof ArrayCollection) {
            $this->irhpPermitRequests = new ArrayCollection(
                array_merge(
                    $this->irhpPermitRequests->toArray(),
                    $irhpPermitRequests->toArray()
                )
            );
        } elseif (!$this->irhpPermitRequests->contains($irhpPermitRequests)) {
            $this->irhpPermitRequests->add($irhpPermitRequests);
        }

        return $this;
    }

    /**
     * Remove a irhp permit requests
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRequests collection being removed
     *
     * @return IrhpApplication
     */
    public function removeIrhpPermitRequests($irhpPermitRequests)
    {
        if ($this->irhpPermitRequests->contains($irhpPermitRequests)) {
            $this->irhpPermitRequests->removeElement($irhpPermitRequests);
        }

        return $this;
    }

    /**
     * Set the notes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $notes collection being set as the value
     *
     * @return IrhpApplication
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get the notes
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Add a notes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $notes collection being added
     *
     * @return IrhpApplication
     */
    public function addNotes($notes)
    {
        if ($notes instanceof ArrayCollection) {
            $this->notes = new ArrayCollection(
                array_merge(
                    $this->notes->toArray(),
                    $notes->toArray()
                )
            );
        } elseif (!$this->notes->contains($notes)) {
            $this->notes->add($notes);
        }

        return $this;
    }

    /**
     * Remove a notes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $notes collection being removed
     *
     * @return IrhpApplication
     */
    public function removeNotes($notes)
    {
        if ($this->notes->contains($notes)) {
            $this->notes->removeElement($notes);
        }

        return $this;
    }

    /**
     * Set the tasks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tasks collection being set as the value
     *
     * @return IrhpApplication
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * Get the tasks
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Add a tasks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $tasks collection being added
     *
     * @return IrhpApplication
     */
    public function addTasks($tasks)
    {
        if ($tasks instanceof ArrayCollection) {
            $this->tasks = new ArrayCollection(
                array_merge(
                    $this->tasks->toArray(),
                    $tasks->toArray()
                )
            );
        } elseif (!$this->tasks->contains($tasks)) {
            $this->tasks->add($tasks);
        }

        return $this;
    }

    /**
     * Remove a tasks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tasks collection being removed
     *
     * @return IrhpApplication
     */
    public function removeTasks($tasks)
    {
        if ($this->tasks->contains($tasks)) {
            $this->tasks->removeElement($tasks);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
