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
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractSubmissionAction Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="submission_action",
 *    indexes={
 *        @ORM\Index(name="ix_submission_action_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_submission_action_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_submission_action_submission_id", columns={"submission_id"})
 *    }
 * )
 */
abstract class AbstractSubmissionAction implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to submission
     *
     * @var \Dvsa\Olcs\Api\Entity\Submission\Submission
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Submission\Submission", fetch="LAZY")
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id")
     */
    protected $submission;

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
     * isDecision
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_decision", nullable=false)
     */
    protected $isDecision = 0;

    /**
     * Comment
     *
     * @var string
     *
     * @ORM\Column(type="text", name="comment", nullable=true)
     */
    protected $comment;

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
     * Reasons
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\Pi\Reason", inversedBy="submissionActions", fetch="LAZY")
     * @ORM\JoinTable(name="submission_action_reason",
     *     joinColumns={
     *         @ORM\JoinColumn(name="submission_action_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="reason_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $reasons;

    /**
     * ActionType
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", inversedBy="submissionActions", fetch="LAZY")
     * @ORM\JoinTable(name="submission_action_type",
     *     joinColumns={
     *         @ORM\JoinColumn(name="submission_action_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="action_type", referencedColumnName="id")
     *     }
     * )
     */
    protected $actionType;

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
        $this->reasons = new ArrayCollection();
        $this->actionType = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return SubmissionAction
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
     * Set the submission
     *
     * @param \Dvsa\Olcs\Api\Entity\Submission\Submission $submission new value being set
     *
     * @return SubmissionAction
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return SubmissionAction
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
     * @return SubmissionAction
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
     * Set the is decision
     *
     * @param string $isDecision new value being set
     *
     * @return SubmissionAction
     */
    public function setIsDecision($isDecision)
    {
        $this->isDecision = $isDecision;

        return $this;
    }

    /**
     * Get the is decision
     *
     * @return string     */
    public function getIsDecision()
    {
        return $this->isDecision;
    }

    /**
     * Set the comment
     *
     * @param string $comment new value being set
     *
     * @return SubmissionAction
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the comment
     *
     * @return string     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SubmissionAction
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
     * Set the reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $reasons collection being set as the value
     *
     * @return SubmissionAction
     */
    public function setReasons($reasons)
    {
        $this->reasons = $reasons;

        return $this;
    }

    /**
     * Get the reasons
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReasons()
    {
        return $this->reasons;
    }

    /**
     * Add a reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $reasons collection being added
     *
     * @return SubmissionAction
     */
    public function addReasons($reasons)
    {
        if ($reasons instanceof ArrayCollection) {
            $this->reasons = new ArrayCollection(
                array_merge(
                    $this->reasons->toArray(),
                    $reasons->toArray()
                )
            );
        } elseif (!$this->reasons->contains($reasons)) {
            $this->reasons->add($reasons);
        }

        return $this;
    }

    /**
     * Remove a reasons
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $reasons collection being removed
     *
     * @return SubmissionAction
     */
    public function removeReasons($reasons)
    {
        if ($this->reasons->contains($reasons)) {
            $this->reasons->removeElement($reasons);
        }

        return $this;
    }

    /**
     * Set the action type
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $actionType collection being set as the value
     *
     * @return SubmissionAction
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * Get the action type
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Add a action type
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $actionType collection being added
     *
     * @return SubmissionAction
     */
    public function addActionType($actionType)
    {
        if ($actionType instanceof ArrayCollection) {
            $this->actionType = new ArrayCollection(
                array_merge(
                    $this->actionType->toArray(),
                    $actionType->toArray()
                )
            );
        } elseif (!$this->actionType->contains($actionType)) {
            $this->actionType->add($actionType);
        }

        return $this;
    }

    /**
     * Remove a action type
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $actionType collection being removed
     *
     * @return SubmissionAction
     */
    public function removeActionType($actionType)
    {
        if ($this->actionType->contains($actionType)) {
            $this->actionType->removeElement($actionType);
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