<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Olcs\Data\Mapper\FinancialEvidenceAssessmentReview;
use PHPUnit\Framework\TestCase;

/**
 * The change document review page shows each issue as a short key/value list (the infringing
 * value, the flag with its remark, and the caseworker's comment) and the checks that passed.
 */
final class FinancialEvidenceAssessmentReviewTest extends TestCase
{
    public function testFailedAndSkippedChecksAreIssuesAndTheRestArePasses(): void
    {
        $page = FinancialEvidenceAssessmentReview::mapFromAnalysis($this->givenAnalysis([
            'statementDate' => ['flag' => 'fail', 'remark' => 'Statement is over 2 months old', 'value' => '2025-12-29'],
            'largeDeposit' => ['flag' => 'skipped', 'remark' => 'Could not read deposits', 'value' => 1],
        ]));

        $this->assertSame(['statementDate', 'largeDeposit'], array_column($page['issues'], 'key'));
        $this->assertSame(
            ['Bank', 'Bank address', 'Authenticity', 'Name', 'Statement period', 'Average funds'],
            array_column($page['passes'], 'label')
        );
        // Bank and bank address carry no flag, so they are not counted as passes.
        $this->assertSame(4, $page['passCount']);
        $this->assertSame(2, $page['unchangedIssueCount']);
    }

    /**
     * Passes carry their row key, remark and whether they are a flagged check, so the change
     * document review page can offer a pass-to-fail form for the flagged ones (not bank/bank
     * address, which are reference-only).
     */
    public function testPassesCarryTheirKeyRemarkAndFlaggedStateForChangingToAFail(): void
    {
        $page = FinancialEvidenceAssessmentReview::mapFromAnalysis($this->givenAnalysis([
            'authenticity' => ['flag' => 'pass', 'remark' => 'Looks genuine', 'value' => null],
        ]));

        $authenticity = array_values(array_filter($page['passes'], static fn(array $pass): bool => $pass['key'] === 'authenticity'))[0];
        $bank = array_values(array_filter($page['passes'], static fn(array $pass): bool => $pass['key'] === 'bank'))[0];

        $this->assertTrue($authenticity['flagged']);
        $this->assertSame('Looks genuine', $authenticity['remark']);
        $this->assertFalse($bank['flagged']);
    }

    public function testAnUnchangedIssueShowsItsValueFlagRemarkAndNoComment(): void
    {
        $page = FinancialEvidenceAssessmentReview::mapFromAnalysis($this->givenAnalysis([
            'statementDate' => ['flag' => 'fail', 'remark' => 'Statement is over 2 months old', 'value' => '2025-12-29'],
        ]));

        $this->assertSame(
            [
                'key' => 'statementDate',
                'label' => 'Statement date',
                'value' => '29/12/2025',
                'flag' => 'FAIL',
                'flagTag' => 'govuk-tag--red',
                'remark' => 'Statement is over 2 months old',
                'comment' => null,
                'changed' => false,
                'kept' => false,
                'changedTo' => null,
                'changedToTag' => null,
                'changedBy' => null,
            ],
            $page['issues'][0]
        );
    }

    public function testAChangedIssueKeepsItsOriginalFlagGreyedOutAndCarriesTheComment(): void
    {
        $page = FinancialEvidenceAssessmentReview::mapFromAnalysis($this->givenAnalysis([
            'name' => [
                'flag' => 'pass',
                'remark' => 'Name does not match',
                'value' => 'Example Haulage',
                'override' => [
                    'originalFlag' => 'fail',
                    'comment' => 'Trading name, checked with Companies House',
                    'changedBy' => 'Ashley Young',
                    'changedOn' => '2026-10-08 10:00:00',
                ],
            ],
        ]));

        $issue = $page['issues'][0];

        $this->assertSame('FAIL', $issue['flag']);
        $this->assertSame('govuk-tag--grey', $issue['flagTag']);
        $this->assertSame('PASS', $issue['changedTo']);
        $this->assertSame('govuk-tag--green', $issue['changedToTag']);
        $this->assertSame('Ashley Young', $issue['changedBy']);
        $this->assertSame('Trading name, checked with Companies House', $issue['comment']);
        $this->assertTrue($issue['changed']);
        $this->assertSame(0, $page['unchangedIssueCount']);
    }

    public function testValuesThatDoNotNameTheInfringingDataAreLeftOut(): void
    {
        $page = FinancialEvidenceAssessmentReview::mapFromAnalysis($this->givenAnalysis([
            'authenticity' => ['flag' => 'fail', 'remark' => 'Possible tampering', 'value' => null],
            'largeDeposit' => ['flag' => 'fail', 'remark' => 'One or more large deposits', 'value' => 2],
            'averageFunds' => ['flag' => 'fail', 'remark' => 'Below the required amount', 'value' => null],
        ]));

        // Authenticity has no value, large deposit only a count, and a missing value shows nothing.
        $this->assertSame([null, null, null], array_column($page['issues'], 'value'));
    }

