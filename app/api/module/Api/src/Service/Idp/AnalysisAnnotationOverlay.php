<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp;

use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;

/**
 * Lays caseworker annotations (document_analysis.annotations) over a normalised result, so
 * everything that reads the result - the list, and the review outcome - sees the flags as the
 * caseworker left them.
 *
 * The stored result is never rewritten: it stays exactly what the analyser produced, and the
 * annotations say what was changed, by whom and why. An annotated row has its flag replaced and
 * gains an "override" block carrying the original flag and the reason, so the change can always
 * be shown alongside what it replaced.
 *
 * Stored shape:
 *
 *     ['rows' => ['largeDeposit' => [
 *         'flag' => 'pass',
 *         'originalFlag' => 'fail',
 *         'comment' => '...',
 *         'changedBy' => ['id' => 1, 'name' => 'Jo Bloggs'],
 *         'changedOn' => '2026-10-08 10:00:00',
 *     ]]]
 *
 * Only a fail or a skipped row can be overridden, and only to a pass. An annotation that no
 * longer matches its row (for example the row is now a pass) is ignored rather than trusted.
 */
class AnalysisAnnotationOverlay
{
    public const string KEY_ROWS = 'rows';
    public const string KEY_OVERRIDE = 'override';

    /** The flags a caseworker may change; a pass needs no change. */
    public const array OVERRIDABLE_FLAGS = [NormalisedResult::FLAG_FAIL, NormalisedResult::FLAG_SKIPPED];

    /**
     * @param array<mixed>|null $annotations as stored in document_analysis.annotations
     */
    public function apply(NormalisedResult $result, ?array $annotations): NormalisedResult
    {
        $annotatedRows = $annotations[self::KEY_ROWS] ?? null;

        if (!is_array($annotatedRows) || $annotatedRows === []) {
            return $result;
        }

        $rows = $result->rows();

        foreach ($annotatedRows as $key => $annotation) {
            if (!isset($rows[$key]) || !is_array($annotation)) {
                continue;
            }

            if (!in_array($rows[$key]['flag'] ?? null, self::OVERRIDABLE_FLAGS, true)) {
                continue;
            }

            if (($annotation['flag'] ?? null) !== NormalisedResult::FLAG_PASS) {
                continue;
            }

            $rows[$key] = array_merge($rows[$key], [
                'flag' => NormalisedResult::FLAG_PASS,
                self::KEY_OVERRIDE => [
                    'originalFlag' => $rows[$key]['flag'],
                    'comment' => is_string($annotation['comment'] ?? null) ? $annotation['comment'] : null,
                    'changedBy' => is_string($annotation['changedBy']['name'] ?? null)
                        ? $annotation['changedBy']['name']
                        : null,
                    'changedOn' => is_string($annotation['changedOn'] ?? null) ? $annotation['changedOn'] : null,
                ],
            ]);
        }

        return NormalisedResult::fromRows($rows);
    }

    /**
     * The annotations with one more row changed to a pass.
     *
     * @param array<mixed>|null $annotations as stored
     *
     * @return array{rows: array<string, array>}
     */
    public function withOverride(
        ?array $annotations,
        string $row,
        string $originalFlag,
        string $comment,
        int $userId,
        string $userName,
        \DateTimeInterface $changedOn
    ): array {
        $annotations = is_array($annotations) ? $annotations : [];
        $rows = is_array($annotations[self::KEY_ROWS] ?? null) ? $annotations[self::KEY_ROWS] : [];

        $rows[$row] = [
            'flag' => NormalisedResult::FLAG_PASS,
            'originalFlag' => $originalFlag,
            'comment' => $comment,
            'changedBy' => ['id' => $userId, 'name' => $userName],
            'changedOn' => $changedOn->format('Y-m-d H:i:s'),
        ];

        $annotations[self::KEY_ROWS] = $rows;

        return $annotations;
    }
}

