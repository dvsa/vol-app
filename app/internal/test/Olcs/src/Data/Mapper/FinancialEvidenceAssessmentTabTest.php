<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Olcs\Data\Mapper\FinancialEvidenceAssessmentTab;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The mapper turns one analysis row from DocumentAnalysisList into the content of one tab
 * panel: the document link, the eight summary rows with display-ready values and flags, and
 * the issue count. Everything it returns is plain text; the view escapes it.
 */
final class FinancialEvidenceAssessmentTabTest extends TestCase
{
    public function testMapsTheEightRowsInDisplayOrderWithTheirLabels(): void
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($this->givenAnalysis());

        $this->assertSame(
            [
                'Bank',
                'Bank address',
                'Authenticity',
                'Name',
                'Statement date',
                'Statement period',
                'Average funds',
                'Large deposit',
            ],
            array_column($tab['rows'], 'label')
        );
        $this->assertTrue($tab['hasAssessment']);
    }

    public function testEveryRowHasTheSameShape(): void
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($this->givenAnalysis());

        foreach ($tab['rows'] as $row) {
            $this->assertSame(['label', 'value', 'flag', 'flagTag', 'remark'], array_keys($row), $row['label']);
        }
    }

    public function testFormatsEachValueForDisplay(): void
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($this->givenAnalysis());

        $this->assertSame(
            [
                'Example Bank',
                '1 Example Street, Exampleton EX1 1EX',
                null,
                'Example Haulage Ltd',
                '31/08/2026',
                '01/08/2026 - 31/08/2026',
                '£15,321.50',
                '2',
            ],
            array_column($tab['rows'], 'value')
        );
    }

    #[DataProvider('currencyProvider')]
    public function testFormatsAverageFundsAsSterling(float|int $amount, string $expected): void
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($this->givenAnalysis([
            'averageFunds' => ['flag' => 'pass', 'remark' => null, 'value' => $amount, 'checks' => []],
        ]));

        $this->assertSame($expected, $this->row($tab, 'Average funds')['value']);
    }

    public static function currencyProvider(): array
    {
        return [
            'whole pounds drop the pence' => [15321.0, '£15,321'],
            'pence are kept when present' => [15321.5, '£15,321.50'],
            'integers are accepted' => [800, '£800'],
            'rounded to the penny' => [1234.567, '£1,234.57'],
            // An overdrawn average is a real outcome: the minus goes before the pound sign.
            'negative amounts' => [-2000.0, '-£2,000'],
        ];
    }

    public function testMapsFlagsToTagsAndLeavesInformationRowsUnflagged(): void
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($this->givenAnalysis([
            'authenticity' => ['flag' => 'pass', 'remark' => 'Fine.', 'value' => null, 'checks' => []],
            'name' => ['flag' => 'fail', 'remark' => 'Wrong name.', 'value' => 'Someone Else', 'checks' => []],
            'statementDate' => ['flag' => 'skipped', 'remark' => 'Not assessed.', 'value' => null, 'checks' => []],
        ]));

        $this->assertSame([null, null], [$this->row($tab, 'Bank')['flag'], $this->row($tab, 'Bank address')['flag']]);
        $this->assertSame(['PASS', 'govuk-tag--green'], $this->flagOf($tab, 'Authenticity'));
        $this->assertSame(['FAIL', 'govuk-tag--red'], $this->flagOf($tab, 'Name'));
        $this->assertSame(['SKIPPED', 'govuk-tag--grey'], $this->flagOf($tab, 'Statement date'));
    }

    /** A flag the view does not know cannot be shown as a pass or a fail. */
    public function testAnUnknownFlagIsShownAsSkipped(): void
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($this->givenAnalysis([
            'authenticity' => ['flag' => 'maybe', 'remark' => null, 'value' => null, 'checks' => []],
        ]));

        $this->assertSame(['SKIPPED', 'govuk-tag--grey'], $this->flagOf($tab, 'Authenticity'));
    }

    public function testCarriesRemarksThrough(): void
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($this->givenAnalysis());

        $this->assertSame('Statement is too old.', $this->row($tab, 'Statement date')['remark']);
        $this->assertNull($this->row($tab, 'Bank')['remark']);
    }

    /** A missing value shows as a dash, except for Authenticity, which has no value to show. */
    public function testShowsADashForAMissingValue(): void
    {
        $analysis = $this->givenAnalysis();

        foreach ($analysis['resultNormalised']['rows'] as &$row) {
            $row['value'] = null;
        }

        unset($row);

        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis);

        $this->assertSame(['-', '-', null, '-', '-', '-', '-', '-'], array_column($tab['rows'], 'value'));
    }

    /** Every row is always shown, so a payload missing one renders as not assessed rather than vanishing. */
    public function testFillsInRowsMissingFromThePayload(): void
    {
        $analysis = $this->givenAnalysis();
        unset($analysis['resultNormalised']['rows']['largeDeposit'], $analysis['resultNormalised']['rows']['bank']);

        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis);

        $this->assertCount(8, $tab['rows']);
        $this->assertSame(['SKIPPED', 'govuk-tag--grey'], $this->flagOf($tab, 'Large deposit'));
        $this->assertSame('-', $this->row($tab, 'Bank')['value']);
        $this->assertNull($this->row($tab, 'Bank')['flag']);
    }

    public function testIgnoresRowsItDoesNotKnow(): void
    {
        $analysis = $this->givenAnalysis();
        $analysis['resultNormalised']['rows']['somethingNew'] = ['flag' => 'fail', 'remark' => 'New.', 'value' => 1, 'checks' => []];

        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis);

        $this->assertCount(8, $tab['rows']);
        $this->assertSame(2, $tab['issueCount']);
    }

    public function testCountsFailedRowsAsIssues(): void
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($this->givenAnalysis());

        $this->assertSame(2, $tab['issueCount']);
    }

    public function testLinksTheDocumentByItsDescription(): void
    {
        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($this->givenAnalysis());

        $this->assertSame(['id' => 11, 'name' => 'Bank statement August 2026'], $tab['document']);
    }

    /** Uploads often have no description; the filename on disk is a path, so only its last part is shown. */
    public function testFallsBackToTheFilenameWhenThereIsNoDescription(): void
    {
        $analysis = $this->givenAnalysis();
        $analysis['documentDescription'] = null;
        $analysis['documentFilename'] = 'documents/2026/09/statement-august.pdf';

        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis);

        $this->assertSame(['id' => 11, 'name' => 'statement-august.pdf'], $tab['document']);
    }

    public function testHasNoDocumentLinkWithoutADocumentId(): void
    {
        $analysis = $this->givenAnalysis();
        unset($analysis['documentId']);

        $this->assertNull(FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis)['document']);
    }

    /**
     * The API upcasts stored payloads to the version it currently emits, so this only happens
     * when the API is deployed ahead of the internal app. Rendering an unknown shape could show
     * a flag that means something else, so the tab shows no assessment instead.
     */
    public function testAPayloadVersionItDoesNotKnowHasNoAssessment(): void
    {
        $analysis = $this->givenAnalysis();
        $analysis['resultNormalised']['version'] = 2;

        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis);

        $this->assertFalse($tab['hasAssessment']);
        $this->assertSame([], $tab['rows']);
        $this->assertSame(11, $tab['document']['id']);
    }

    /** A stored report the API could not normalise: the tab says so instead of showing an empty table. */
    public function testAnAnalysisWithoutANormalisedResultHasNoAssessment(): void
    {
        $analysis = $this->givenAnalysis();
        $analysis['resultNormalised'] = null;

        $tab = FinancialEvidenceAssessmentTab::mapFromAnalysis($analysis);

        $this->assertFalse($tab['hasAssessment']);
        $this->assertSame([], $tab['rows']);
        $this->assertSame(0, $tab['issueCount']);
        // The document is still linked so the caseworker can assess it by hand.
        $this->assertSame(11, $tab['document']['id']);
    }

    private function row(array $tab, string $label): array
    {
        foreach ($tab['rows'] as $row) {
            if ($row['label'] === $label) {
                return $row;
            }
        }

        $this->fail(sprintf('No row labelled "%s"', $label));
    }

    private function flagOf(array $tab, string $label): array
    {
        $row = $this->row($tab, $label);

        return [$row['flag'], $row['flagTag']];
    }

    /** One row of DocumentAnalysisList as the API returns it, with two failures. */
    private function givenAnalysis(array $rowOverrides = []): array
    {
        $rows = [
            'bank' => ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []],
            'bankAddress' => ['flag' => null, 'remark' => null, 'value' => '1 Example Street, Exampleton EX1 1EX', 'checks' => []],
            'authenticity' => ['flag' => 'pass', 'remark' => 'All key fields present.', 'value' => null, 'checks' => []],
            'name' => ['flag' => 'pass', 'remark' => 'Correct entity.', 'value' => 'Example Haulage Ltd', 'checks' => []],
            'statementDate' => ['flag' => 'fail', 'remark' => 'Statement is too old.', 'value' => '2026-08-31', 'checks' => []],
            'statementPeriod' => [
                'flag' => 'pass',
                'remark' => 'Covers 31 days.',
                'value' => ['start' => '2026-08-01', 'end' => '2026-08-31'],
                'checks' => [],
            ],
            'averageFunds' => ['flag' => 'pass', 'remark' => 'Average exceeds required.', 'value' => 15321.5, 'checks' => []],
            'largeDeposit' => ['flag' => 'fail', 'remark' => 'Two deposits exceed the threshold.', 'value' => 2, 'checks' => []],
        ];

        return [
            'id' => 1,
            'documentId' => 11,
            'documentDescription' => 'Bank statement August 2026',
            'documentFilename' => 'documents/2026/09/statement-august.pdf',
            'documentDate' => '2026-08-31 00:00:00',
            'status' => 'SUCCESS',
            'resultNormalised' => ['version' => 1, 'rows' => array_replace($rows, $rowOverrides)],
            'errorDetail' => null,
            'completedAt' => '2026-09-01 09:00:00',
        ];
    }
}