    /**
     * A genuine pass changed to a fail shows the fail it was changed to, not a pass regardless of
     * direction: changedTo must follow the row's own flag, not be fixed to one value.
     */
    public function testAPassChangedToAFailShowsTheFailItWasChangedToNotAlwaysPass(): void
    {
        $page = FinancialEvidenceAssessmentReview::mapFromAnalysis($this->givenAnalysis([
            'authenticity' => [
                'flag' => 'fail',
                'remark' => 'Possible tampering',
                'value' => null,
                'override' => [
                    'originalFlag' => 'pass',
                    'comment' => 'Reconsidered after a second look',
                    'changedBy' => 'Sam Taylor',
                    'changedOn' => '2026-10-08 10:00:00',
                ],
            ],
        ]));

        $issue = $page['issues'][0];

        $this->assertSame('PASS', $issue['flag']);
        $this->assertSame('govuk-tag--grey', $issue['flagTag']);
        $this->assertTrue($issue['changed']);
        $this->assertSame('FAIL', $issue['changedTo']);
        $this->assertSame('govuk-tag--red', $issue['changedToTag']);
        $this->assertSame('Sam Taylor', $issue['changedBy']);
        $this->assertSame('Reconsidered after a second look', $issue['comment']);
    }

    /**
     * An override that leaves the row's own flag unchanged (a note - "No, but here is why") stays
     * a pass, carrying its comment, rather than being promoted to an issue.
     */
    public function testAPassWithOnlyANoteStaysAPassAndCarriesItsComment(): void
    {
        $page = FinancialEvidenceAssessmentReview::mapFromAnalysis($this->givenAnalysis([
            'statementDate' => [
                'flag' => 'pass',
                'remark' => null,
                'value' => '2026-09-30',
                'override' => [
                    'originalFlag' => 'pass',
                    'comment' => 'Looks fine, checked again',
                    'changedBy' => 'Jo Bloggs',
                    'changedOn' => '2026-10-08 10:00:00',
                ],
            ],
        ]));

        $this->assertNotContains('statementDate', array_column($page['issues'], 'key'));

        $pass = array_values(array_filter($page['passes'], static fn(array $pass): bool => $pass['key'] === 'statementDate'))[0];

        $this->assertSame('PASS', $pass['flag']);
        $this->assertSame('Looks fine, checked again', $pass['comment']);
    }

    /**
     * A note left on an unresolved issue (flag unchanged) says the flag was kept, by whom - not
     * silence, and not a false claim that it changed.
     */
    public function testANoteOnAnUnresolvedIssueShowsItWasKept(): void
    {
        $page = FinancialEvidenceAssessmentReview::mapFromAnalysis($this->givenAnalysis([
            'statementDate' => [
                'flag' => 'fail',
                'remark' => 'Statement is over 2 months old',
                'value' => '2025-12-29',
                'override' => [
                    'originalFlag' => 'fail',
                    'comment' => 'Checked with the bank directly, still a genuine fail',
                    'changedBy' => 'Ashley Young',
                    'changedOn' => '2026-10-08 10:00:00',
                ],
            ],
        ]));

        $issue = $page['issues'][0];

        $this->assertFalse($issue['changed']);
        $this->assertTrue($issue['kept']);
        $this->assertSame('FAIL', $issue['changedTo']);
        $this->assertSame('govuk-tag--red', $issue['changedToTag']);
        $this->assertSame('Ashley Young', $issue['changedBy']);
        $this->assertSame('Checked with the bank directly, still a genuine fail', $issue['comment']);
        // Still unresolved: a note does not count as the issue having been fixed.
        $this->assertSame(1, $page['unchangedIssueCount']);
    }

    /**
     * A successful analysis whose flagged rows all pass unless overridden by $rows.
     */
    private function givenAnalysis(array $rows = []): array
    {
        $pass = static fn(mixed $value): array => ['flag' => 'pass', 'remark' => null, 'value' => $value];

        return [
            'id' => 7,
            'documentId' => 42,
            'documentDescription' => 'RSBankStatement234.pdf',
            'resultNormalised' => [
                'version' => 1,
                'rows' => $rows + [
                    'bank' => ['value' => 'Nationwide'],
                    'bankAddress' => ['value' => '34 Green Park, Manchester'],
                    'authenticity' => $pass(null),
                    'name' => $pass('Example Haulage'),
                    'statementDate' => $pass('2026-09-30'),
                    'statementPeriod' => $pass(['start' => '2026-08-01', 'end' => '2026-08-31']),
                    'averageFunds' => $pass(16845),
                    'largeDeposit' => $pass(0),
                ],
            ],
        ];
    }
}
