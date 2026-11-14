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
 * A row can be changed either way, as many times as needed: a fail or skipped row to a pass (the
 * caseworker clears an issue), or a pass to a fail (the caseworker raises one). Neither direction
 * is a one-off; a row changed one way can always be changed back, or changed again.
 *
 * A caseworker can also leave a note without changing anything: the annotation's flag is then the
 * same as the row's own, so applying it changes nothing to show, only the comment. That is how
 * "No, but here is why" is recorded, as distinct from "Yes, change it".
 *
 * An annotation that no longer matches its row for the direction it claims (for example a "fail"
 * annotation on a row that is not currently a pass) is ignored rather than trusted.
 */
class AnalysisAnnotationOverlay
{
    public const string KEY_ROWS = 'rows';
    public const string KEY_OVERRIDE = 'override';

    /** The flags a caseworker may change to a pass; a pass needs no change. */
    public const array OVERRIDABLE_TO_PASS_FLAGS = [NormalisedResult::FLAG_FAIL, NormalisedResult::FLAG_SKIPPED];

    /** The flags a caseworker may change to a fail; only a pass is raised. */
    public const array OVERRIDABLE_TO_FAIL_FLAGS = [NormalisedResult::FLAG_PASS];

    /** The rows a row's current flag must be in for an annotation to move it to the map's key. */
    private const array ALLOWED_SOURCE_FLAGS = [
        NormalisedResult::FLAG_PASS => self::OVERRIDABLE_TO_PASS_FLAGS,
        NormalisedResult::FLAG_FAIL => self::OVERRIDABLE_TO_FAIL_FLAGS,
    ];

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

            $targetFlag = $annotation['flag'] ?? null;
            $currentFlag = $rows[$key]['flag'] ?? null;

            // A note targets the row's own current flag, so it is always allowed: it changes
            // nothing but the comment. Anything else must be an allowed change of direction.
            $isNote = $targetFlag === $currentFlag;
            $allowedSourceFlags = self::ALLOWED_SOURCE_FLAGS[$targetFlag] ?? null;

            if (!$isNote && ($allowedSourceFlags === null || !in_array($currentFlag, $allowedSourceFlags, true))) {
                continue;
            }

            $rows[$key] = array_merge($rows[$key], [
                'flag' => $targetFlag,
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
     * The annotations with one more row changed to $toFlag (a pass, or a fail to reverse one).
     *
     * @param array<mixed>|null $annotations as stored
     *
     * @return array{rows: array<string, array>}
     */
    public function withOverride(
        ?array $annotations,
        string $row,
        string $toFlag,
        string $originalFlag,
        string $comment,
        int $userId,
        string $userName,
        \DateTimeInterface $changedOn
    ): array {
        $annotations = is_array($annotations) ? $annotations : [];
        $rows = is_array($annotations[self::KEY_ROWS] ?? null) ? $annotations[self::KEY_ROWS] : [];

        $rows[$row] = [
            'flag' => $toFlag,
            'originalFlag' => $originalFlag,
            'comment' => $comment,
            'changedBy' => ['id' => $userId, 'name' => $userName],
            'changedOn' => $changedOn->format('Y-m-d H:i:s'),
        ];

        $annotations[self::KEY_ROWS] = $rows;

        return $annotations;
    }
}
