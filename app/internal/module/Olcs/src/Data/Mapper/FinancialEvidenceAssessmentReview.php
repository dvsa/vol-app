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
 * changed it to a pass (the API lays that change over the result and says what it replaced).
 * A pass is a check the analyser passed, whether or not a caseworker has since changed it to a
 * fail, the same way; bank and bank address are shown with them for reference. A row's section
 * is fixed by what it started as, so an override never moves a row off the page it is shown on -
 * it only changes the badge shown against it.
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
     *         flagged: bool,
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
     *         changedBy: string|null
     *     }>,
     *     passes: list<array{
     *         key: string,
     *         flagged: bool,
     *         label: string,
     *         value: string|null,
     *         flag: string|null,
     *         flagTag: string|null,
     *         remark: string|null,
     *         comment: string|null,
     *         changed: bool,
     *         kept: bool,
     *         changedTo: string|null,
     *         changedToTag: string|null,
     *         changedBy: string|null
     *     }>,
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
            // A row's section is fixed by what it started as, never by what a caseworker has
            // since changed it to: an override only changes the badge shown against the row, so a
            // row can never seem to vanish from the page the caseworker is looking at by being
            // changed on it (an issue changed to a pass stays listed as an issue, grey and
            // superseded, and a pass changed to a fail stays listed as a pass the same way).
            $originalFlag = $row['override']['originalFlag'] ?? $row['flag'];

            if ($row['flagged'] && $originalFlag !== FinancialEvidenceAssessmentTab::FLAG_PASS) {
                $issues[] = self::entry($row, $originalFlag, true);
            } else {
                $passes[] = self::entry($row, $originalFlag, false);
            }

            // Read directly from the row's own current flag, not from its section: a row still
            // needs resolving for as long as it does not currently read as a pass, whichever
            // section it is shown in.
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
     * separately from them.
     */
    private static function isRealChange(array $row): bool
    {
        $override = $row['override'];

        return $override !== null && $override['originalFlag'] !== $row['flag'];
    }

    /**
     * One row as either list shows it: the infringing value (issues only; null when there is none
     * worth showing), the flag it started as with its remark, and the caseworker's decision on it.
     *
     * A genuine change (the row's flag moved) greys out the original flag and carries the flag it
     * was changed to. A note (the caseworker left a comment but kept the flag as it stands) keeps
     * the flag as it is and says so - "Kept as X by Y" - rather than either staying silent or
     * wrongly claiming a change that did not happen.
     */
    private static function entry(array $row, ?string $originalFlag, bool $isIssue): array
    {
        $override = $row['override'];
        $changed = self::isRealChange($row);
        $kept = $override !== null && !$changed;

        if ($isIssue) {
            $showValue = !in_array($row['key'], self::ROWS_WITHOUT_INFRINGING_VALUE, true)
                && $row['value'] !== null
                && $row['value'] !== self::NO_VALUE;
            $value = $showValue ? $row['value'] : null;
        } else {
            // The passes list shows a row's value for reference regardless (e.g. a large deposit
            // count), not just when it names what failed.
            $value = $row['value'];
        }

        return [
            'key' => $row['key'],
            'flagged' => $row['flagged'],
            'label' => $row['label'],
            'value' => $value,
            'flag' => $originalFlag,
            'flagTag' => $changed
                ? FinancialEvidenceAssessmentTab::FLAG_TAG_SUPERSEDED
                : ($originalFlag !== null ? FinancialEvidenceAssessmentTab::flagTag($originalFlag) : null),
            'remark' => $row['remark'],
            'comment' => $override['comment'] ?? null,
            'changed' => $changed,
            'kept' => $kept,
            'changedTo' => ($changed || $kept) ? $row['flag'] : null,
            'changedToTag' => ($changed || $kept) ? $row['flagTag'] : null,
            'changedBy' => $override['changedBy'] ?? null,
        ];
    }
}
