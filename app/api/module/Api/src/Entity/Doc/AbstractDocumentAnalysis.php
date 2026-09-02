<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Doc;

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
 * AbstractDocumentAnalysis Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 */
#[ORM\Table(name: 'document_analysis')]
#[ORM\Index(name: 'fk_document_analysis_created_by_user_id', columns: ['created_by'])]
#[ORM\Index(name: 'fk_document_analysis_last_modified_by_user_id', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_document_analysis_application', columns: ['application_id'])]
#[ORM\Index(name: 'ix_document_analysis_document', columns: ['document_id'])]
#[ORM\Index(name: 'ix_document_analysis_status', columns: ['status'])]
#[ORM\Index(name: 'ix_document_analysis_sweeper', columns: ['status', 'created_on'])]
#[ORM\UniqueConstraint(name: 'uk_document_analysis_token', columns: ['token'])]
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractDocumentAnalysis implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', name: 'id', nullable: false, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * Analysed document; deleting the document removes the live analysis row
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Doc\Document::class, fetch: 'LAZY')]
    protected $document;

    /**
     * Owning application; nulled if the application is deleted
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     */
    #[ORM\JoinColumn(name: 'application_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Application\Application::class, fetch: 'LAZY')]
    protected $application;

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
     * UUIDv7 analysis token in packed binary form for point lookups and event correlation
     *
     * @var string|resource
     */
    #[ORM\Column(type: 'binary', name: 'token', length: 16, nullable: false, options: ['fixed' => true])]
    protected $token;

    /**
     * Analysis lifecycle state
     *
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'status', nullable: false, options: ['default' => 'PENDING'])]
    protected $status = 'PENDING';

    /**
     * Provider result payload on success
     *
     * @var array
     */
    #[ORM\Column(type: 'json', name: 'result', nullable: true)]
    protected $result;

    /**
     * Provider metadata accompanying the analysis result
     *
     * @var array
     */
    #[ORM\Column(type: 'json', name: 'result_metadata', nullable: true)]
    protected $resultMetadata;

    /**
     * Failure detail when the analysis ends in ERROR
     *
     * @var string
     */
    #[ORM\Column(type: 'text', name: 'error_detail', nullable: true)]
    protected $errorDetail;

    /**
     * Reserved for caseworker annotations
     *
     * @var array
     */
    #[ORM\Column(type: 'json', name: 'annotations', nullable: true)]
    protected $annotations;

    /**
     * When the sweeper resolved the row to TIMEOUT
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', name: 'timed_out_at', nullable: true)]
    protected $timedOutAt;

    /**
     * When a terminal result was recorded
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', name: 'completed_at', nullable: true)]
    protected $completedAt;

    /**
     * Version
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint', name: 'version', nullable: false, options: ['default' => 1, 'unsigned' => true])]
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
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application new value being set
     *
     * @return static
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
     * Set the token
     *
     * @param string|resource $token new value being set
     *
     * @return static
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the token
     *
     * @return string|resource
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the status
     *
     * @param string $status new value being set
     *
     * @return static
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the result
     *
     * @param array $result new value being set
     *
     * @return static
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get the result
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set the result metadata
     *
     * @param array $resultMetadata new value being set
     *
     * @return static
     */
    public function setResultMetadata($resultMetadata)
    {
        $this->resultMetadata = $resultMetadata;

        return $this;
    }

    /**
     * Get the result metadata
     *
     * @return array
     */
    public function getResultMetadata()
    {
        return $this->resultMetadata;
    }

    /**
     * Set the error detail
     *
     * @param string $errorDetail new value being set
     *
     * @return static
     */
    public function setErrorDetail($errorDetail)
    {
        $this->errorDetail = $errorDetail;

        return $this;
    }

    /**
     * Get the error detail
     *
     * @return string
     */
    public function getErrorDetail()
    {
        return $this->errorDetail;
    }

    /**
     * Set the annotations
     *
     * @param array $annotations new value being set
     *
     * @return static
     */
    public function setAnnotations($annotations)
    {
        $this->annotations = $annotations;

        return $this;
    }

    /**
     * Get the annotations
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * Set the timed out at
     *
     * @param \DateTime $timedOutAt new value being set
     *
     * @return static
     */
    public function setTimedOutAt($timedOutAt)
    {
        $this->timedOutAt = $timedOutAt;

        return $this;
    }

    /**
     * Get the timed out at
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getTimedOutAt($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->timedOutAt);
        }

        return $this->timedOutAt;
    }

    /**
     * Set the completed at
     *
     * @param \DateTime $completedAt new value being set
     *
     * @return static
     */
    public function setCompletedAt($completedAt)
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * Get the completed at
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getCompletedAt($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->completedAt);
        }

        return $this->completedAt;
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
