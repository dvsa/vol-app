<?php

declare(strict_types=1);

namespace Olcs\Data\Mapper;

/**
 * Maps one row of DocumentAnalysisList onto the "change document review" page: the issues a
 * caseworker can change, each listed on its own, and the checks that passed.
 *
 * Built on FinancialEvidenceAssessmentTab, which owns labels, formatting and tag colours, so the
 * two pages always describe a check the same way. Like it, this returns plain text; the view and
 * the passes table formatters escape it.
 *
 * An issue is a check the analyser failed or skipped, whether or not a caseworker has since
 * changed it to a pass (the API lays that change over the result and says what it replaced), or
 * left a note on it without changing it (a note is a change-or-note the caseworker can revisit
 * the same way). A pass is a check the analyser passed; bank and bank address are shown with
 * them for reference.
 */
final class FinancialEvidenceAssessmentReview
{
    /**
     * Values that name the infringing data. Large deposit holds only a count of deposits, and
     * Authenticity no value at all, so neither says what the check failed on.
     */
    private const array ROWS_WITHOUT_INFRINGING_VALUE = ['authenticity', 'largeDeposit'];

    /** What FinancialEvidenceAssessmentTab shows for a missing value. */
    private const string NO_VALUE = '-';

    /**
     * @param array $analysis one entry of DocumentAnalysisList's "analyses"
     *
     * @return array{
     *     document: array{id: int, name: string|null}|null,
     *     hasAssessment: bool,
     *     issues: list<array{
     *         key: string,
     *         label: string,
     *         value: string|null,
     *         flag: string,
     *         flagTag: string,
     *         remark: string|null,
     *         comment: string|null,
     *         changed: bool,
     *         kept: bool,
     *         changedTo: string|null,
     *         changedToTag: string|null,
     *         changedBy: string|null,
     *         changedOn: string|null
     *     }>,
     *     passes: list<array{label: string, value: string|null, flag: string|null, flagTag: string|null, comment: string|null}>,
     *     passCount: int,
     *     unchangedIssueCount: int
     * }
     */
    public static function mapFromAnalysis(array $analysis): array
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis);

        $issues = [];
        $passes = [];
        $unchangedIssueCount = 0;

        foreach ($tab['rows'] as $row) {
            if ($row['flagged'] && ($row['flag'] !== FinancialEvidenceAssessmentTab::FLAG_PASS || $row['override'] !== null)) {
                $issues[] = self::issue($row);
            } else {
                $passes[] = [
                    'label' => $row['label'],
                    'value' => $row['value'],
                    'flag' => $row['flag'],
                    'flagTag' => $row['flagTag'],
                    'comment' => null,
                ];
            }

            // Read directly from the row's own current flag, not from the issue built above: a
            // row still needs resolving for as long as it does not currently read as a pass,
            // whether that is because nobody has answered it or because the caseworker's answer
            // left it as it was (a note, not a change).
            if ($row['flagged'] && $row['flag'] !== FinancialEvidenceAssessmentTab::FLAG_PASS) {
                $unchangedIssueCount++;
            }
        }

        return [
            'document' => $tab['document'],
            'hasAssessment' => $tab['hasAssessment'],
            'issues' => $issues,
            'passes' => $passes,
            'passCount' => count(array_filter($passes, static fn(array $pass): bool => $pass['flag'] !== null)),
            'unchangedIssueCount' => $unchangedIssueCount,
        ];
    }

    /**
     * Whether a row's override is a genuine change of flag, rather than a note left on the row
     * as it stands (the override's original flag then equals the row's own current flag). This
     * reads the override's own stored flags directly; nothing about "having changed" is tracked
     * separately from them, so undoing a change (the caseworker answers "No" again) reads the
     * same as never having changed it, other than the note and the audit trail it leaves behind.
     */
    private static function isRealChange(array $row): bool
    {
        $override = $row['override'];

        return $override !== null && $override['originalFlag'] !== $row['flag'];
    }

    /**
     * One issue as the view lists it: the infringing value (null when there is none worth
     * showing), the flag the analyser gave with its remark, and the caseworker's answer on it.
     *
     * A genuine change (the row's flag moved) greys out the original flag and carries the flag it
     * was changed to. A note (the caseworker left a comment but kept the flag as it stands) keeps
     * the flag as it is and says so, rather than either staying silent or wrongly claiming a
     * change that did not happen.
     */
    private static function issue(array $row): array
    {
        $override = $row['override'];
        $originalFlag = $override['originalFlag'] ?? (string)$row['flag'];
        $changed = self::isRealChange($row);
        $kept = $override !== null && !$changed;
        $showValue = !in_array($row['key'], self::ROWS_WITHOUT_INFRINGING_VALUE, true)
            && $row['value'] !== null
            && $row['value'] !== self::NO_VALUE;

        return [
            'key' => $row['key'],
            'label' => $row['label'],
            'value' => $showValue ? $row['value'] : null,
            'flag' => $originalFlag,
            'flagTag' => $changed
                ? FinancialEvidenceAssessmentTab::FLAG_TAG_SUPERSEDED
                : FinancialEvidenceAssessmentTab::flagTag($originalFlag),
            'remark' => $row['remark'],
            'comment' => $override['comment'] ?? null,
            'changed' => $changed,
            'kept' => $kept,
            'changedTo' => ($changed || $kept) ? $row['flag'] : null,
            'changedToTag' => ($changed || $kept) ? $row['flagTag'] : null,
            'changedBy' => $override['changedBy'] ?? null,
            'changedOn' => $override['changedOn'] ?? null,
        ];
    }
}
