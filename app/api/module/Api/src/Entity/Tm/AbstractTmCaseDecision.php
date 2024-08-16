<?php

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
 * TmCaseDecision Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="tm_case_decision",
 *    indexes={
 *        @ORM\Index(name="ix_tm_case_decision_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_tm_case_decision_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_tm_case_decision_decision", columns={"decision"}),
 *        @ORM\Index(name="ix_tm_case_decision_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_tm_case_decision_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractTmCaseDecision implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases",
     *     fetch="LAZY",
     *     inversedBy="tmDecisions"
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
     * Decision
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="decision", referencedColumnName="id", nullable=false)
     */
    protected $decision;

    /**
     * Decision date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="decision_date", nullable=true)
     */
    protected $decisionDate;

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
     * Is msi
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_msi", nullable=false, options={"default": 0})
     */
    protected $isMsi = 0;

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
     * No further action reason
     *
     * @var string
     *
     * @ORM\Column(type="string", name="no_further_action_reason", length=4000, nullable=true)
     */
    protected $noFurtherActionReason;

    /**
     * Notified date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="notified_date", nullable=true)
     */
    protected $notifiedDate;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Rehab measure
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\System\RefData",
     *     inversedBy="tmCaseDecisions",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="tm_case_decision_rehab",
     *     joinColumns={
     *         @ORM\JoinColumn(name="tm_case_decision_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="rehab_measure_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $rehabMeasures;

    /**
     * Repute not lost reason
     *
     * @var string
     *
     * @ORM\Column(type="string", name="repute_not_lost_reason", length=500, nullable=true)
     */
    protected $reputeNotLostReason;

    /**
     * Unfitness end date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="unfitness_end_date", nullable=true)
     */
    protected $unfitnessEndDate;

    /**
     * Unfitness reason
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\System\RefData",
     *     inversedBy="tmCaseDecisions",
     *     fetch="LAZY"
     * )
     * @ORM\JoinTable(name="tm_case_decision_unfitness",
     *     joinColumns={
     *         @ORM\JoinColumn(name="tm_case_decision_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="unfitness_reason_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $unfitnessReasons;

    /**
     * Unfitness start date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="unfitness_start_date", nullable=true)
     */
    protected $unfitnessStartDate;

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
        $this->rehabMeasures = new ArrayCollection();
        $this->unfitnessReasons = new ArrayCollection();
    }

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case entity being set as the value
     *
     * @return TmCaseDecision
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
     * @return TmCaseDecision
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
     * Set the decision
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $decision entity being set as the value
     *
     * @return TmCaseDecision
     */
    public function setDecision($decision)
    {
        $this->decision = $decision;

        return $this;
    }

    /**
     * Get the decision
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getDecision()
    {
        return $this->decision;
    }

    /**
     * Set the decision date
     *
     * @param \DateTime $decisionDate new value being set
     *
     * @return TmCaseDecision
     */
    public function setDecisionDate($decisionDate)
    {
        $this->decisionDate = $decisionDate;

        return $this;
    }

    /**
     * Get the decision date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getDecisionDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->decisionDate);
        }

        return $this->decisionDate;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return TmCaseDecision
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
     * Set the is msi
     *
     * @param string $isMsi new value being set
     *
     * @return TmCaseDecision
     */
    public function setIsMsi($isMsi)
    {
        $this->isMsi = $isMsi;

        return $this;
    }

    /**
     * Get the is msi
     *
     * @return string
     */
    public function getIsMsi()
    {
        return $this->isMsi;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return TmCaseDecision
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
     * Set the no further action reason
     *
     * @param string $noFurtherActionReason new value being set
     *
     * @return TmCaseDecision
     */
    public function setNoFurtherActionReason($noFurtherActionReason)
    {
        $this->noFurtherActionReason = $noFurtherActionReason;

        return $this;
    }

    /**
     * Get the no further action reason
     *
     * @return string
     */
    public function getNoFurtherActionReason()
    {
        return $this->noFurtherActionReason;
    }

    /**
     * Set the notified date
     *
     * @param \DateTime $notifiedDate new value being set
     *
     * @return TmCaseDecision
     */
    public function setNotifiedDate($notifiedDate)
    {
        $this->notifiedDate = $notifiedDate;

        return $this;
    }

    /**
     * Get the notified date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getNotifiedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->notifiedDate);
        }

        return $this->notifiedDate;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return TmCaseDecision
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
     * Set the rehab measure
     *
     * @param ArrayCollection $rehabMeasures collection being set as the value
     *
     * @return TmCaseDecision
     */
    public function setRehabMeasures($rehabMeasures)
    {
        $this->rehabMeasures = $rehabMeasures;

        return $this;
    }

    /**
     * Get the rehab measures
     *
     * @return ArrayCollection
     */
    public function getRehabMeasures()
    {
        return $this->rehabMeasures;
    }

    /**
     * Add a rehab measures
     *
     * @param ArrayCollection|mixed $rehabMeasures collection being added
     *
     * @return TmCaseDecision
     */
    public function addRehabMeasures($rehabMeasures)
    {
        if ($rehabMeasures instanceof ArrayCollection) {
            $this->rehabMeasures = new ArrayCollection(
                array_merge(
                    $this->rehabMeasures->toArray(),
                    $rehabMeasures->toArray()
                )
            );
        } elseif (!$this->rehabMeasures->contains($rehabMeasures)) {
            $this->rehabMeasures->add($rehabMeasures);
        }

        return $this;
    }

    /**
     * Remove a rehab measures
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $rehabMeasures collection being removed
     *
     * @return TmCaseDecision
     */
    public function removeRehabMeasures($rehabMeasures)
    {
        if ($this->rehabMeasures->contains($rehabMeasures)) {
            $this->rehabMeasures->removeElement($rehabMeasures);
        }

        return $this;
    }

    /**
     * Set the repute not lost reason
     *
     * @param string $reputeNotLostReason new value being set
     *
     * @return TmCaseDecision
     */
    public function setReputeNotLostReason($reputeNotLostReason)
    {
        $this->reputeNotLostReason = $reputeNotLostReason;

        return $this;
    }

    /**
     * Get the repute not lost reason
     *
     * @return string
     */
    public function getReputeNotLostReason()
    {
        return $this->reputeNotLostReason;
    }

    /**
     * Set the unfitness end date
     *
     * @param \DateTime $unfitnessEndDate new value being set
     *
     * @return TmCaseDecision
     */
    public function setUnfitnessEndDate($unfitnessEndDate)
    {
        $this->unfitnessEndDate = $unfitnessEndDate;

        return $this;
    }

    /**
     * Get the unfitness end date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getUnfitnessEndDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->unfitnessEndDate);
        }

        return $this->unfitnessEndDate;
    }

    /**
     * Set the unfitness reason
     *
     * @param ArrayCollection $unfitnessReasons collection being set as the value
     *
     * @return TmCaseDecision
     */
    public function setUnfitnessReasons($unfitnessReasons)
    {
        $this->unfitnessReasons = $unfitnessReasons;

        return $this;
    }

    /**
     * Get the unfitness reasons
     *
     * @return ArrayCollection
     */
    public function getUnfitnessReasons()
    {
        return $this->unfitnessReasons;
    }

    /**
     * Add a unfitness reasons
     *
     * @param ArrayCollection|mixed $unfitnessReasons collection being added
     *
     * @return TmCaseDecision
     */
    public function addUnfitnessReasons($unfitnessReasons)
    {
        if ($unfitnessReasons instanceof ArrayCollection) {
            $this->unfitnessReasons = new ArrayCollection(
                array_merge(
                    $this->unfitnessReasons->toArray(),
                    $unfitnessReasons->toArray()
                )
            );
        } elseif (!$this->unfitnessReasons->contains($unfitnessReasons)) {
            $this->unfitnessReasons->add($unfitnessReasons);
        }

        return $this;
    }

    /**
     * Remove a unfitness reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $unfitnessReasons collection being removed
     *
     * @return TmCaseDecision
     */
    public function removeUnfitnessReasons($unfitnessReasons)
    {
        if ($this->unfitnessReasons->contains($unfitnessReasons)) {
            $this->unfitnessReasons->removeElement($unfitnessReasons);
        }

        return $this;
    }

    /**
     * Set the unfitness start date
     *
     * @param \DateTime $unfitnessStartDate new value being set
     *
     * @return TmCaseDecision
     */
    public function setUnfitnessStartDate($unfitnessStartDate)
    {
        $this->unfitnessStartDate = $unfitnessStartDate;

        return $this;
    }

    /**
     * Get the unfitness start date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getUnfitnessStartDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->unfitnessStartDate);
        }

        return $this->unfitnessStartDate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return TmCaseDecision
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
}
