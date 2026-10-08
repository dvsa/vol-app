<?php

declare(strict_types=1);

namespace Olcs\Data\Mapper;

/**
 * Maps one row of DocumentAnalysisList onto the "change document review" page: the issues a
 * caseworker can change, each as the rows of its own small table, and the checks that passed.
 *
 * Built on FinancialEvidenceAssessmentTab, which owns labels, formatting and tag colours, so the
 * two pages always describe a check the same way. Like it, this returns plain text; the table
 * formatters escape it.
 *
 * An issue is a check the analyser failed or skipped, whether or not a caseworker has since
 * changed it to a pass (the API lays that change over the result and says what it replaced).
 * A pass is a check the analyser passed; bank and bank address are shown with them for reference.
 */
final class FinancialEvidenceAssessmentReview
{
    /** Row types of an issue table, read by its formatters. */
    public const string ROW_VALUE = 'value';
    public const string ROW_FLAG = 'flag';
    public const string ROW_REMARK = 'remark';
    public const string ROW_COMMENT = 'comment';

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
     *     issues: list<array{key: string, label: string, changed: bool, tableRows: list<array>}>,
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

        foreach ($tab['rows'] as $row) {
            if ($row['flagged'] && ($row['flag'] !== FinancialEvidenceAssessmentTab::FLAG_PASS || $row['override'] !== null)) {
                $issues[] = self::issue($row);
                continue;
            }

            $passes[] = [
                'label' => $row['label'],
                'value' => $row['value'],
                'flag' => $row['flag'],
                'flagTag' => $row['flagTag'],
                'comment' => null,
            ];
        }

        return [
            'document' => $tab['document'],
            'hasAssessment' => $tab['hasAssessment'],
            'issues' => $issues,
            'passes' => $passes,
            'passCount' => count(array_filter($passes, static fn(array $pass): bool => $pass['flag'] !== null)),
            'unchangedIssueCount' => count(array_filter($issues, static fn(array $issue): bool => !$issue['changed'])),
        ];
    }

    private static function issue(array $row): array
    {
        $override = $row['override'];
        $originalFlag = $override['originalFlag'] ?? (string)$row['flag'];

        $tableRows = [];

        if (
            !in_array($row['key'], self::ROWS_WITHOUT_INFRINGING_VALUE, true)
            && $row['value'] !== null
            && $row['value'] !== self::NO_VALUE
        ) {
            $tableRows[] = ['type' => self::ROW_VALUE, 'heading' => $row['label'], 'text' => $row['value']];
        }

        $tableRows[] = [
            'type' => self::ROW_FLAG,
            'heading' => 'Flag',
            'flag' => $originalFlag,
            'flagTag' => $override === null
                ? FinancialEvidenceAssessmentTab::flagTag($originalFlag)
                : FinancialEvidenceAssessmentTab::FLAG_TAG_SUPERSEDED,
            'changedTo' => $override === null ? null : FinancialEvidenceAssessmentTab::FLAG_PASS,
            'changedToTag' => $override === null ? null : FinancialEvidenceAssessmentTab::flagTag(FinancialEvidenceAssessmentTab::FLAG_PASS),
            'changedBy' => $override['changedBy'] ?? null,
        ];

        $tableRows[] = ['type' => self::ROW_REMARK, 'heading' => 'Remark', 'text' => $row['remark']];

        if ($override !== null) {
            $tableRows[] = [
                'type' => self::ROW_COMMENT,
                'heading' => 'Comment',
                'changedBy' => $override['changedBy'],
                'text' => $override['comment'],
            ];
        }

        return [
            'key' => $row['key'],
            'label' => $row['label'],
            'changed' => $override !== null,
            'tableRows' => $tableRows,
        ];
    }
}

