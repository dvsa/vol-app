<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser;

/**
 * Maps the AI analysis report (document_analysis.result, as the pipeline produced it) onto the
 * current NormalisedResult: one row per line on the assessment tab, keyed by a stable name.
 *
 * This is the only place that knows the model's output format and the FI check to category
 * mapping. It always produces the current payload version; stored payloads of earlier versions
 * are lifted by the Version mappers instead, never re-mapped from the report.
 *
 * FI check codes are internal identifiers. They are kept as keys under "checks" for
 * traceability back to the raw report, and must not be shown to caseworkers.
 *
 * Nothing validates the shape of the report when it is stored, and reports from earlier prompt
 * versions lack the statement details and category remarks, so every read here is defensive:
 * anything unreadable is dropped, never guessed at.
 */
class ReportMapper
{
    /**
     * Category rows in display order, each with its FI checks in the order the remark fallback
     * considers them. The mapping comes from Service Design's category sheet.
     */
    private const array CATEGORY_CHECKS = [
        'authenticity' => ['FI01', 'FI03', 'FI04'],
        'name' => ['FI06', 'FI07', 'FI08'],
        'statementDate' => ['FI10'],
        'statementPeriod' => ['FI05'],
        'averageFunds' => ['FI09', 'FI02'],
        'largeDeposit' => ['FI16'],
    ];

    /** The analysis output is snake_case like the rest of the tool schema; row keys are ours. */
    private const array CATEGORY_REMARK_KEYS = [
        'authenticity' => 'authenticity',
        'name' => 'name',
        'statementDate' => 'statement_date',
        'statementPeriod' => 'statement_period',
        'averageFunds' => 'average_funds',
        'largeDeposit' => 'large_deposit',
    ];

    /** Most telling first: a failure explains a row better than a pass, and a pass better than a skip. */
    private const array REMARK_FALLBACK_ORDER = [
        NormalisedResult::FLAG_FAIL,
        NormalisedResult::FLAG_PASS,
        NormalisedResult::FLAG_SKIPPED,
    ];

    /**
     * @param array $report the analysis report as stored in document_analysis.result:
     *                      {"metadata": {...}, "applicantProfile": {...}, "analysis": {...}}
     *
     * @return NormalisedResult|null null when the report holds no analysis to map
     */
    public function map(array $report): ?NormalisedResult
    {
        $analysis = $report['analysis'] ?? null;

        if (!is_array($analysis) || !is_array($analysis['core_checks'] ?? null)) {
            return null;
        }

        $checks = $analysis['core_checks'];
        $details = $this->arrayOrEmpty($analysis['statement_details'] ?? null);
        $remarks = $this->arrayOrEmpty($analysis['category_remarks'] ?? null);

        $rows = [
            'bank' => $this->informationRow($this->text($details['bank_name'] ?? null)),
            'bankAddress' => $this->informationRow($this->text($details['bank_address'] ?? null)),
        ];

        foreach (self::CATEGORY_CHECKS as $category => $codes) {
            $rows[$category] = $this->categoryRow($category, $codes, $checks, $details, $remarks);
        }

        return NormalisedResult::fromRows($rows);
    }

    /** Bank and bank address are shown for reference only: no check is carried out on them. */
    private function informationRow(?string $value): array
    {
        return ['flag' => null, 'remark' => null, 'value' => $value, 'checks' => []];
    }

    /**
     * @param list<string> $codes
     */
    private function categoryRow(string $category, array $codes, array $checks, array $details, array $remarks): array
    {
        $categoryChecks = [];

        foreach ($codes as $code) {
            $check = $this->readCheck($checks[$code] ?? null);

            if ($check !== null) {
                $categoryChecks[$code] = $check;
            }
        }

        $flag = $this->rollUp($categoryChecks);

        return [
            'flag' => $flag,
            'remark' => $this->text($remarks[self::CATEGORY_REMARK_KEYS[$category]] ?? null)
                ?? $this->fallbackRemark($categoryChecks),
            // A skipped category was never assessed, so the tab shows "-" whatever was extracted.
            'value' => $flag === NormalisedResult::FLAG_SKIPPED ? null : $this->valueFor($category, $details),
            'checks' => $categoryChecks,
        ];
    }

