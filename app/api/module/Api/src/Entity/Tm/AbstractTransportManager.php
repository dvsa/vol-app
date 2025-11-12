<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Tm;

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
 * AbstractTransportManager Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="transport_manager",
 *    indexes={
 *        @ORM\Index(name="ix_transport_manager_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_transport_manager_home_cd_id", columns={"home_cd_id"}),
 *        @ORM\Index(name="ix_transport_manager_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_transport_manager_merge_to_transport_manager_id", columns={"merge_to_transport_manager_id"}),
 *        @ORM\Index(name="ix_transport_manager_tm_status", columns={"tm_status"}),
 *        @ORM\Index(name="ix_transport_manager_tm_type", columns={"tm_type"}),
 *        @ORM\Index(name="ix_transport_manager_work_cd_id", columns={"work_cd_id"})
 *    }
 * )
 */
abstract class AbstractTransportManager implements BundleSerializableInterface, JsonSerializable
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
     * Current,Disqualified,Active,Disabled
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_status", referencedColumnName="id")
     */
    protected $tmStatus;

    /**
     * Internal to organisation, external or both when multi orgs.
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_type", referencedColumnName="id", nullable=true)
     */
    protected $tmType;

    /**
     * Home contact details FK
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails", fetch="LAZY")
     * @ORM\JoinColumn(name="home_cd_id", referencedColumnName="id")
     */
    protected $homeCd;

    /**
     * Work contact details FK
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails", fetch="LAZY")
     * @ORM\JoinColumn(name="work_cd_id", referencedColumnName="id", nullable=true)
     */
    protected $workCd;

    /**
     * MergeToTransportManager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager", fetch="LAZY")
     * @ORM\JoinColumn(name="merge_to_transport_manager_id", referencedColumnName="id", nullable=true)
     */
    protected $mergeToTransportManager;

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
     * FK to case where TM became disqualified
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="disqualification_tm_case_id", nullable=true)
     */
    protected $disqualificationTmCaseId;

    /**
     * Removed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="removed_date", nullable=true)
     */
    protected $removedDate;

    /**
     * Last licence date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="last_licence_date", nullable=true)
     */
    protected $lastLicenceDate;

    /**
     * Family name recognised by nysiis - cross Europe name resolution
     *
     * @var string
     *
     * @ORM\Column(type="string", name="nysiis_family_name", length=100, nullable=true)
     */
    protected $nysiisFamilyName;

    /**
     * First name recognised by nysiis - cross Europe name resolution
     *
     * @var string
     *
     * @ORM\Column(type="string", name="nysiis_forename", length=100, nullable=true)
     */
    protected $nysiisForename;

    /**
     * Merge details
     *
     * @var array
     *
     * @ORM\Column(type="json", name="merge_details", nullable=true)
     */
    protected $mergeDetails;

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
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Cases
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", mappedBy="transportManager")
     */
    protected $cases;

    /**
     * Documents
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", mappedBy="transportManager")
     */
    protected $documents;

    /**
     * OtherLicences
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence", mappedBy="transportManager")
     */
    protected $otherLicences;

    /**
     * PreviousConvictions
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Application\PreviousConviction", mappedBy="transportManager")
     */
    protected $previousConvictions;

    /**
     * Employments
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TmEmployment", mappedBy="transportManager")
     */
    protected $employments;

    /**
     * Qualifications
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TmQualification", mappedBy="transportManager")
     */
    protected $qualifications;

    /**
     * TmApplications
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication", mappedBy="transportManager")
     */
    protected $tmApplications;

    /**
     * TmLicences
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence", mappedBy="transportManager")
     */
    protected $tmLicences;

    /**
     * ReadAudits
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManagerReadAudit", mappedBy="transportManager")
     */
    protected $readAudits;

    /**
     * Users
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\User\User", mappedBy="transportManager")
     */
    protected $users;

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
        $this->cases = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->otherLicences = new ArrayCollection();
        $this->previousConvictions = new ArrayCollection();
        $this->employments = new ArrayCollection();
        $this->qualifications = new ArrayCollection();
        $this->tmApplications = new ArrayCollection();
        $this->tmLicences = new ArrayCollection();
        $this->readAudits = new ArrayCollection();
        $this->users = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return TransportManager
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
     * Set the tm status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tmStatus new value being set
     *
     * @return TransportManager
     */
    public function setTmStatus($tmStatus)
    {
        $this->tmStatus = $tmStatus;

        return $this;
    }

    /**
     * Get the tm status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getTmStatus()
    {
        return $this->tmStatus;
    }

    /**
     * Set the tm type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tmType new value being set
     *
     * @return TransportManager
     */
    public function setTmType($tmType)
    {
        $this->tmType = $tmType;

        return $this;
    }

    /**
     * Get the tm type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getTmType()
    {
        return $this->tmType;
    }

    /**
     * Set the home cd
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $homeCd new value being set
     *
     * @return TransportManager
     */
    public function setHomeCd($homeCd)
    {
        $this->homeCd = $homeCd;

        return $this;
    }

    /**
     * Get the home cd
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails     */
    public function getHomeCd()
    {
        return $this->homeCd;
    }

    /**
     * Set the work cd
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $workCd new value being set
     *
     * @return TransportManager
     */
    public function setWorkCd($workCd)
    {
        $this->workCd = $workCd;

        return $this;
    }

    /**
     * Get the work cd
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails     */
    public function getWorkCd()
    {
        return $this->workCd;
    }

    /**
     * Set the merge to transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $mergeToTransportManager new value being set
     *
     * @return TransportManager
     */
    public function setMergeToTransportManager($mergeToTransportManager)
    {
        $this->mergeToTransportManager = $mergeToTransportManager;

        return $this;
    }

    /**
     * Get the merge to transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager     */
    public function getMergeToTransportManager()
    {
        return $this->mergeToTransportManager;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return TransportManager
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
     * @return TransportManager
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
     * Set the disqualification tm case id
     *
     * @param int $disqualificationTmCaseId new value being set
     *
     * @return TransportManager
     */
    public function setDisqualificationTmCaseId($disqualificationTmCaseId)
    {
        $this->disqualificationTmCaseId = $disqualificationTmCaseId;

        return $this;
    }

    /**
     * Get the disqualification tm case id
     *
     * @return int     */
    public function getDisqualificationTmCaseId()
    {
        return $this->disqualificationTmCaseId;
    }

    /**
     * Set the removed date
     *
     * @param \DateTime $removedDate new value being set
     *
     * @return TransportManager
     */
    public function setRemovedDate($removedDate)
    {
        $this->removedDate = $removedDate;

        return $this;
    }

    /**
     * Get the removed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getRemovedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->removedDate);
        }

        return $this->removedDate;
    }

    /**
     * Set the last licence date
     *
     * @param \DateTime $lastLicenceDate new value being set
     *
     * @return TransportManager
     */
    public function setLastLicenceDate($lastLicenceDate)
    {
        $this->lastLicenceDate = $lastLicenceDate;

        return $this;
    }

    /**
     * Get the last licence date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getLastLicenceDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastLicenceDate);
        }

        return $this->lastLicenceDate;
    }

    /**
     * Set the nysiis family name
     *
     * @param string $nysiisFamilyName new value being set
     *
     * @return TransportManager
     */
    public function setNysiisFamilyName($nysiisFamilyName)
    {
        $this->nysiisFamilyName = $nysiisFamilyName;

        return $this;
    }

    /**
     * Get the nysiis family name
     *
     * @return string     */
    public function getNysiisFamilyName()
    {
        return $this->nysiisFamilyName;
    }

    /**
     * Set the nysiis forename
     *
     * @param string $nysiisForename new value being set
     *
     * @return TransportManager
     */
    public function setNysiisForename($nysiisForename)
    {
        $this->nysiisForename = $nysiisForename;

        return $this;
    }

    /**
     * Get the nysiis forename
     *
     * @return string     */
    public function getNysiisForename()
    {
        return $this->nysiisForename;
    }

    /**
     * Set the merge details
     *
     * @param array $mergeDetails new value being set
     *
     * @return TransportManager
     */
    public function setMergeDetails($mergeDetails)
    {
        $this->mergeDetails = $mergeDetails;

        return $this;
    }

    /**
     * Get the merge details
     *
     * @return array     */
    public function getMergeDetails()
    {
        return $this->mergeDetails;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return TransportManager
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
     * @return TransportManager
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
     * Set the cases
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $cases collection being set as the value
     *
     * @return TransportManager
     */
    public function setCases($cases)
    {
        $this->cases = $cases;

        return $this;
    }

    /**
     * Get the cases
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCases()
    {
        return $this->cases;
    }

    /**
     * Add a cases
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $cases collection being added
     *
     * @return TransportManager
     */
    public function addCases($cases)
    {
        if ($cases instanceof ArrayCollection) {
            $this->cases = new ArrayCollection(
                array_merge(
                    $this->cases->toArray(),
                    $cases->toArray()
                )
            );
        } elseif (!$this->cases->contains($cases)) {
            $this->cases->add($cases);
        }

        return $this;
    }

    /**
     * Remove a cases
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $cases collection being removed
     *
     * @return TransportManager
     */
    public function removeCases($cases)
    {
        if ($this->cases->contains($cases)) {
            $this->cases->removeElement($cases);
        }

        return $this;
    }

    /**
     * Set the documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being set as the value
     *
     * @return TransportManager
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
     * @return TransportManager
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
     * @return TransportManager
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Set the other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $otherLicences collection being set as the value
     *
     * @return TransportManager
     */
    public function setOtherLicences($otherLicences)
    {
        $this->otherLicences = $otherLicences;

        return $this;
    }

    /**
     * Get the other licences
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOtherLicences()
    {
        return $this->otherLicences;
    }

    /**
     * Add a other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $otherLicences collection being added
     *
     * @return TransportManager
     */
    public function addOtherLicences($otherLicences)
    {
        if ($otherLicences instanceof ArrayCollection) {
            $this->otherLicences = new ArrayCollection(
                array_merge(
                    $this->otherLicences->toArray(),
                    $otherLicences->toArray()
                )
            );
        } elseif (!$this->otherLicences->contains($otherLicences)) {
            $this->otherLicences->add($otherLicences);
        }

        return $this;
    }

    /**
     * Remove a other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $otherLicences collection being removed
     *
     * @return TransportManager
     */
    public function removeOtherLicences($otherLicences)
    {
        if ($this->otherLicences->contains($otherLicences)) {
            $this->otherLicences->removeElement($otherLicences);
        }

        return $this;
    }

    /**
     * Set the previous convictions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $previousConvictions collection being set as the value
     *
     * @return TransportManager
     */
    public function setPreviousConvictions($previousConvictions)
    {
        $this->previousConvictions = $previousConvictions;

        return $this;
    }

    /**
     * Get the previous convictions
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPreviousConvictions()
    {
        return $this->previousConvictions;
    }

    /**
     * Add a previous convictions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $previousConvictions collection being added
     *
     * @return TransportManager
     */
    public function addPreviousConvictions($previousConvictions)
    {
        if ($previousConvictions instanceof ArrayCollection) {
            $this->previousConvictions = new ArrayCollection(
                array_merge(
                    $this->previousConvictions->toArray(),
                    $previousConvictions->toArray()
                )
            );
        } elseif (!$this->previousConvictions->contains($previousConvictions)) {
            $this->previousConvictions->add($previousConvictions);
        }

        return $this;
    }

    /**
     * Remove a previous convictions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $previousConvictions collection being removed
     *
     * @return TransportManager
     */
    public function removePreviousConvictions($previousConvictions)
    {
        if ($this->previousConvictions->contains($previousConvictions)) {
            $this->previousConvictions->removeElement($previousConvictions);
        }

        return $this;
    }

    /**
     * Set the employments
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $employments collection being set as the value
     *
     * @return TransportManager
     */
    public function setEmployments($employments)
    {
        $this->employments = $employments;

        return $this;
    }

    /**
     * Get the employments
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEmployments()
    {
        return $this->employments;
    }

    /**
     * Add a employments
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $employments collection being added
     *
     * @return TransportManager
     */
    public function addEmployments($employments)
    {
        if ($employments instanceof ArrayCollection) {
            $this->employments = new ArrayCollection(
                array_merge(
                    $this->employments->toArray(),
                    $employments->toArray()
                )
            );
        } elseif (!$this->employments->contains($employments)) {
            $this->employments->add($employments);
        }

        return $this;
    }

    /**
     * Remove a employments
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $employments collection being removed
     *
     * @return TransportManager
     */
    public function removeEmployments($employments)
    {
        if ($this->employments->contains($employments)) {
            $this->employments->removeElement($employments);
        }

        return $this;
    }

    /**
     * Set the qualifications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $qualifications collection being set as the value
     *
     * @return TransportManager
     */
    public function setQualifications($qualifications)
    {
        $this->qualifications = $qualifications;

        return $this;
    }

    /**
     * Get the qualifications
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getQualifications()
    {
        return $this->qualifications;
    }

    /**
     * Add a qualifications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $qualifications collection being added
     *
     * @return TransportManager
     */
    public function addQualifications($qualifications)
    {
        if ($qualifications instanceof ArrayCollection) {
            $this->qualifications = new ArrayCollection(
                array_merge(
                    $this->qualifications->toArray(),
                    $qualifications->toArray()
                )
            );
        } elseif (!$this->qualifications->contains($qualifications)) {
            $this->qualifications->add($qualifications);
        }

        return $this;
    }

    /**
     * Remove a qualifications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $qualifications collection being removed
     *
     * @return TransportManager
     */
    public function removeQualifications($qualifications)
    {
        if ($this->qualifications->contains($qualifications)) {
            $this->qualifications->removeElement($qualifications);
        }

        return $this;
    }

    /**
     * Set the tm applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tmApplications collection being set as the value
     *
     * @return TransportManager
     */
    public function setTmApplications($tmApplications)
    {
        $this->tmApplications = $tmApplications;

        return $this;
    }

    /**
     * Get the tm applications
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTmApplications()
    {
        return $this->tmApplications;
    }

    /**
     * Add a tm applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $tmApplications collection being added
     *
     * @return TransportManager
     */
    public function addTmApplications($tmApplications)
    {
        if ($tmApplications instanceof ArrayCollection) {
            $this->tmApplications = new ArrayCollection(
                array_merge(
                    $this->tmApplications->toArray(),
                    $tmApplications->toArray()
                )
            );
        } elseif (!$this->tmApplications->contains($tmApplications)) {
            $this->tmApplications->add($tmApplications);
        }

        return $this;
    }

    /**
     * Remove a tm applications
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tmApplications collection being removed
     *
     * @return TransportManager
     */
    public function removeTmApplications($tmApplications)
    {
        if ($this->tmApplications->contains($tmApplications)) {
            $this->tmApplications->removeElement($tmApplications);
        }

        return $this;
    }

    /**
     * Set the tm licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tmLicences collection being set as the value
     *
     * @return TransportManager
     */
    public function setTmLicences($tmLicences)
    {
        $this->tmLicences = $tmLicences;

        return $this;
    }

    /**
     * Get the tm licences
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTmLicences()
    {
        return $this->tmLicences;
    }

    /**
     * Add a tm licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $tmLicences collection being added
     *
     * @return TransportManager
     */
    public function addTmLicences($tmLicences)
    {
        if ($tmLicences instanceof ArrayCollection) {
            $this->tmLicences = new ArrayCollection(
                array_merge(
                    $this->tmLicences->toArray(),
                    $tmLicences->toArray()
                )
            );
        } elseif (!$this->tmLicences->contains($tmLicences)) {
            $this->tmLicences->add($tmLicences);
        }

        return $this;
    }

    /**
     * Remove a tm licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tmLicences collection being removed
     *
     * @return TransportManager
     */
    public function removeTmLicences($tmLicences)
    {
        if ($this->tmLicences->contains($tmLicences)) {
            $this->tmLicences->removeElement($tmLicences);
        }

        return $this;
    }

    /**
     * Set the read audits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $readAudits collection being set as the value
     *
     * @return TransportManager
     */
    public function setReadAudits($readAudits)
    {
        $this->readAudits = $readAudits;

        return $this;
    }

    /**
     * Get the read audits
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReadAudits()
    {
        return $this->readAudits;
    }

    /**
     * Add a read audits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $readAudits collection being added
     *
     * @return TransportManager
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
     * @return TransportManager
     */
    public function removeReadAudits($readAudits)
    {
        if ($this->readAudits->contains($readAudits)) {
            $this->readAudits->removeElement($readAudits);
        }

        return $this;
    }

    /**
     * Set the users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $users collection being set as the value
     *
     * @return TransportManager
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get the users
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add a users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $users collection being added
     *
     * @return TransportManager
     */
    public function addUsers($users)
    {
        if ($users instanceof ArrayCollection) {
            $this->users = new ArrayCollection(
                array_merge(
                    $this->users->toArray(),
                    $users->toArray()
                )
            );
        } elseif (!$this->users->contains($users)) {
            $this->users->add($users);
        }

        return $this;
    }

    /**
     * Remove a users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $users collection being removed
     *
     * @return TransportManager
     */
    public function removeUsers($users)
    {
        if ($this->users->contains($users)) {
            $this->users->removeElement($users);
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