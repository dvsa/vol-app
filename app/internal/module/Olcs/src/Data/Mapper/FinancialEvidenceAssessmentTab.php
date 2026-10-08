<?php

declare(strict_types=1);

namespace Olcs\Data\Mapper;

/**
 * Maps one row of DocumentAnalysisList (as the API returns it) onto the content of one tab on
 * the financial evidence assessment page: the document link, the eight summary rows and the
 * issue count.
 *
 * The API's resultNormalised payload is keyed by stable row names with a flag, a remark and a
 * typed value per row (see NormalisedResult in the API). This mapper owns everything
 * about how those appear: labels, date and money formatting, tag colours, the dash for a
 * missing value. It returns plain text throughout; the view escapes it, since remarks and
 * values are model-generated.
 */
final class FinancialEvidenceAssessmentTab
{
    public const string FLAG_PASS = 'PASS';
    public const string FLAG_FAIL = 'FAIL';
    public const string FLAG_SKIPPED = 'SKIPPED';

    /** Shown when a row carries no value, so the caseworker can see it was not found or not assessed. */
    private const string NO_VALUE = '-';

    /**
     * The payload version this mapper reads (NormalisedResult::VERSION in the API). The
     * API upcasts stored rows to the version it emits, so another version only reaches here when
     * the API has been deployed ahead of this app. It is shown as no assessment rather than
     * rendered, because a flag in an unknown shape could mean something else.
     */
    private const int PAYLOAD_VERSION = 1;

    /**
     * The rows in display order. Bank and bank address are information only, so they are never
     * flagged; Authenticity is a judgement on the whole document, so it has no value to show.
     */
    private const array ROWS = [
        'bank' => ['label' => 'Bank', 'flagged' => false, 'hasValue' => true],
        'bankAddress' => ['label' => 'Bank address', 'flagged' => false, 'hasValue' => true],
        'authenticity' => ['label' => 'Authenticity', 'flagged' => true, 'hasValue' => false],
        'name' => ['label' => 'Name', 'flagged' => true, 'hasValue' => true],
        'statementDate' => ['label' => 'Statement date', 'flagged' => true, 'hasValue' => true],
        'statementPeriod' => ['label' => 'Statement period', 'flagged' => true, 'hasValue' => true],
        'averageFunds' => ['label' => 'Average funds', 'flagged' => true, 'hasValue' => true],
        'largeDeposit' => ['label' => 'Large deposit', 'flagged' => true, 'hasValue' => true],
    ];

    /** Anything else the API might send is shown as skipped: it can never read as a pass or a fail. */
    private const array FLAG_TAGS = [
        'pass' => [self::FLAG_PASS, 'govuk-tag--green'],
        'fail' => [self::FLAG_FAIL, 'govuk-tag--red'],
        'skipped' => [self::FLAG_SKIPPED, 'govuk-tag--grey'],
    ];

    /** A flag a caseworker has changed is greyed out, so the change reads as the one that counts. */
    public const string FLAG_TAG_SUPERSEDED = 'govuk-tag--grey';

    /**
     * @param array $analysis one entry of DocumentAnalysisList's "analyses"
     *
     * @return array{
     *     document: array{id: int, name: string|null}|null,
     *     hasAssessment: bool,
     *     rows: list<array{
     *         key: string,
     *         flagged: bool,
     *         label: string,
     *         value: string|null,
     *         flag: string|null,
     *         flagTag: string|null,
     *         remark: string|null,
     *         override: array{originalFlag: string, comment: string|null, changedBy: string|null, changedOn: string|null}|null
     *     }>,
     *     issueCount: int
     * }
     */
    public static function mapFromAnalysis(array $analysis): array
    {
        $payload = $analysis['resultNormalised'] ?? null;
        $payloadRows = $payload['rows'] ?? null;

        if (($payload['version'] ?? null) !== self::PAYLOAD_VERSION || !is_array($payloadRows)) {
            return [
                'document' => self::document($analysis),
                'hasAssessment' => false,
                'rows' => [],
                'issueCount' => 0,
            ];
        }

        $rows = [];

        foreach (self::ROWS as $key => $definition) {
            $rows[] = self::row($key, $definition, $payloadRows[$key] ?? []);
        }

        return [
            'document' => self::document($analysis),
            'hasAssessment' => true,
            'rows' => $rows,
            'issueCount' => count(array_filter($rows, static fn(array $row): bool => $row['flag'] === self::FLAG_FAIL)),
        ];
    }

