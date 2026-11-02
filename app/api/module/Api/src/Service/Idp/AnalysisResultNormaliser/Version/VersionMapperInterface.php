<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\Version;

/**
 * Lifts a stored normalised payload from one superseded version to the next.
 *
 * Stored payloads are never rewritten when NormalisedResult::VERSION is bumped: a row keeps the
 * version it was written with, which is also the assessment the caseworker saw and annotated.
 * Instead, one mapper is added per superseded version, and AnalysisResultNormaliser::fromStored()
 * chains them from the stored version up to the current one on every read.
 *
 * A mapper can only reshape what its input version already holds. A change that needs
 * information never normalised at the earlier version is the one case for regenerating a row
 * from the raw report, as a deliberate correction rather than part of a version bump.
 */
interface VersionMapperInterface
{
    /** The stored payload version this mapper reads. */
    public function fromVersion(): int;

    /**
     * Returns the complete payload at fromVersion() + 1, including its "version" key.
     *
     * @param array<mixed> $payload a payload at fromVersion()
     *
     * @return array<mixed>
     */
    public function upgrade(array $payload): array;
}
