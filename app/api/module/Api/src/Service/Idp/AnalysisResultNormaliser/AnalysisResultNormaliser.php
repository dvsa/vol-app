<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser;

use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\Version\VersionMapperInterface;
use Olcs\Logging\Log\Logger;

/**
 * The single entry point for the normalised analysis result. Whichever way in, the result is a
 * NormalisedResult at the current version:
 *
 *  - normalise(): from the AI analysis report, when a result is stored (ReportMapper).
 *  - fromStored(): from a result_normalised payload of any version, when a result is read
 *    (the Version mappers, chained from the stored version up to the current one).
 *
 * To bump the version: raise NormalisedResult::VERSION, change ReportMapper to produce the new
 * shape, add a VersionMapperInterface implementation for the superseded version and register it
 * in the factory. Stored rows are not touched. Deploy the API before the internal app; until the
 * internal app follows, it shows the new version as "no assessment" rather than render a shape
 * it does not know.
 *
 * Not final: the handlers that depend on it mock it in their tests.
 */
class AnalysisResultNormaliser
{
    /** @var array<int, VersionMapperInterface> keyed by the stored version each one reads */
    private array $versionMappers = [];

    /**
     * @param iterable<VersionMapperInterface> $versionMappers one per superseded version
     */
    public function __construct(private readonly ReportMapper $reportMapper, iterable $versionMappers = [])
    {
        foreach ($versionMappers as $mapper) {
            if (isset($this->versionMappers[$mapper->fromVersion()])) {
                throw new \LogicException(sprintf(
                    'Two version mappers read stored version %d: %s and %s',
                    $mapper->fromVersion(),
                    $this->versionMappers[$mapper->fromVersion()]::class,
                    $mapper::class
                ));
            }

            $this->versionMappers[$mapper->fromVersion()] = $mapper;
        }
    }

    /**
     * @param array<mixed> $report the analysis report as the pipeline produced it
     *
     * @return NormalisedResult|null null when the report holds no analysis to normalise
     */
    public function normalise(array $report): ?NormalisedResult
    {
        return $this->reportMapper->map($report);
    }

    /**
     * @param array<mixed>|null $stored the payload as read from result_normalised
     *
     * @return NormalisedResult|null null when there is nothing stored, or no path from the
     *                               stored version to the current one
     */
    public function fromStored(?array $stored): ?NormalisedResult
    {
        if ($stored === null) {
            return null;
        }

        $version = $stored['version'] ?? null;

        if (!is_int($version)) {
            Logger::warn('IDP analysis result: stored payload has no usable version', ['version' => $version]);

            return null;
        }

        $payload = $stored;

        while ($version < NormalisedResult::VERSION) {
            $mapper = $this->versionMappers[$version] ?? null;

            if ($mapper === null) {
                Logger::warn('IDP analysis result: no version mapper from stored payload version', [
                    'stored_version' => $version,
                    'current_version' => NormalisedResult::VERSION,
                ]);

                return null;
            }

            $payload = $mapper->upgrade($payload);
            $version++;
        }

        // A payload written by newer code than this is also unusable: there is no way down.
        if ($version !== NormalisedResult::VERSION) {
            Logger::warn('IDP analysis result: stored payload is newer than this code', [
                'stored_version' => $version,
                'current_version' => NormalisedResult::VERSION,
            ]);

            return null;
        }

        return NormalisedResult::fromArray($payload);
    }
}
