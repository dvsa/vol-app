<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Idp\AnalysisResultNormaliser;

use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\ReportMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The normalised payload is the contract the internal assessment tab renders and the one
 * caseworker annotations are later merged over, so these tests pin its shape as well as
 * the FI check to category mapping. They go through toArray() because the array is the
 * contract: it is what is stored and what leaves the API. All fixture data is synthetic.
 */
final class ReportMapperTest extends TestCase
{
    private ReportMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new ReportMapper();
    }

    public function testStampsThePayloadVersion(): void
    {
        $normalised = $this->normalise($this->givenResult($this->typicalChecks()));

        $this->assertSame(1, $normalised['version']);
    }

    public function testProducesTheRowsInDisplayOrder(): void
    {
        $normalised = $this->normalise($this->givenResult($this->typicalChecks()));

        $this->assertSame(
            [
                'bank',
                'bankAddress',
                'authenticity',
                'name',
                'statementDate',
                'statementPeriod',
                'averageFunds',
                'largeDeposit',
            ],
            array_keys($normalised['rows'])
        );
    }

    /** One shape for every row keeps the view loop and the per-row annotation merge trivial. */
    public function testEveryRowHasTheSameShape(): void
    {
        $normalised = $this->normalise($this->givenResult($this->typicalChecks()));

        foreach ($normalised['rows'] as $key => $row) {
            $this->assertSame(['flag', 'remark', 'value', 'checks'], array_keys($row), $key);
        }
    }

    public function testGroupsChecksUnderTheirCategory(): void
    {
        $normalised = $this->normalise($this->givenResult($this->typicalChecks()));

        $this->assertSame(
            [
                'bank' => [],
                'bankAddress' => [],
                'authenticity' => ['FI01', 'FI03', 'FI04'],
                'name' => ['FI06', 'FI07', 'FI08'],
                'statementDate' => ['FI10'],
                'statementPeriod' => ['FI05'],
                'averageFunds' => ['FI09', 'FI02'],
                'largeDeposit' => ['FI16'],
            ],
            array_map(static fn(array $row): array => array_keys($row['checks']), $normalised['rows'])
        );
    }

    /** workingOut stays in the raw result column; the normalised payload only carries what is shown. */
    public function testCheckEntriesCarryResultAndRemarkOnly(): void
    {
        $normalised = $this->normalise($this->givenResult([
            'FI10' => ['result' => 'Fail', 'workingOut' => ['step one', 'step two'], 'remark' => 'Too old.'],
        ]));

        $this->assertSame(
            ['FI10' => ['result' => 'fail', 'remark' => 'Too old.']],
            $normalised['rows']['statementDate']['checks']
        );
    }

    /**
     * "All must pass" cannot be read literally: FI08 is skipped for every limited company and
     * FI02 is skipped whenever FI09 calculates an average, so skipped checks have to be neutral.
     */
    #[DataProvider('flagRollupProvider')]
    public function testRollsCheckResultsUpIntoTheCategoryFlag(array $checks, string $expectedFlag): void
    {
        $normalised = $this->normalise($this->givenResult($checks));

        $this->assertSame($expectedFlag, $normalised['rows']['name']['flag']);
    }

    public static function flagRollupProvider(): array
    {
        return [
            'all pass' => [
                ['FI06' => self::check('Pass'), 'FI07' => self::check('Pass'), 'FI08' => self::check('Pass')],
                'pass',
            ],
            'pass with a skipped check' => [
                ['FI06' => self::check('Pass'), 'FI07' => self::check('Pass'), 'FI08' => self::check('Skipped')],
                'pass',
            ],
            'one fail among passes' => [
                ['FI06' => self::check('Pass'), 'FI07' => self::check('Fail'), 'FI08' => self::check('Pass')],
                'fail',
            ],
            'fail with a skipped check' => [
                ['FI06' => self::check('Skipped'), 'FI07' => self::check('Fail'), 'FI08' => self::check('Skipped')],
                'fail',
            ],
            'all skipped' => [
                ['FI06' => self::check('Skipped'), 'FI07' => self::check('Skipped'), 'FI08' => self::check('Skipped')],
                'skipped',
            ],
            'no checks returned for the category' => [
                ['FI10' => self::check('Pass')],
                'skipped',
            ],
        ];
    }

    #[DataProvider('resultSpellingProvider')]
    public function testReadsCheckResultsCaseInsensitively(string $result, string $expected): void
    {
        $normalised = $this->normalise($this->givenResult(['FI10' => self::check($result)]));

        $this->assertSame($expected, $normalised['rows']['statementDate']['checks']['FI10']['result']);
    }

    public static function resultSpellingProvider(): array
    {
        return [
            'schema casing' => ['Pass', 'pass'],
            'upper case' => ['FAIL', 'fail'],
            'padded' => [' Skipped ', 'skipped'],
        ];
    }

    /**
     * Nothing validates the result shape when it is stored, so a check the normaliser cannot
     * read is dropped. Dropping it means it can never count towards a pass.
     */
    #[DataProvider('unreadableCheckProvider')]
    public function testDropsChecksItCannotRead(mixed $check): void
    {
        $normalised = $this->normalise($this->givenResult(['FI10' => $check]));

        $this->assertSame([], $normalised['rows']['statementDate']['checks']);
        $this->assertSame('skipped', $normalised['rows']['statementDate']['flag']);
    }

    public static function unreadableCheckProvider(): array
    {
        return [
            'unrecognised result' => [['result' => 'Maybe', 'remark' => 'Unsure.']],
            'missing result' => [['remark' => 'No result given.']],
            'non-string result' => [['result' => true, 'remark' => 'Boolean result.']],
            'not an object' => ['Pass'],
        ];
    }

    public function testIgnoresCheckCodesOutsideTheMapping(): void
    {
        $normalised = $this->normalise($this->givenResult(
            $this->typicalChecks() + ['FI11' => self::check('Fail', 'Not one of ours.')]
        ));

        $this->assertStringNotContainsString('FI11', json_encode($normalised, JSON_THROW_ON_ERROR));
    }

    public function testUsesTheCategoryRemarkDraftedByTheAnalysis(): void
    {
        $normalised = $this->normalise($this->givenResult(
            $this->typicalChecks(),
            remarks: ['name' => 'Account holder is the operator.']
        ));

        $this->assertSame('Account holder is the operator.', $normalised['rows']['name']['remark']);
    }

    /**
     * Rows stored before the prompt drafted category remarks only have per-check remarks, so the
     * row borrows the one that best explains its flag: a failure first, then a pass, then a skip.
     */
    #[DataProvider('remarkFallbackProvider')]
    public function testFallsBackToACheckRemark(array $checks, ?string $expectedRemark): void
    {
        $normalised = $this->normalise($this->givenResult($checks));

        $this->assertSame($expectedRemark, $normalised['rows']['name']['remark']);
    }

    public static function remarkFallbackProvider(): array
    {
        return [
            'first failing check wins over an earlier pass' => [
                [
                    'FI06' => self::check('Pass', 'Name shown in full.'),
                    'FI07' => self::check('Fail', 'Different entity.'),
                    'FI08' => self::check('Fail', 'Declaration needed.'),
                ],
                'Different entity.',
            ],
            'first passing check when nothing failed' => [
                [
                    'FI06' => self::check('Skipped', 'Not evaluated.'),
                    'FI07' => self::check('Pass', 'Correct entity.'),
                    'FI08' => self::check('Pass', 'No declaration needed.'),
                ],
                'Correct entity.',
            ],
            'first skipped check when nothing was evaluated' => [
                [
                    'FI06' => self::check('Skipped', 'Skipped: no financial evidence.'),
                    'FI07' => self::check('Skipped', 'Skipped as well.'),
                ],
                'Skipped: no financial evidence.',
            ],
            'category order, not payload order, decides "first"' => [
                [
                    'FI08' => self::check('Fail', 'Declaration needed.'),
                    'FI06' => self::check('Fail', 'Name truncated.'),
                ],
                'Name truncated.',
            ],
            'no checks, no remark' => [
                ['FI10' => self::check('Pass')],
                null,
            ],
        ];
    }

    public function testABlankCategoryRemarkFallsBackToACheckRemark(): void
    {
        $normalised = $this->normalise($this->givenResult(
            ['FI10' => self::check('Fail', 'Statement is too old.')],
            remarks: ['statement_date' => '   ']
        ));

        $this->assertSame('Statement is too old.', $normalised['rows']['statementDate']['remark']);
    }

    public function testBankRowsAreInformationOnly(): void
    {
        $normalised = $this->normalise($this->givenResult(
            $this->typicalChecks(),
            details: ['bank_name' => ' Example Bank ', 'bank_address' => '1 Example Street, Exampleton EX1 1EX']
        ));

        $this->assertSame(
            ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []],
            $normalised['rows']['bank']
        );
        $this->assertSame(
            ['flag' => null, 'remark' => null, 'value' => '1 Example Street, Exampleton EX1 1EX', 'checks' => []],
            $normalised['rows']['bankAddress']
        );
    }

    public function testCarriesTheValueShownAgainstEachCategory(): void
    {
        $normalised = $this->normalise($this->givenResult($this->typicalChecks(), $this->typicalDetails()));

        $this->assertSame(
            [
                'bank' => 'Example Bank',
                'bankAddress' => '1 Example Street, Exampleton EX1 1EX',
                'authenticity' => null,
                'name' => 'Example Haulage Ltd',
                'statementDate' => '2026-08-31',
                'statementPeriod' => ['start' => '2026-08-01', 'end' => '2026-08-31'],
                'averageFunds' => 15321.5,
                'largeDeposit' => 2,
            ],
            array_map(static fn(array $row): mixed => $row['value'], $normalised['rows'])
        );
    }

    /** The model is asked for typed values, but the stored JSON is not validated, so each is checked. */
    #[DataProvider('unusableValueProvider')]
    public function testDiscardsValuesOfTheWrongShape(array $details, string $row): void
    {
        $normalised = $this->normalise($this->givenResult($this->typicalChecks(), $details));

        $this->assertNull($normalised['rows'][$row]['value']);
    }

    public static function unusableValueProvider(): array
    {
        return [
            'blank bank name' => [['bank_name' => '  '], 'bank'],
            'non-string bank address' => [['bank_address' => ['line one']], 'bankAddress'],
            'date in display format' => [['statement_issue_date' => '31/08/2026'], 'statementDate'],
            'impossible date' => [['statement_issue_date' => '2026-02-30'], 'statementDate'],
            'period missing its end' => [['statement_period_start' => '2026-08-01'], 'statementPeriod'],
            'period with an unreadable start' => [
                ['statement_period_start' => 'August', 'statement_period_end' => '2026-08-31'],
                'statementPeriod',
            ],
            'non-numeric average' => [['average_funds' => 'about fifteen thousand'], 'averageFunds'],
            'negative deposit count' => [['large_deposit_count' => -1], 'largeDeposit'],
            'fractional deposit count' => [['large_deposit_count' => 1.5], 'largeDeposit'],
        ];
    }

    public function testAcceptsNumericStringsForTheNumericValues(): void
    {
        $normalised = $this->normalise($this->givenResult(
            $this->typicalChecks(),
            ['average_funds' => '15321.50', 'large_deposit_count' => '0']
        ));

        $this->assertSame(15321.5, $normalised['rows']['averageFunds']['value']);
        $this->assertSame(0, $normalised['rows']['largeDeposit']['value']);
    }

    /** A skipped category was never assessed, so it shows no value, whatever was extracted. */
    public function testASkippedCategoryShowsNoValue(): void
    {
        $normalised = $this->normalise($this->givenResult(
            ['FI01' => self::check('Fail'), 'FI06' => self::check('Skipped'), 'FI07' => self::check('Skipped')],
            $this->typicalDetails()
        ));

        $this->assertSame('skipped', $normalised['rows']['name']['flag']);
        $this->assertNull($normalised['rows']['name']['value']);
    }

    /** The shape of every row stored before the prompt returned statement details and category remarks. */
    public function testNormalisesAResultWithoutDetailsOrCategoryRemarks(): void
    {
        $result = $this->givenResult(['FI16' => self::check('Fail', 'One deposit exceeds the threshold.')]);
        unset($result['analysis']['statement_details'], $result['analysis']['category_remarks']);

        $normalised = $this->normalise($result);

        $this->assertSame(
            [
                'flag' => 'fail',
                'remark' => 'One deposit exceeds the threshold.',
                'value' => null,
                'checks' => ['FI16' => ['result' => 'fail', 'remark' => 'One deposit exceeds the threshold.']],
            ],
            $normalised['rows']['largeDeposit']
        );
        $this->assertNull($normalised['rows']['bank']['value']);
    }

    /** The seeded test dataset stores an analysis with no checks at all. */
    public function testAnAnalysisWithNoChecksSkipsEveryCategory(): void
    {
        $normalised = $this->normalise([
            'analysis' => ['summary' => 'Seeded.', 'detected_currency' => 'GBP', 'core_checks' => []],
        ]);

        foreach (['authenticity', 'name', 'statementDate', 'statementPeriod', 'averageFunds', 'largeDeposit'] as $key) {
            $this->assertSame(
                ['flag' => 'skipped', 'remark' => null, 'value' => null, 'checks' => []],
                $normalised['rows'][$key],
                $key
            );
        }
    }

    /** Null tells the caller there is nothing to show, without pretending every category was skipped. */
    #[DataProvider('unusableResultProvider')]
    public function testReturnsNullWhenThereIsNoAnalysisToNormalise(array $result): void
    {
        $this->assertNull($this->normalise($result));
    }

    public static function unusableResultProvider(): array
    {
        return [
            'empty result' => [[]],
            'analysis is not an object' => [['analysis' => 'unavailable']],
            'analysis without core_checks' => [['analysis' => ['summary' => 'No checks.']]],
            'core_checks is not an object' => [['analysis' => ['core_checks' => 'none']]],
        ];
    }

    /**
     * The normalised payload is what leaves the API for the internal app. The applicant profile
     * is what the AI was told, and the metadata is pipeline provenance (bucket, key, execution
     * ARN); neither is an assessment outcome, so neither may travel with it.
     */
    public function testLeavesTheApplicantProfileAndPipelineMetadataBehind(): void
    {
        $normalised = $this->normalise($this->givenResult($this->typicalChecks(), $this->typicalDetails()));

        $json = json_encode($normalised, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('Profile Only Ltd', $json);
        $this->assertStringNotContainsString('example-idp-output-bucket', $json);
        $this->assertStringNotContainsString('arn:aws:states', $json);
    }

    private function normalise(array $report): ?array
    {
        return $this->sut->map($report)?->toArray();
    }

    private static function check(string $result, string $remark = 'Remark.'): array
    {
        return ['result' => $result, 'workingOut' => ['Working out step.'], 'remark' => $remark];
    }

    /** A clean limited company statement: FI08 and FI02 are skipped on the happy path. */
    private function typicalChecks(): array
    {
        return [
            'FI01' => self::check('Pass'),
            'FI02' => self::check('Skipped'),
            'FI03' => self::check('Pass'),
            'FI04' => self::check('Pass'),
            'FI05' => self::check('Pass'),
            'FI06' => self::check('Pass'),
            'FI07' => self::check('Pass'),
            'FI08' => self::check('Skipped'),
            'FI09' => self::check('Pass'),
            'FI10' => self::check('Pass'),
            'FI16' => self::check('Pass'),
        ];
    }

    private function typicalDetails(): array
    {
        return [
            'bank_name' => 'Example Bank',
            'bank_address' => '1 Example Street, Exampleton EX1 1EX',
            'account_holder_name' => 'Example Haulage Ltd',
            'statement_issue_date' => '2026-08-31',
            'statement_period_start' => '2026-08-01',
            'statement_period_end' => '2026-08-31',
            'average_funds' => 15321.5,
            'large_deposit_count' => 2,
        ];
    }

    /** The result column as stored: the analysis report from S3, verbatim, metadata included. */
    private function givenResult(array $checks, array $details = [], array $remarks = []): array
    {
        return [
            'metadata' => [
                'bucket' => 'example-idp-output-bucket',
                'key' => 'documents/example-statement.pdf',
                'executionId' => 'arn:aws:states:eu-west-1:000000000000:execution:example-ai-analysis:example',
                'classification' => 'BANK_STATEMENT',
            ],
            'applicantProfile' => ['organisation_name' => 'Profile Only Ltd', 'required_funds' => 8000],
            'analysis' => [
                'summary' => 'Synthetic summary.',
                'detected_currency' => 'GBP',
                'core_checks' => $checks,
                'statement_details' => $details,
                'category_remarks' => $remarks,
            ],
        ];
    }
}
