<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

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
 * AbstractSlaTargetDate Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 */
#[ORM\Table(name: 'sla_target_date')]
#[ORM\Index(name: 'fk_sla_target_date_statement_id_statement_id', columns: ['statement_id'])]
#[ORM\Index(name: 'ix_sla_target_date_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_sla_target_date_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_sla_target_date_pi_id', columns: ['pi_id'])]
#[ORM\Index(name: 'ix_sla_target_date_propose_to_revoke_idx', columns: ['propose_to_revoke_id'])]
#[ORM\Index(name: 'ix_sla_target_date_sla_id', columns: ['sla_id'])]
#[ORM\Index(name: 'ix_sla_target_date_submission_id', columns: ['submission_id'])]
#[ORM\Index(name: 'uk_sla_target_date_document_id', columns: ['document_id'])]
#[ORM\UniqueConstraint(name: 'uk_sla_target_date_document_id', columns: ['document_id'])]
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\SoftDeleteable(fieldName: 'deletedDate', timeAware: true)]
abstract class AbstractSlaTargetDate implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'id', nullable: false)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * Foreign Key to document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\OneToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Doc\Document::class, fetch: 'LAZY')]
    protected $document;

    /**
     * Foreign Key to pi
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\Pi
     */
    #[ORM\JoinColumn(name: 'pi_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Pi\Pi::class, fetch: 'LAZY')]
    protected $pi;

    /**
     * Foreign Key to submission
     *
     * @var \Dvsa\Olcs\Api\Entity\Submission\Submission
     */
    #[ORM\JoinColumn(name: 'submission_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Submission\Submission::class, fetch: 'LAZY')]
    protected $submission;

    /**
     * ProposeToRevoke
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke
     */
    #[ORM\JoinColumn(name: 'propose_to_revoke_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke::class, fetch: 'LAZY')]
    protected $proposeToRevoke;

    /**
     * Statement
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Statement
     */
    #[ORM\JoinColumn(name: 'statement_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Cases\Statement::class, fetch: 'LAZY')]
    protected $statement;

    /**
     * Foreign Key to sla
     *
     * @var \Dvsa\Olcs\Api\Entity\System\Sla
     */
    #[ORM\JoinColumn(name: 'sla_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\System\Sla::class, fetch: 'LAZY')]
    protected $sla;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     */
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: 'LAZY')]
    #[Gedmo\Blameable(on: 'create')]
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     */
    #[ORM\JoinColumn(name: 'last_modified_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: 'LAZY')]
    #[Gedmo\Blameable(on: 'update')]
    protected $lastModifiedBy;

    /**
     * Agreed date
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'date', name: 'agreed_date', nullable: false)]
    protected $agreedDate;

    /**
     * Target date
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'date', name: 'target_date', nullable: true)]
    protected $targetDate;

    /**
     * Sent date
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'date', name: 'sent_date', nullable: true)]
    protected $sentDate;

    /**
     * underDelegation
     *
     * @var string
     */
    #[ORM\Column(type: 'yesno', name: 'under_delegation', nullable: false, options: ['default' => 0])]
    protected $underDelegation = 0;

    /**
     * SLA Target date notes
     *
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'notes', length: 4000, nullable: true)]
    protected $notes;

    /**
     * Version
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint', name: 'version', nullable: false, options: ['default' => 1])]
    #[ORM\Version]
    protected $version = 1;

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
     * @return static
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
     * Set the document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $document new value being set
     *
     * @return static
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
     * Set the pi
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\Pi $pi new value being set
     *
     * @return static
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
     * Set the submission
     *
     * @param \Dvsa\Olcs\Api\Entity\Submission\Submission $submission new value being set
     *
     * @return static
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
     * Set the propose to revoke
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke $proposeToRevoke new value being set
     *
     * @return static
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
     * Set the statement
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Statement $statement new value being set
     *
     * @return static
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
     * Set the sla
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Sla $sla new value being set
     *
     * @return static
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return static
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
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return static
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
     * Set the agreed date
     *
     * @param \DateTime $agreedDate new value being set
     *
     * @return static
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
     * @return \DateTime
     */
    public function getAgreedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->agreedDate);
        }

        return $this->agreedDate;
    }

    /**
     * Set the target date
     *
     * @param \DateTime $targetDate new value being set
     *
     * @return static
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
     * @return \DateTime
     */
    public function getTargetDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->targetDate);
        }

        return $this->targetDate;
    }

    /**
     * Set the sent date
     *
     * @param \DateTime $sentDate new value being set
     *
     * @return static
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
     * @return \DateTime
     */
    public function getSentDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->sentDate);
        }

        return $this->sentDate;
    }

    /**
     * Set the under delegation
     *
     * @param string $underDelegation new value being set
     *
     * @return static
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
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return static
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
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return static
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
     * Get bundle data
     */
    #[\Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
