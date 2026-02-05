<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Submission;

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
 * AbstractSubmission Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="submission",
 *    indexes={
 *        @ORM\Index(name="ix_submission_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_submission_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_submission_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_submission_recipient_user_id", columns={"recipient_user_id"}),
 *        @ORM\Index(name="ix_submission_sender_user_id", columns={"sender_user_id"}),
 *        @ORM\Index(name="ix_submission_submission_type", columns={"submission_type"})
 *    }
 * )
 */
abstract class AbstractSubmission implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id")
     */
    protected $case;

    /**
     * SubmissionType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="submission_type", referencedColumnName="id")
     */
    protected $submissionType;

    /**
     * User that assigned a submission to a recipient
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="sender_user_id", referencedColumnName="id", nullable=true)
     */
    protected $senderUser;

    /**
     * The user who must next action a submission
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="recipient_user_id", referencedColumnName="id", nullable=true)
     */
    protected $recipientUser;

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
     * Contains data for each submission section concatenated togather as a JSon string.
     *
     * @var string
     *
     * @ORM\Column(type="text", name="data_snapshot", nullable=true)
     */
    protected $dataSnapshot;

    /**
     * Flag to prioratise submissions for recipient user
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="urgent", nullable=true)
     */
    protected $urgent;

    /**
     * Date submission was assigned
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="assigned_date", nullable=true)
     */
    protected $assignedDate;

    /**
     * Tc sla started
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="tc_sla_started", nullable=false, options={"default": 0})
     */
    protected $tcSlaStarted = 0;

    /**
     * Date all submission information was completed
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="information_complete_date", nullable=true)
     */
    protected $informationCompleteDate;

    /**
     * Date submission completed, no further action required
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="closed_date", nullable=true)
     */
    protected $closedDate;

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
     * Documents
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", mappedBy="submission")
     */
    protected $documents;

    /**
     * SlaTargetDates
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\System\SlaTargetDate", mappedBy="submission", cascade={"persist"}, indexBy="sla_id", orphanRemoval=true)
     */
    protected $slaTargetDates;

    /**
     * SubmissionActions
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Submission\SubmissionAction", mappedBy="submission")
     */
    protected $submissionActions;

    /**
     * SubmissionSectionComments
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Submission\SubmissionSectionComment", mappedBy="submission")
     */
    protected $submissionSectionComments;

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
        $this->documents = new ArrayCollection();
        $this->slaTargetDates = new ArrayCollection();
        $this->submissionActions = new ArrayCollection();
        $this->submissionSectionComments = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Submission
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
     * @return Submission
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
     * Set the submission type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $submissionType new value being set
     *
     * @return Submission
     */
    public function setSubmissionType($submissionType)
    {
        $this->submissionType = $submissionType;

        return $this;
    }

    /**
     * Get the submission type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getSubmissionType()
    {
        return $this->submissionType;
    }

    /**
     * Set the sender user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $senderUser new value being set
     *
     * @return Submission
     */
    public function setSenderUser($senderUser)
    {
        $this->senderUser = $senderUser;

        return $this;
    }

    /**
     * Get the sender user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getSenderUser()
    {
        return $this->senderUser;
    }

    /**
     * Set the recipient user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $recipientUser new value being set
     *
     * @return Submission
     */
    public function setRecipientUser($recipientUser)
    {
        $this->recipientUser = $recipientUser;

        return $this;
    }

    /**
     * Get the recipient user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getRecipientUser()
    {
        return $this->recipientUser;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Submission
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
     * @return Submission
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
     * Set the data snapshot
     *
     * @param string $dataSnapshot new value being set
     *
     * @return Submission
     */
    public function setDataSnapshot($dataSnapshot)
    {
        $this->dataSnapshot = $dataSnapshot;

        return $this;
    }

    /**
     * Get the data snapshot
     *
     * @return string     */
    public function getDataSnapshot()
    {
        return $this->dataSnapshot;
    }

    /**
     * Set the urgent
     *
     * @param string $urgent new value being set
     *
     * @return Submission
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
     * Set the assigned date
     *
     * @param \DateTime $assignedDate new value being set
     *
     * @return Submission
     */
    public function setAssignedDate($assignedDate)
    {
        $this->assignedDate = $assignedDate;

        return $this;
    }

    /**
     * Get the assigned date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getAssignedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->assignedDate);
        }

        return $this->assignedDate;
    }

    /**
     * Set the tc sla started
     *
     * @param bool $tcSlaStarted new value being set
     *
     * @return Submission
     */
    public function setTcSlaStarted($tcSlaStarted)
    {
        $this->tcSlaStarted = $tcSlaStarted;

        return $this;
    }

    /**
     * Get the tc sla started
     *
     * @return bool     */
    public function getTcSlaStarted()
    {
        return $this->tcSlaStarted;
    }

    /**
     * Set the information complete date
     *
     * @param \DateTime $informationCompleteDate new value being set
     *
     * @return Submission
     */
    public function setInformationCompleteDate($informationCompleteDate)
    {
        $this->informationCompleteDate = $informationCompleteDate;

        return $this;
    }

    /**
     * Get the information complete date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getInformationCompleteDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->informationCompleteDate);
        }

        return $this->informationCompleteDate;
    }

    /**
     * Set the closed date
     *
     * @param \DateTime $closedDate new value being set
     *
     * @return Submission
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
     * @return \DateTime     */
    public function getClosedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->closedDate);
        }

        return $this->closedDate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Submission
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
     * Set the documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being set as the value
     *
     * @return Submission
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
     * @return Submission
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
     * @return Submission
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Set the sla target dates
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $slaTargetDates collection being set as the value
     *
     * @return Submission
     */
    public function setSlaTargetDates($slaTargetDates)
    {
        $this->slaTargetDates = $slaTargetDates;

        return $this;
    }

    /**
     * Get the sla target dates
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSlaTargetDates()
    {
        return $this->slaTargetDates;
    }

    /**
     * Add a sla target dates
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $slaTargetDates collection being added
     *
     * @return Submission
     */
    public function addSlaTargetDates($slaTargetDates)
    {
        if ($slaTargetDates instanceof ArrayCollection) {
            $this->slaTargetDates = new ArrayCollection(
                array_merge(
                    $this->slaTargetDates->toArray(),
                    $slaTargetDates->toArray()
                )
            );
        } elseif (!$this->slaTargetDates->contains($slaTargetDates)) {
            $this->slaTargetDates->add($slaTargetDates);
        }

        return $this;
    }

    /**
     * Remove a sla target dates
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $slaTargetDates collection being removed
     *
     * @return Submission
     */
    public function removeSlaTargetDates($slaTargetDates)
    {
        if ($this->slaTargetDates->contains($slaTargetDates)) {
            $this->slaTargetDates->removeElement($slaTargetDates);
        }

        return $this;
    }

    /**
     * Set the submission actions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $submissionActions collection being set as the value
     *
     * @return Submission
     */
    public function setSubmissionActions($submissionActions)
    {
        $this->submissionActions = $submissionActions;

        return $this;
    }

    /**
     * Get the submission actions
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSubmissionActions()
    {
        return $this->submissionActions;
    }

    /**
     * Add a submission actions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $submissionActions collection being added
     *
     * @return Submission
     */
    public function addSubmissionActions($submissionActions)
    {
        if ($submissionActions instanceof ArrayCollection) {
            $this->submissionActions = new ArrayCollection(
                array_merge(
                    $this->submissionActions->toArray(),
                    $submissionActions->toArray()
                )
            );
        } elseif (!$this->submissionActions->contains($submissionActions)) {
            $this->submissionActions->add($submissionActions);
        }

        return $this;
    }

    /**
     * Remove a submission actions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $submissionActions collection being removed
     *
     * @return Submission
     */
    public function removeSubmissionActions($submissionActions)
    {
        if ($this->submissionActions->contains($submissionActions)) {
            $this->submissionActions->removeElement($submissionActions);
        }

        return $this;
    }

    /**
     * Set the submission section comments
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $submissionSectionComments collection being set as the value
     *
     * @return Submission
     */
    public function setSubmissionSectionComments($submissionSectionComments)
    {
        $this->submissionSectionComments = $submissionSectionComments;

        return $this;
    }

    /**
     * Get the submission section comments
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSubmissionSectionComments()
    {
        return $this->submissionSectionComments;
    }

    /**
     * Add a submission section comments
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $submissionSectionComments collection being added
     *
     * @return Submission
     */
    public function addSubmissionSectionComments($submissionSectionComments)
    {
        if ($submissionSectionComments instanceof ArrayCollection) {
            $this->submissionSectionComments = new ArrayCollection(
                array_merge(
                    $this->submissionSectionComments->toArray(),
                    $submissionSectionComments->toArray()
                )
            );
        } elseif (!$this->submissionSectionComments->contains($submissionSectionComments)) {
            $this->submissionSectionComments->add($submissionSectionComments);
        }

        return $this;
    }

    /**
     * Remove a submission section comments
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $submissionSectionComments collection being removed
     *
     * @return Submission
     */
    public function removeSubmissionSectionComments($submissionSectionComments)
    {
        if ($this->submissionSectionComments->contains($submissionSectionComments)) {
            $this->submissionSectionComments->removeElement($submissionSectionComments);
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
