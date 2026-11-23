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
 * Only a fail or a skipped row can be annotated. Its flag can be changed to a pass, or left as
 * it is with just a comment (a note, rather than a change) - the caseworker's answer can always
 * be revisited, which changes the row back or replaces the note. An annotation that no longer
 * matches its row (for example the row is now a pass) is ignored rather than trusted.
 */
class AnalysisAnnotationOverlay
{
    public const string KEY_ROWS = 'rows';
    public const string KEY_OVERRIDE = 'override';

    /** The flags a caseworker may annotate; a pass needs no change or note. */
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

            $rawFlag = $rows[$key]['flag'] ?? null;

            if (!in_array($rawFlag, self::OVERRIDABLE_FLAGS, true)) {
                continue;
            }

            //matrix below can be extended for other scenarios
            $targetFlag = $annotation['flag'] ?? null;

            // Either a genuine change (to a pass) or a note that leaves the row's own flag as it
            // is; anything else no longer matches what this annotation claims to do.
            if ($targetFlag !== NormalisedResult::FLAG_PASS && $targetFlag !== $rawFlag) {
                continue;
            }

            $rows[$key] = array_merge($rows[$key], [
                'flag' => $targetFlag,
                self::KEY_OVERRIDE => [
                    'originalFlag' => $rawFlag,
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
     * The annotations with one row changed to $toFlag: a pass (the caseworker clears the issue)
     * or the row's own original flag (the caseworker leaves it as it is, with a note, or undoes
     * an earlier change back to what the analyser said).
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

    /**
     * Whether a row carries a caseworker's entry (a change or a note).
     *
     * @param array<mixed>|null $annotations as stored
     */
    public function hasRow(?array $annotations, string $row): bool
    {
        return is_array($annotations[self::KEY_ROWS][$row] ?? null);
    }

    /**
     * The annotations with one row's entry taken away, which puts that row back exactly as the
     * analyser left it: no change and no note.
     *
     * @param array<mixed>|null $annotations as stored
     *
     * @return array{rows: array<string, array>}
     */
    public function withoutRow(?array $annotations, string $row): array
    {
        $annotations = is_array($annotations) ? $annotations : [];
        $rows = is_array($annotations[self::KEY_ROWS] ?? null) ? $annotations[self::KEY_ROWS] : [];

        unset($rows[$row]);

        $annotations[self::KEY_ROWS] = $rows;

        return $annotations;
    }
}
