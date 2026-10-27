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
