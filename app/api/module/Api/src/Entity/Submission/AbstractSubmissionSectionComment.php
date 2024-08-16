<?php

namespace Dvsa\Olcs\Api\Entity\Submission;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SubmissionSectionComment Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="submission_section_comment",
 *    indexes={
 *        @ORM\Index(name="ix_submission_section_comment_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_submission_section_comment_last_modified_by",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_submission_section_comment_submission_id", columns={"submission_id"}),
 *        @ORM\Index(name="ix_submission_section_comment_submission_section",
     *     columns={"submission_section"})
 *    }
 * )
 */
abstract class AbstractSubmissionSectionComment implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Comment
     *
     * @var string
     *
     * @ORM\Column(type="text", name="comment", length=16777215, nullable=true)
     */
    protected $comment;

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
     * Submission
     *
     * @var \Dvsa\Olcs\Api\Entity\Submission\Submission
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Submission\Submission",
     *     fetch="LAZY",
     *     inversedBy="submissionSectionComments"
     * )
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id", nullable=false)
     */
    protected $submission;

    /**
     * Submission section
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="submission_section", referencedColumnName="id", nullable=false)
     */
    protected $submissionSection;

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
     * Set the comment
     *
     * @param string $comment new value being set
     *
     * @return SubmissionSectionComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return SubmissionSectionComment
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
     * @return SubmissionSectionComment
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
     * @return SubmissionSectionComment
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
     * Set the submission
     *
     * @param \Dvsa\Olcs\Api\Entity\Submission\Submission $submission entity being set as the value
     *
     * @return SubmissionSectionComment
     */
    public function setSubmission($submission)
    {
        $this->submission = $submission;

        return $this;
    }

    /**
     * Get the submission
     *
     * @return \Dvsa\Olcs\Api\Entity\Submission\Submission
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * Set the submission section
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $submissionSection entity being set as the value
     *
     * @return SubmissionSectionComment
     */
    public function setSubmissionSection($submissionSection)
    {
        $this->submissionSection = $submissionSection;

        return $this;
    }

    /**
     * Get the submission section
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getSubmissionSection()
    {
        return $this->submissionSection;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SubmissionSectionComment
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