    /**
     * A check that cannot be read is dropped, which also stops it counting towards a pass.
     *
     * @return array{result: string, remark: string|null}|null
     */
    private function readCheck(mixed $check): ?array
    {
        if (!is_array($check) || !is_string($check['result'] ?? null)) {
            return null;
        }

        $result = strtolower(trim($check['result']));

        if (!in_array($result, [NormalisedResult::FLAG_PASS, NormalisedResult::FLAG_FAIL, NormalisedResult::FLAG_SKIPPED], true)) {
            return null;
        }

        return ['result' => $result, 'remark' => $this->text($check['remark'] ?? null)];
    }

    /**
     * Any failure fails the category. Skipped checks are neutral: FI08 is skipped for every
     * limited company and FI02 whenever FI09 calculates an average, so requiring every check to
     * pass would leave Name and Average Funds unable to pass at all.
     *
     * @param array<string, array{result: string, remark: string|null}> $categoryChecks
     */
    private function rollUp(array $categoryChecks): string
    {
        $results = array_column($categoryChecks, 'result');

        if (in_array(NormalisedResult::FLAG_FAIL, $results, true)) {
            return NormalisedResult::FLAG_FAIL;
        }

        return in_array(NormalisedResult::FLAG_PASS, $results, true) ? NormalisedResult::FLAG_PASS : NormalisedResult::FLAG_SKIPPED;
    }

    /**
     * For results stored before the prompt drafted a remark per category: borrow the check
     * remark that best explains the flag.
     *
     * @param array<string, array{result: string, remark: string|null}> $categoryChecks
     */
    private function fallbackRemark(array $categoryChecks): ?string
    {
        foreach (self::REMARK_FALLBACK_ORDER as $wanted) {
            foreach ($categoryChecks as $check) {
                if ($check['result'] === $wanted && $check['remark'] !== null) {
                    return $check['remark'];
                }
            }
        }

        return null;
    }

    private function valueFor(string $category, array $details): mixed
    {
        return match ($category) {
            'name' => $this->text($details['account_holder_name'] ?? null),
            'statementDate' => $this->date($details['statement_issue_date'] ?? null),
            'statementPeriod' => $this->period(
                $details['statement_period_start'] ?? null,
                $details['statement_period_end'] ?? null
            ),
            'averageFunds' => $this->amount($details['average_funds'] ?? null),
            'largeDeposit' => $this->count($details['large_deposit_count'] ?? null),
            // Authenticity is a judgement on the document as a whole; there is no single value to show.
            default => null,
        };
    }

    private function arrayOrEmpty(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    private function text(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /** ISO dates only, and only real ones: PHP would otherwise roll 2026-02-30 over into March. */
    private function date(mixed $value): ?string
    {
        $value = $this->text($value);

        if ($value === null) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $value : null;
    }

    /**
     * Half a period is not a period, so both ends must be readable.
     *
     * @return array{start: string, end: string}|null
     */
    private function period(mixed $start, mixed $end): ?array
    {
        $start = $this->date($start);
        $end = $this->date($end);

        return $start !== null && $end !== null ? ['start' => $start, 'end' => $end] : null;
    }

    private function amount(mixed $value): ?float
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (is_bool($value) || !is_numeric($value)) {
            return null;
        }

        $amount = (float)$value;

        return is_finite($amount) ? $amount : null;
    }

    /** A whole number of deposits, zero included: "0" is a meaningful value for a passing row. */
    private function count(mixed $value): ?int
    {
        $amount = $this->amount($value);

        if ($amount === null || $amount < 0 || floor($amount) !== $amount) {
            return null;
        }

        return (int)$amount;
    }
}
