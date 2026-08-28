<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Doc;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Uid\UuidV7;

/**
 * Created PENDING when a document is submitted for analysis; moved to TIMEOUT by the sweeper,
 * or to SUCCESS/ERROR when the result comes back.
 *
 * Status transitions after insert are atomic UPDATEs issued by the repository - never
 * read-modify-write through this entity, as the sweeper and result handler can race.
 */
#[ORM\Table(name: 'document_analysis')]
#[ORM\Index(name: 'ix_document_analysis_document', columns: ['document_id'])]
#[ORM\Index(name: 'ix_document_analysis_application', columns: ['application_id'])]
#[ORM\Index(name: 'ix_document_analysis_sweeper', columns: ['status', 'created_on'])]
#[ORM\Index(name: 'ix_document_analysis_status', columns: ['status'])]
#[ORM\UniqueConstraint(name: 'uk_document_analysis_token', columns: ['token'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class DocumentAnalysis
{
    use CreatedOnTrait;
    use ModifiedOnTrait;
    // Required by the two traits above - their setters normalise input via asDateTime().
    use ProcessDateTrait;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_TIMEOUT = 'TIMEOUT';

    /** SUCCESS is absent deliberately: it is sticky, so a racing ERROR cannot overwrite it. */
    public const OVERWRITABLE_STATUSES = [self::STATUS_PENDING, self::STATUS_TIMEOUT];

    #[ORM\Id]
    #[ORM\Column(type: 'bigint', name: 'id', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /** UUIDv7 as BINARY(16) - the external identifier used in events, S3 prefixes and the CLI. */
    #[ORM\Column(type: 'binary', name: 'token', length: 16, nullable: false, options: ['fixed' => true])]
    protected $token;

    #[ORM\ManyToOne(targetEntity: Document::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $document;

    // Nullable: the DB nulls this FK when the owning application is deleted, preserving the row.
    #[ORM\ManyToOne(targetEntity: Application::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'application_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected $application;

    #[ORM\Column(type: 'string', name: 'status', nullable: false, options: ['default' => self::STATUS_PENDING])]
    protected $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'json', name: 'result', nullable: true)]
    protected $result;

    #[ORM\Column(type: 'json', name: 'result_metadata', nullable: true)]
    protected $resultMetadata;

    #[ORM\Column(type: 'text', name: 'error_detail', nullable: true)]
    protected $errorDetail;

    /** Reserved for caseworker annotations; unused for now. */
    #[ORM\Column(type: 'json', name: 'annotations', nullable: true)]
    protected $annotations;

    /** Survives a late terminal override, so this plus completedAt shows a late-arriving result. */
    #[ORM\Column(type: 'datetime', name: 'timed_out_at', nullable: true)]
    protected $timedOutAt;

    #[ORM\Column(type: 'datetime', name: 'completed_at', nullable: true)]
    protected $completedAt;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User|null
     */
    #[ORM\JoinColumn(name: 'last_modified_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: 'LAZY')]
    #[Gedmo\Blameable(on: 'update')]
    protected $lastModifiedBy;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User|null
     */
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: 'LAZY')]
    #[Gedmo\Blameable(on: 'create')]
    protected $createdBy;

    #[ORM\Column(type: 'smallint', name: 'version', nullable: false, options: ['unsigned' => true, 'default' => 1])]
    #[ORM\Version]
    protected $version = 1;

    public function getId()
    {
        return $this->id;
    }

    /** Raw 16 bytes as stored; use getTokenString() for the form that goes on the wire. */
    public function getToken(): string
    {
        return $this->readToken();
    }

    /**
     * Reassigning the same token is a no-op by design.
     *
     * A hydrated entity holds a stream here, and the UnitOfWork compares scalar fields by
     * identity (`$orgValue !== $actualValue`), so replacing that stream with an equal string
     * still reads as a change: a pointless UPDATE, a bumped @Version, and a spurious row in
     * document_analysis_hist. Guarding the round trip keeps setToken(getToken()) harmless.
     */
    public function setToken(string $token): self
    {
        if (is_resource($this->token) && $this->readToken() === $token) {
            return $this;
        }

        $this->token = $token;

        return $this;
    }

    /** The dashed UUID string, as it appears on the wire. */
    public function getTokenString(): string
    {
        return UuidV7::fromBinary($this->readToken())->toRfc4122();
    }

    /**
     * The raw 16 bytes, whichever form the property currently holds.
     *
     * Doctrine's BinaryType always hydrates a BINARY column into a php://temp stream, so a
     * managed entity holds a resource here while a freshly constructed one holds the string
     * passed to setToken(). Reading a stream advances its pointer, so the read is anchored
     * at offset 0 rather than trusting wherever an earlier call left it; without that, the
     * second read of the same entity returns an empty string and fromBinary() throws.
     */
    private function readToken(): string
    {
        if (is_resource($this->token)) {
            return (string)stream_get_contents($this->token, -1, 0);
        }

        return (string)$this->token;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    /** @return Application|null null once the owning application has been deleted */
    public function getApplication()
    {
        return $this->application;
    }

    public function setApplication(Application $application): self
    {
        $this->application = $application;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getResultMetadata()
    {
        return $this->resultMetadata;
    }

    public function getErrorDetail()
    {
        return $this->errorDetail;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    public function getTimedOutAt()
    {
        return $this->timedOutAt;
    }

    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    public function getVersion()
    {
        return $this->version;
    }
}
