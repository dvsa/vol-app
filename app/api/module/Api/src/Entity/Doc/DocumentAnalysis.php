<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Doc;

use Doctrine\ORM\Mapping as ORM;
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
class DocumentAnalysis extends AbstractDocumentAnalysis
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_SUCCESS = 'SUCCESS';
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_TIMEOUT = 'TIMEOUT';

    /** SUCCESS is absent deliberately: it is sticky, so a racing ERROR cannot overwrite it. */
    public const OVERWRITABLE_STATUSES = [self::STATUS_PENDING, self::STATUS_TIMEOUT];

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
    public function setToken($token): self
    {
        if (!is_string($token)) {
            throw new \TypeError(sprintf(
                '%s(): Argument #1 ($token) must be of type string, %s given',
                __METHOD__,
                get_debug_type($token)
            ));
        }

        if (is_resource($this->token) && $this->readToken() === $token) {
            return $this;
        }

        return parent::setToken($token);
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
}