    /** The tag colour for a flag label (PASS, FAIL or SKIPPED) as this mapper produces it. */
    public static function flagTag(string $flag): string
    {
        foreach (self::FLAG_TAGS as [$label, $tag]) {
            if ($label === $flag) {
                return $tag;
            }
        }

        return self::FLAG_TAGS['skipped'][1];
    }

    /**
     * @param array{label: string, flagged: bool, hasValue: bool} $definition
     */
    private static function row(string $key, array $definition, mixed $payloadRow): array
    {
        $payloadRow = is_array($payloadRow) ? $payloadRow : [];

        [$flag, $flagTag] = $definition['flagged']
            ? self::FLAG_TAGS[$payloadRow['flag'] ?? null] ?? self::FLAG_TAGS['skipped']
            : [null, null];

        $value = $definition['hasValue']
            ? self::formatValue($key, $payloadRow['value'] ?? null) ?? self::NO_VALUE
            : null;

        $remark = $payloadRow['remark'] ?? null;

        return [
            'key' => $key,
            'flagged' => $definition['flagged'],
            'label' => $definition['label'],
            'value' => $value,
            'flag' => $flag,
            'flagTag' => $flagTag,
            'remark' => is_string($remark) && $remark !== '' ? $remark : null,
            'override' => $definition['flagged'] ? self::override($payloadRow['override'] ?? null) : null,
        ];
    }

    /**
     * A caseworker's change to the row, as the API lays it over the analyser's result: the flag
     * it replaced and why. Null when the row is as the analyser left it.
     *
     * @return array{originalFlag: string, comment: string|null, changedBy: string|null, changedOn: string|null}|null
     */
    private static function override(mixed $override): ?array
    {
        if (!is_array($override)) {
            return null;
        }

        [$originalFlag] = self::FLAG_TAGS[$override['originalFlag'] ?? null] ?? self::FLAG_TAGS['skipped'];

        $text = static fn(mixed $value): ?string => is_string($value) && trim($value) !== '' ? trim($value) : null;
        $changedOn = $text($override['changedOn'] ?? null);
        $changedOnDate = $changedOn === null ? false : \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $changedOn);

        return [
            'originalFlag' => $originalFlag,
            'comment' => $text($override['comment'] ?? null),
            'changedBy' => $text($override['changedBy'] ?? null),
            'changedOn' => $changedOnDate === false ? null : $changedOnDate->format('d/m/Y'),
        ];
    }

    private static function formatValue(string $key, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($key) {
            'statementDate' => self::date($value),
            'statementPeriod' => self::period($value),
            'averageFunds' => self::sterling($value),
            'largeDeposit' => is_int($value) ? (string)$value : null,
            default => is_string($value) && $value !== '' ? $value : null,
        };
    }

    /** The API sends ISO dates; caseworkers read d/m/Y everywhere else on the page. */
    private static function date(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date === false ? null : $date->format('d/m/Y');
    }

    private static function period(mixed $value): ?string
    {
        if (!is_array($value)) {
            return null;
        }

        $start = self::date($value['start'] ?? null);
        $end = self::date($value['end'] ?? null);

        return $start !== null && $end !== null ? sprintf('%s - %s', $start, $end) : null;
    }

    /** Whole pounds read as "£15,321"; pence are only shown when there are some. */
    private static function sterling(mixed $value): ?string
    {
        if (!is_int($value) && !is_float($value)) {
            return null;
        }

        $formatted = number_format(abs($value), 2);

        if (str_ends_with($formatted, '.00')) {
            $formatted = substr($formatted, 0, -3);
        }

        return ($value < 0 ? '-' : '') . '£' . $formatted;
    }

    /**
     * The link text prefers the description a caseworker gave the document; an upload without
     * one falls back to the file's own name. The filename column holds a path on disk.
     *
     * @return array{id: int, name: string|null}|null
     */
    private static function document(array $analysis): ?array
    {
        $id = $analysis['documentId'] ?? null;

        if (!is_int($id)) {
            return null;
        }

        $description = $analysis['documentDescription'] ?? null;
        $filename = $analysis['documentFilename'] ?? null;

        if (is_string($description) && trim($description) !== '') {
            $name = trim($description);
        } elseif (is_string($filename) && trim($filename) !== '') {
            $name = basename(trim($filename));
        } else {
            $name = null;
        }

        return ['id' => $id, 'name' => $name];
    }
}
