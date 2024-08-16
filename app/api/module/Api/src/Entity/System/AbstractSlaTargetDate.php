<?php

namespace Dvsa\Olcs\Api\Entity\System;

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
 * SlaTargetDate Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="sla_target_date",
 *    indexes={
 *        @ORM\Index(name="fk_sla_target_date_statement_id_statement_id", columns={"statement_id"}),
 *        @ORM\Index(name="ix_sla_target_date_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_sla_target_date_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_sla_target_date_pi_id", columns={"pi_id"}),
 *        @ORM\Index(name="ix_sla_target_date_propose_to_revoke_idx",
     *     columns={"propose_to_revoke_id"}),
 *        @ORM\Index(name="ix_sla_target_date_sla_id", columns={"sla_id"}),
 *        @ORM\Index(name="ix_sla_target_date_submission_id", columns={"submission_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_sla_target_date_document_id", columns={"document_id"})
 *    }
 * )
 */
abstract class AbstractSlaTargetDate implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Agreed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="agreed_date", nullable=false)
     */
    protected $agreedDate;

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
     * Document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\OneToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document",
     *     fetch="LAZY",
     *     inversedBy="slaTargetDate"
     * )
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=true)
     */
    protected $document;

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
     * Notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notes", length=4000, nullable=true)
     */
    protected $notes;

    /**
     * Pi
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\Pi
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Pi\Pi",
     *     fetch="LAZY",
     *     inversedBy="slaTargetDates"
     * )
     * @ORM\JoinColumn(name="pi_id", referencedColumnName="id", nullable=true)
     */
    protected $pi;

    /**
     * Propose to revoke
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke",
     *     fetch="LAZY",
     *     inversedBy="slaTargetDates"
     * )
     * @ORM\JoinColumn(name="propose_to_revoke_id", referencedColumnName="id", nullable=true)
     */
    protected $proposeToRevoke;

    /**
     * Sent date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="sent_date", nullable=true)
     */
    protected $sentDate;

    /**
     * Sla
     *
     * @var \Dvsa\Olcs\Api\Entity\System\Sla
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\Sla", fetch="LAZY")
     * @ORM\JoinColumn(name="sla_id", referencedColumnName="id", nullable=true)
     */
    protected $sla;

    /**
     * Statement
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Statement
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Cases\Statement",
     *     fetch="LAZY",
     *     inversedBy="slaTargetDates"
     * )
     * @ORM\JoinColumn(name="statement_id", referencedColumnName="id", nullable=true)
     */
    protected $statement;

    /**
     * Submission
     *
     * @var \Dvsa\Olcs\Api\Entity\Submission\Submission
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Submission\Submission",
     *     fetch="LAZY",
     *     inversedBy="slaTargetDates"
     * )
     * @ORM\JoinColumn(name="submission_id", referencedColumnName="id", nullable=true)
     */
    protected $submission;

    /**
     * Target date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="target_date", nullable=true)
     */
    protected $targetDate;

    /**
     * Under delegation
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="under_delegation", nullable=false, options={"default": 0})
     */
    protected $underDelegation = 0;

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
     * Set the agreed date
     *
     * @param \DateTime $agreedDate new value being set
     *
     * @return SlaTargetDate
     */
    public function setAgreedDate($agreedDate)
    {
        $this->agreedDate = $agreedDate;

        return $this;
    }

    /**
     * Get the agreed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getAgreedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->agreedDate);
        }

        return $this->agreedDate;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return SlaTargetDate
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
     * Set the document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $document entity being set as the value
     *
     * @return SlaTargetDate
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get the document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return SlaTargetDate
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
     * @return SlaTargetDate
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
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return SlaTargetDate
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get the notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set the pi
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\Pi $pi entity being set as the value
     *
     * @return SlaTargetDate
     */
    public function setPi($pi)
    {
        $this->pi = $pi;

        return $this;
    }

    /**
     * Get the pi
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\Pi
     */
    public function getPi()
    {
        return $this->pi;
    }

    /**
     * Set the propose to revoke
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke $proposeToRevoke entity being set as the value
     *
     * @return SlaTargetDate
     */
    public function setProposeToRevoke($proposeToRevoke)
    {
        $this->proposeToRevoke = $proposeToRevoke;

        return $this;
    }

    /**
     * Get the propose to revoke
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke
     */
    public function getProposeToRevoke()
    {
        return $this->proposeToRevoke;
    }

    /**
     * Set the sent date
     *
     * @param \DateTime $sentDate new value being set
     *
     * @return SlaTargetDate
     */
    public function setSentDate($sentDate)
    {
        $this->sentDate = $sentDate;

        return $this;
    }

    /**
     * Get the sent date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getSentDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->sentDate);
        }

        return $this->sentDate;
    }

    /**
     * Set the sla
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Sla $sla entity being set as the value
     *
     * @return SlaTargetDate
     */
    public function setSla($sla)
    {
        $this->sla = $sla;

        return $this;
    }

    /**
     * Get the sla
     *
     * @return \Dvsa\Olcs\Api\Entity\System\Sla
     */
    public function getSla()
    {
        return $this->sla;
    }

    /**
     * Set the statement
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Statement $statement entity being set as the value
     *
     * @return SlaTargetDate
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;

        return $this;
    }

    /**
     * Get the statement
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Statement
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * Set the submission
     *
     * @param \Dvsa\Olcs\Api\Entity\Submission\Submission $submission entity being set as the value
     *
     * @return SlaTargetDate
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
     * Set the target date
     *
     * @param \DateTime $targetDate new value being set
     *
     * @return SlaTargetDate
     */
    public function setTargetDate($targetDate)
    {
        $this->targetDate = $targetDate;

        return $this;
    }

    /**
     * Get the target date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getTargetDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->targetDate);
        }

        return $this->targetDate;
    }

    /**
     * Set the under delegation
     *
     * @param string $underDelegation new value being set
     *
     * @return SlaTargetDate
     */
    public function setUnderDelegation($underDelegation)
    {
        $this->underDelegation = $underDelegation;

        return $this;
    }

    /**
     * Get the under delegation
     *
     * @return string
     */
    public function getUnderDelegation()
    {
        return $this->underDelegation;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SlaTargetDate
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
