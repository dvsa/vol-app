<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser;

/**
 * The normalised analysis result at its current version: what document_analysis.result_normalised
 * stores and what DocumentAnalysisList returns.
 *
 * It exists in exactly one shape, the current one. A payload read from storage at an earlier
 * version is lifted by the Version mappers before it becomes one of these, so code holding a
 * NormalisedResult never has to ask which version it is looking at.
 *
 * The array form is the contract with the internal app and with caseworker annotations, which
 * are later merged over it row by row with the annotation winning. For that merge to be a plain
 * array_merge() per row, every row has the same four keys and rows are a map, never a list:
 *
 *     ['version' => 1, 'rows' => ['name' => ['flag' => 'pass', 'remark' => '...',
 *         'value' => '...', 'checks' => ['FI06' => ['result' => 'pass', 'remark' => '...']]]]]
 */
final class NormalisedResult
{
    /**
     * Bumped whenever the payload shape or the FI check to category mapping changes. Stored
     * payloads keep the version they were written with; AnalysisResultNormaliser::fromStored()
     * lifts them to this one on the way out.
     */
    public const int VERSION = 1;

    public const string FLAG_PASS = 'pass';
    public const string FLAG_FAIL = 'fail';
    public const string FLAG_SKIPPED = 'skipped';

    /**
     * @param array<string, array{
     *     flag: string|null,
     *     remark: string|null,
     *     value: mixed,
     *     checks: array<string, array{result: string, remark: string|null}>
     * }> $rows
     */
    private function __construct(private readonly array $rows)
    {
    }

    /**
     * @param array<string, array{flag: string|null, remark: string|null, value: mixed, checks: array}> $rows
     */
    public static function fromRows(array $rows): self
    {
        return new self($rows);
    }

    /**
     * Only a payload already at the current version is accepted; anything else must go through
     * the Version mappers first. Null, not an exception: a stored payload is data, not code.
     *
     * @param array<mixed> $payload
     */
    public static function fromArray(array $payload): ?self
    {
        if (($payload['version'] ?? null) !== self::VERSION || !is_array($payload['rows'] ?? null)) {
            return null;
        }

        return new self($payload['rows']);
    }

    /**
     * @return array<string, array{flag: string|null, remark: string|null, value: mixed, checks: array}>
     */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * @return array{version: int, rows: array<string, array>}
     */
    public function toArray(): array
    {
        return ['version' => self::VERSION, 'rows' => $this->rows];
    }
}
