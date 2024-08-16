<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

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
 * Cases Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="cases",
 *    indexes={
 *        @ORM\Index(name="ix_cases_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_cases_case_type", columns={"case_type"}),
 *        @ORM\Index(name="ix_cases_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_cases_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_cases_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_cases_olbs_key_olbs_type", columns={"olbs_key","olbs_type"}),
 *        @ORM\Index(name="ix_cases_transport_manager_id", columns={"transport_manager_id"})
 *    }
 * )
 */
abstract class AbstractCases implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Annual test history
     *
     * @var string
     *
     * @ORM\Column(type="string", name="annual_test_history", length=4000, nullable=true)
     */
    protected $annualTestHistory;

    /**
     * Application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\Application",
     *     fetch="LAZY",
     *     inversedBy="cases"
     * )
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=true)
     */
    protected $application;

    /**
     * Case type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="case_type", referencedColumnName="id", nullable=false)
     */
    protected $caseType;

    /**
     * Category
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\System\RefData",
     *     inversedBy="cases",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="case_category",
     *     joinColumns={
     *         @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $categorys;

    /**
     * Closed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="closed_date", nullable=true)
     */
    protected $closedDate;

    /**
     * Conviction note
     *
     * @var string
     *
     * @ORM\Column(type="string", name="conviction_note", length=4000, nullable=true)
     */
    protected $convictionNote;

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
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=1024, nullable=true)
     */
    protected $description;

    /**
     * Ecms no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="ecms_no", length=45, nullable=true)
     */
    protected $ecmsNo;

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
     * Is impounding
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_impounding", nullable=false, options={"default": 0})
     */
    protected $isImpounding = 0;

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
     * Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence",
     *     fetch="LAZY",
     *     inversedBy="cases"
     * )
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=true)
     */
    protected $licence;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Olbs type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;

    /**
     * Open date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="open_date", nullable=false)
     */
    protected $openDate;

    /**
     * Outcome
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\System\RefData",
     *     inversedBy="casess",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="case_outcome",
     *     joinColumns={
     *         @ORM\JoinColumn(name="cases_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="outcome_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $outcomes;

    /**
     * Penalties note
     *
     * @var string
     *
     * @ORM\Column(type="string", name="penalties_note", length=4000, nullable=true)
     */
    protected $penaltiesNote;

    /**
     * Prohibition note
     *
     * @var string
     *
     * @ORM\Column(type="string", name="prohibition_note", length=4000, nullable=true)
     */
    protected $prohibitionNote;

    /**
     * Transport manager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager",
     *     fetch="LAZY",
     *     inversedBy="cases"
     * )
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id", nullable=true)
     */
    protected $transportManager;

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
     * Appeal
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Appeal
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Appeal", mappedBy="case")
     */
    protected $appeal;

    /**
     * Read audit
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\CasesReadAudit", mappedBy="case")
     */
    protected $readAudits;

    /**
     * Complaint
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Complaint", mappedBy="case")
     */
    protected $complaints;

    /**
     * Condition undertaking
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking", mappedBy="case")
     */
    protected $conditionUndertakings;

    /**
     * Conviction
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Conviction", mappedBy="case")
     */
    protected $convictions;

    /**
     * Document
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", mappedBy="case")
     */
    protected $documents;

    /**
     * Erru request
     *
     * @var \Dvsa\Olcs\Api\Entity\Si\ErruRequest
     *
     * @ORM\OneToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Si\ErruRequest",
     *     mappedBy="case",
     *     cascade={"persist"}
     * )
     */
    protected $erruRequest;

    /**
     * Legacy offence
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Legacy\LegacyOffence", mappedBy="case")
     */
    protected $legacyOffences;

    /**
     * Opposition
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Opposition\Opposition", mappedBy="case")
     */
    protected $oppositions;

    /**
     * Public inquiry
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\Pi
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\Pi", mappedBy="case")
     */
    protected $publicInquiry;

    /**
     * Prohibition
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Prohibition\Prohibition", mappedBy="case")
     */
    protected $prohibitions;

    /**
     * Serious infringement
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Si\SeriousInfringement",
     *     mappedBy="case",
     *     cascade={"persist"}
     * )
     */
    protected $seriousInfringements;

    /**
     * Statement
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Statement", mappedBy="case")
     */
    protected $statements;

    /**
     * Stay
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Stay", mappedBy="case")
     */
    protected $stays;

    /**
     * Tm decision
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision", mappedBy="case")
     */
    protected $tmDecisions;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->categorys = new ArrayCollection();
        $this->outcomes = new ArrayCollection();
        $this->readAudits = new ArrayCollection();
        $this->complaints = new ArrayCollection();
        $this->conditionUndertakings = new ArrayCollection();
        $this->convictions = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->legacyOffences = new ArrayCollection();
        $this->oppositions = new ArrayCollection();
        $this->prohibitions = new ArrayCollection();
        $this->seriousInfringements = new ArrayCollection();
        $this->statements = new ArrayCollection();
        $this->stays = new ArrayCollection();
        $this->tmDecisions = new ArrayCollection();
    }

    /**
     * Set the annual test history
     *
     * @param string $annualTestHistory new value being set
     *
     * @return Cases
     */
    public function setAnnualTestHistory($annualTestHistory)
    {
        $this->annualTestHistory = $annualTestHistory;

        return $this;
    }

    /**
     * Get the annual test history
     *
     * @return string
     */
    public function getAnnualTestHistory()
    {
        return $this->annualTestHistory;
    }

    /**
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application entity being set as the value
     *
     * @return Cases
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get the application
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the case type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $caseType entity being set as the value
     *
     * @return Cases
     */
    public function setCaseType($caseType)
    {
        $this->caseType = $caseType;

        return $this;
    }

    /**
     * Get the case type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getCaseType()
    {
        return $this->caseType;
    }

    /**
     * Set the category
     *
     * @param ArrayCollection $categorys collection being set as the value
     *
     * @return Cases
     */
    public function setCategorys($categorys)
    {
        $this->categorys = $categorys;

        return $this;
    }

    /**
     * Get the categorys
     *
     * @return ArrayCollection
     */
    public function getCategorys()
    {
        return $this->categorys;
    }

    /**
     * Add a categorys
     *
     * @param ArrayCollection|mixed $categorys collection being added
     *
     * @return Cases
     */
    public function addCategorys($categorys)
    {
        if ($categorys instanceof ArrayCollection) {
            $this->categorys = new ArrayCollection(
                array_merge(
                    $this->categorys->toArray(),
                    $categorys->toArray()
                )
            );
        } elseif (!$this->categorys->contains($categorys)) {
            $this->categorys->add($categorys);
        }

        return $this;
    }

    /**
     * Remove a categorys
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $categorys collection being removed
     *
     * @return Cases
     */
    public function removeCategorys($categorys)
    {
        if ($this->categorys->contains($categorys)) {
            $this->categorys->removeElement($categorys);
        }

        return $this;
    }

    /**
     * Set the closed date
     *
     * @param \DateTime $closedDate new value being set
     *
     * @return Cases
     */
    public function setClosedDate($closedDate)
    {
        $this->closedDate = $closedDate;

        return $this;
    }

    /**
     * Get the closed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getClosedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->closedDate);
        }

        return $this->closedDate;
    }

    /**
     * Set the conviction note
     *
     * @param string $convictionNote new value being set
     *
     * @return Cases
     */
    public function setConvictionNote($convictionNote)
    {
        $this->convictionNote = $convictionNote;

        return $this;
    }

    /**
     * Get the conviction note
     *
     * @return string
     */
    public function getConvictionNote()
    {
        return $this->convictionNote;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Cases
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
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return Cases
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
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
     * Set the ecms no
     *
     * @param string $ecmsNo new value being set
     *
     * @return Cases
     */
    public function setEcmsNo($ecmsNo)
    {
        $this->ecmsNo = $ecmsNo;

        return $this;
    }

    /**
     * Get the ecms no
     *
     * @return string
     */
    public function getEcmsNo()
    {
        return $this->ecmsNo;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Cases
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
     * Set the is impounding
     *
     * @param string $isImpounding new value being set
     *
     * @return Cases
     */
    public function setIsImpounding($isImpounding)
    {
        $this->isImpounding = $isImpounding;

        return $this;
    }

    /**
     * Get the is impounding
     *
     * @return string
     */
    public function getIsImpounding()
    {
        return $this->isImpounding;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return Cases
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
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence entity being set as the value
     *
     * @return Cases
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return Cases
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int
     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return Cases
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string
     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Set the open date
     *
     * @param \DateTime $openDate new value being set
     *
     * @return Cases
     */
    public function setOpenDate($openDate)
    {
        $this->openDate = $openDate;

        return $this;
    }

    /**
     * Get the open date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getOpenDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->openDate);
        }

        return $this->openDate;
    }

    /**
     * Set the outcome
     *
     * @param ArrayCollection $outcomes collection being set as the value
     *
     * @return Cases
     */
    public function setOutcomes($outcomes)
    {
        $this->outcomes = $outcomes;

        return $this;
    }

    /**
     * Get the outcomes
     *
     * @return ArrayCollection
     */
    public function getOutcomes()
    {
        return $this->outcomes;
    }

    /**
     * Add a outcomes
     *
     * @param ArrayCollection|mixed $outcomes collection being added
     *
     * @return Cases
     */
    public function addOutcomes($outcomes)
    {
        if ($outcomes instanceof ArrayCollection) {
            $this->outcomes = new ArrayCollection(
                array_merge(
                    $this->outcomes->toArray(),
                    $outcomes->toArray()
                )
            );
        } elseif (!$this->outcomes->contains($outcomes)) {
            $this->outcomes->add($outcomes);
        }

        return $this;
    }

    /**
     * Remove a outcomes
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $outcomes collection being removed
     *
     * @return Cases
     */
    public function removeOutcomes($outcomes)
    {
        if ($this->outcomes->contains($outcomes)) {
            $this->outcomes->removeElement($outcomes);
        }

        return $this;
    }

    /**
     * Set the penalties note
     *
     * @param string $penaltiesNote new value being set
     *
     * @return Cases
     */
    public function setPenaltiesNote($penaltiesNote)
    {
        $this->penaltiesNote = $penaltiesNote;

        return $this;
    }

    /**
     * Get the penalties note
     *
     * @return string
     */
    public function getPenaltiesNote()
    {
        return $this->penaltiesNote;
    }

    /**
     * Set the prohibition note
     *
     * @param string $prohibitionNote new value being set
     *
     * @return Cases
     */
    public function setProhibitionNote($prohibitionNote)
    {
        $this->prohibitionNote = $prohibitionNote;

        return $this;
    }

    /**
     * Get the prohibition note
     *
     * @return string
     */
    public function getProhibitionNote()
    {
        return $this->prohibitionNote;
    }

    /**
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager entity being set as the value
     *
     * @return Cases
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;

        return $this;
    }

    /**
     * Get the transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Cases
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
     * Set the appeal
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Appeal $appeal entity being set as the value
     *
     * @return Cases
     */
    public function setAppeal($appeal)
    {
        $this->appeal = $appeal;

        return $this;
    }

    /**
     * Get the appeal
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Appeal
     */
    public function getAppeal()
    {
        return $this->appeal;
    }

    /**
     * Set the read audit
     *
     * @param ArrayCollection $readAudits collection being set as the value
     *
     * @return Cases
     */
    public function setReadAudits($readAudits)
    {
        $this->readAudits = $readAudits;

        return $this;
    }

    /**
     * Get the read audits
     *
     * @return ArrayCollection
     */
    public function getReadAudits()
    {
        return $this->readAudits;
    }

    /**
     * Add a read audits
     *
     * @param ArrayCollection|mixed $readAudits collection being added
     *
     * @return Cases
     */
    public function addReadAudits($readAudits)
    {
        if ($readAudits instanceof ArrayCollection) {
            $this->readAudits = new ArrayCollection(
                array_merge(
                    $this->readAudits->toArray(),
                    $readAudits->toArray()
                )
            );
        } elseif (!$this->readAudits->contains($readAudits)) {
            $this->readAudits->add($readAudits);
        }

        return $this;
    }

    /**
     * Remove a read audits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $readAudits collection being removed
     *
     * @return Cases
     */
    public function removeReadAudits($readAudits)
    {
        if ($this->readAudits->contains($readAudits)) {
            $this->readAudits->removeElement($readAudits);
        }

        return $this;
    }

    /**
     * Set the complaint
     *
     * @param ArrayCollection $complaints collection being set as the value
     *
     * @return Cases
     */
    public function setComplaints($complaints)
    {
        $this->complaints = $complaints;

        return $this;
    }

    /**
     * Get the complaints
     *
     * @return ArrayCollection
     */
    public function getComplaints()
    {
        return $this->complaints;
    }

    /**
     * Add a complaints
     *
     * @param ArrayCollection|mixed $complaints collection being added
     *
     * @return Cases
     */
    public function addComplaints($complaints)
    {
        if ($complaints instanceof ArrayCollection) {
            $this->complaints = new ArrayCollection(
                array_merge(
                    $this->complaints->toArray(),
                    $complaints->toArray()
                )
            );
        } elseif (!$this->complaints->contains($complaints)) {
            $this->complaints->add($complaints);
        }

        return $this;
    }

    /**
     * Remove a complaints
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $complaints collection being removed
     *
     * @return Cases
     */
    public function removeComplaints($complaints)
    {
        if ($this->complaints->contains($complaints)) {
            $this->complaints->removeElement($complaints);
        }

        return $this;
    }

    /**
     * Set the condition undertaking
     *
     * @param ArrayCollection $conditionUndertakings collection being set as the value
     *
     * @return Cases
     */
    public function setConditionUndertakings($conditionUndertakings)
    {
        $this->conditionUndertakings = $conditionUndertakings;

        return $this;
    }

    /**
     * Get the condition undertakings
     *
     * @return ArrayCollection
     */
    public function getConditionUndertakings()
    {
        return $this->conditionUndertakings;
    }

    /**
     * Add a condition undertakings
     *
     * @param ArrayCollection|mixed $conditionUndertakings collection being added
     *
     * @return Cases
     */
    public function addConditionUndertakings($conditionUndertakings)
    {
        if ($conditionUndertakings instanceof ArrayCollection) {
            $this->conditionUndertakings = new ArrayCollection(
                array_merge(
                    $this->conditionUndertakings->toArray(),
                    $conditionUndertakings->toArray()
                )
            );
        } elseif (!$this->conditionUndertakings->contains($conditionUndertakings)) {
            $this->conditionUndertakings->add($conditionUndertakings);
        }

        return $this;
    }

    /**
     * Remove a condition undertakings
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $conditionUndertakings collection being removed
     *
     * @return Cases
     */
    public function removeConditionUndertakings($conditionUndertakings)
    {
        if ($this->conditionUndertakings->contains($conditionUndertakings)) {
            $this->conditionUndertakings->removeElement($conditionUndertakings);
        }

        return $this;
    }

    /**
     * Set the conviction
     *
     * @param ArrayCollection $convictions collection being set as the value
     *
     * @return Cases
     */
    public function setConvictions($convictions)
    {
        $this->convictions = $convictions;

        return $this;
    }

    /**
     * Get the convictions
     *
     * @return ArrayCollection
     */
    public function getConvictions()
    {
        return $this->convictions;
    }

    /**
     * Add a convictions
     *
     * @param ArrayCollection|mixed $convictions collection being added
     *
     * @return Cases
     */
    public function addConvictions($convictions)
    {
        if ($convictions instanceof ArrayCollection) {
            $this->convictions = new ArrayCollection(
                array_merge(
                    $this->convictions->toArray(),
                    $convictions->toArray()
                )
            );
        } elseif (!$this->convictions->contains($convictions)) {
            $this->convictions->add($convictions);
        }

        return $this;
    }

    /**
     * Remove a convictions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $convictions collection being removed
     *
     * @return Cases
     */
    public function removeConvictions($convictions)
    {
        if ($this->convictions->contains($convictions)) {
            $this->convictions->removeElement($convictions);
        }

        return $this;
    }

    /**
     * Set the document
     *
     * @param ArrayCollection $documents collection being set as the value
     *
     * @return Cases
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get the documents
     *
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add a documents
     *
     * @param ArrayCollection|mixed $documents collection being added
     *
     * @return Cases
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
     * @return Cases
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Set the erru request
     *
     * @param \Dvsa\Olcs\Api\Entity\Si\ErruRequest $erruRequest entity being set as the value
     *
     * @return Cases
     */
    public function setErruRequest($erruRequest)
    {
        $this->erruRequest = $erruRequest;

        return $this;
    }

    /**
     * Get the erru request
     *
     * @return \Dvsa\Olcs\Api\Entity\Si\ErruRequest
     */
    public function getErruRequest()
    {
        return $this->erruRequest;
    }

    /**
     * Set the legacy offence
     *
     * @param ArrayCollection $legacyOffences collection being set as the value
     *
     * @return Cases
     */
    public function setLegacyOffences($legacyOffences)
    {
        $this->legacyOffences = $legacyOffences;

        return $this;
    }

    /**
     * Get the legacy offences
     *
     * @return ArrayCollection
     */
    public function getLegacyOffences()
    {
        return $this->legacyOffences;
    }

    /**
     * Add a legacy offences
     *
     * @param ArrayCollection|mixed $legacyOffences collection being added
     *
     * @return Cases
     */
    public function addLegacyOffences($legacyOffences)
    {
        if ($legacyOffences instanceof ArrayCollection) {
            $this->legacyOffences = new ArrayCollection(
                array_merge(
                    $this->legacyOffences->toArray(),
                    $legacyOffences->toArray()
                )
            );
        } elseif (!$this->legacyOffences->contains($legacyOffences)) {
            $this->legacyOffences->add($legacyOffences);
        }

        return $this;
    }

    /**
     * Remove a legacy offences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $legacyOffences collection being removed
     *
     * @return Cases
     */
    public function removeLegacyOffences($legacyOffences)
    {
        if ($this->legacyOffences->contains($legacyOffences)) {
            $this->legacyOffences->removeElement($legacyOffences);
        }

        return $this;
    }

    /**
     * Set the opposition
     *
     * @param ArrayCollection $oppositions collection being set as the value
     *
     * @return Cases
     */
    public function setOppositions($oppositions)
    {
        $this->oppositions = $oppositions;

        return $this;
    }

    /**
     * Get the oppositions
     *
     * @return ArrayCollection
     */
    public function getOppositions()
    {
        return $this->oppositions;
    }

    /**
     * Add a oppositions
     *
     * @param ArrayCollection|mixed $oppositions collection being added
     *
     * @return Cases
     */
    public function addOppositions($oppositions)
    {
        if ($oppositions instanceof ArrayCollection) {
            $this->oppositions = new ArrayCollection(
                array_merge(
                    $this->oppositions->toArray(),
                    $oppositions->toArray()
                )
            );
        } elseif (!$this->oppositions->contains($oppositions)) {
            $this->oppositions->add($oppositions);
        }

        return $this;
    }

    /**
     * Remove a oppositions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $oppositions collection being removed
     *
     * @return Cases
     */
    public function removeOppositions($oppositions)
    {
        if ($this->oppositions->contains($oppositions)) {
            $this->oppositions->removeElement($oppositions);
        }

        return $this;
    }

    /**
     * Set the public inquiry
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\Pi $publicInquiry entity being set as the value
     *
     * @return Cases
     */
    public function setPublicInquiry($publicInquiry)
    {
        $this->publicInquiry = $publicInquiry;

        return $this;
    }

    /**
     * Get the public inquiry
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\Pi
     */
    public function getPublicInquiry()
    {
        return $this->publicInquiry;
    }

    /**
     * Set the prohibition
     *
     * @param ArrayCollection $prohibitions collection being set as the value
     *
     * @return Cases
     */
    public function setProhibitions($prohibitions)
    {
        $this->prohibitions = $prohibitions;

        return $this;
    }

    /**
     * Get the prohibitions
     *
     * @return ArrayCollection
     */
    public function getProhibitions()
    {
        return $this->prohibitions;
    }

    /**
     * Add a prohibitions
     *
     * @param ArrayCollection|mixed $prohibitions collection being added
     *
     * @return Cases
     */
    public function addProhibitions($prohibitions)
    {
        if ($prohibitions instanceof ArrayCollection) {
            $this->prohibitions = new ArrayCollection(
                array_merge(
                    $this->prohibitions->toArray(),
                    $prohibitions->toArray()
                )
            );
        } elseif (!$this->prohibitions->contains($prohibitions)) {
            $this->prohibitions->add($prohibitions);
        }

        return $this;
    }

    /**
     * Remove a prohibitions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $prohibitions collection being removed
     *
     * @return Cases
     */
    public function removeProhibitions($prohibitions)
    {
        if ($this->prohibitions->contains($prohibitions)) {
            $this->prohibitions->removeElement($prohibitions);
        }

        return $this;
    }

    /**
     * Set the serious infringement
     *
     * @param ArrayCollection $seriousInfringements collection being set as the value
     *
     * @return Cases
     */
    public function setSeriousInfringements($seriousInfringements)
    {
        $this->seriousInfringements = $seriousInfringements;

        return $this;
    }

    /**
     * Get the serious infringements
     *
     * @return ArrayCollection
     */
    public function getSeriousInfringements()
    {
        return $this->seriousInfringements;
    }

    /**
     * Add a serious infringements
     *
     * @param ArrayCollection|mixed $seriousInfringements collection being added
     *
     * @return Cases
     */
    public function addSeriousInfringements($seriousInfringements)
    {
        if ($seriousInfringements instanceof ArrayCollection) {
            $this->seriousInfringements = new ArrayCollection(
                array_merge(
                    $this->seriousInfringements->toArray(),
                    $seriousInfringements->toArray()
                )
            );
        } elseif (!$this->seriousInfringements->contains($seriousInfringements)) {
            $this->seriousInfringements->add($seriousInfringements);
        }

        return $this;
    }

    /**
     * Remove a serious infringements
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $seriousInfringements collection being removed
     *
     * @return Cases
     */
    public function removeSeriousInfringements($seriousInfringements)
    {
        if ($this->seriousInfringements->contains($seriousInfringements)) {
            $this->seriousInfringements->removeElement($seriousInfringements);
        }

        return $this;
    }

    /**
     * Set the statement
     *
     * @param ArrayCollection $statements collection being set as the value
     *
     * @return Cases
     */
    public function setStatements($statements)
    {
        $this->statements = $statements;

        return $this;
    }

    /**
     * Get the statements
     *
     * @return ArrayCollection
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * Add a statements
     *
     * @param ArrayCollection|mixed $statements collection being added
     *
     * @return Cases
     */
    public function addStatements($statements)
    {
        if ($statements instanceof ArrayCollection) {
            $this->statements = new ArrayCollection(
                array_merge(
                    $this->statements->toArray(),
                    $statements->toArray()
                )
            );
        } elseif (!$this->statements->contains($statements)) {
            $this->statements->add($statements);
        }

        return $this;
    }

    /**
     * Remove a statements
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $statements collection being removed
     *
     * @return Cases
     */
    public function removeStatements($statements)
    {
        if ($this->statements->contains($statements)) {
            $this->statements->removeElement($statements);
        }

        return $this;
    }

    /**
     * Set the stay
     *
     * @param ArrayCollection $stays collection being set as the value
     *
     * @return Cases
     */
    public function setStays($stays)
    {
        $this->stays = $stays;

        return $this;
    }

    /**
     * Get the stays
     *
     * @return ArrayCollection
     */
    public function getStays()
    {
        return $this->stays;
    }

    /**
     * Add a stays
     *
     * @param ArrayCollection|mixed $stays collection being added
     *
     * @return Cases
     */
    public function addStays($stays)
    {
        if ($stays instanceof ArrayCollection) {
            $this->stays = new ArrayCollection(
                array_merge(
                    $this->stays->toArray(),
                    $stays->toArray()
                )
            );
        } elseif (!$this->stays->contains($stays)) {
            $this->stays->add($stays);
        }

        return $this;
    }

    /**
     * Remove a stays
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $stays collection being removed
     *
     * @return Cases
     */
    public function removeStays($stays)
    {
        if ($this->stays->contains($stays)) {
            $this->stays->removeElement($stays);
        }

        return $this;
    }

    /**
     * Set the tm decision
     *
     * @param ArrayCollection $tmDecisions collection being set as the value
     *
     * @return Cases
     */
    public function setTmDecisions($tmDecisions)
    {
        $this->tmDecisions = $tmDecisions;

        return $this;
    }

    /**
     * Get the tm decisions
     *
     * @return ArrayCollection
     */
    public function getTmDecisions()
    {
        return $this->tmDecisions;
    }

    /**
     * Add a tm decisions
     *
     * @param ArrayCollection|mixed $tmDecisions collection being added
     *
     * @return Cases
     */
    public function addTmDecisions($tmDecisions)
    {
        if ($tmDecisions instanceof ArrayCollection) {
            $this->tmDecisions = new ArrayCollection(
                array_merge(
                    $this->tmDecisions->toArray(),
                    $tmDecisions->toArray()
                )
            );
        } elseif (!$this->tmDecisions->contains($tmDecisions)) {
            $this->tmDecisions->add($tmDecisions);
        }

        return $this;
    }

    /**
     * Remove a tm decisions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tmDecisions collection being removed
     *
     * @return Cases
     */
    public function removeTmDecisions($tmDecisions)
    {
        if ($this->tmDecisions->contains($tmDecisions)) {
            $this->tmDecisions->removeElement($tmDecisions);
        }

        return $this;
    }
}
